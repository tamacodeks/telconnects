<?php

// Isolated regression checks: SQLite :memory: and mocked HTTP only.
// Run with the PHP 7 runtime used by this Laravel 5.6 application.
error_reporting(E_ALL & ~E_DEPRECATED);
require dirname(__DIR__) . '/vendor/autoload.php';

use App\Http\Controllers\Service\V2\TamaTopupV2BaseController;
use App\Models\QueuedTopup;
use App\Models\TopupProcessingSetting;
use App\Services\QueuedTopupDispatcher;
use App\Services\QueuedTopupProcessor;
use App\Services\TopupRefundService;
use App\Services\TopupQueueMaintenance;
use App\Services\TopupWorkerHealth;
use GuzzleHttp\Client;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Middleware;
use GuzzleHttp\Psr7\Response;
use Illuminate\Container\Container;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Http\Request;
use Illuminate\Queue\DatabaseQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Facade;
use Illuminate\Support\Facades\Schema;

$container = new Container;
Container::setInstance($container);
$capsule = new Capsule($container);
$capsule->addConnection(['driver' => 'sqlite', 'database' => ':memory:', 'prefix' => '']);
$capsule->setAsGlobal();
$capsule->bootEloquent();
$container->instance('db', $capsule->getDatabaseManager());
$container->instance('db.schema', $capsule->getConnection()->getSchemaBuilder());
$logger = new Monolog\Logger('tests');
$logger->pushHandler(new Monolog\Handler\NullHandler);
$container->instance('log', $logger);
$container->instance(Psr\Log\LoggerInterface::class, $logger);
Facade::setFacadeApplication($container);
$queue = new DatabaseQueue($capsule->getConnection(), 'jobs', 'topups', 240);
$container->instance('queue', new class($queue) {
    private $queue;
    public function __construct($queue) { $this->queue = $queue; }
    public function connection($name) { return $this->queue; }
});
$cache = new Illuminate\Cache\Repository(new Illuminate\Cache\ArrayStore);
$container->instance('cache', new class($cache) {
    private $cache;
    public function __construct($cache) { $this->cache = $cache; }
    public function store($name) { return $this->cache; }
});

Schema::create('users', function (Blueprint $table) { $table->increments('id'); });
Schema::create('orders', function (Blueprint $table) {
    $table->increments('id');
    $table->integer('user_id');
    $table->integer('service_id')->default(2);
    $table->integer('order_status_id')->default(7);
    $table->integer('order_item_id')->nullable();
    $table->string('txn_ref');
    $table->integer('is_parent_order')->default(0);
    $table->decimal('order_amount', 12, 2);
    $table->timestamps();
});
Schema::create('order_items', function (Blueprint $table) {
    $table->increments('id');
    $table->integer('order_id');
    $table->string('transfer_ref')->nullable();
    $table->decimal('tt_dest_amount', 12, 2)->nullable();
    $table->string('tt_dest_currency')->nullable();
    $table->timestamps();
});
Schema::create('payments', function (Blueprint $table) {
    $table->increments('id');
    $table->integer('user_id');
    $table->integer('transaction_id')->nullable();
    $table->string('date');
    $table->decimal('amount', 12, 2);
    $table->string('description');
    $table->integer('received_by');
});
Schema::create('transactions', function (Blueprint $table) {
    $table->increments('id');
    $table->decimal('amount', 12, 2);
});
Schema::create('track_orders', function (Blueprint $table) {
    $table->increments('id');
    $table->integer('status')->default(0);
    $table->string('api_order_id')->nullable();
    $table->string('api_trans_id')->nullable();
    $table->string('remarks')->nullable();
    $table->integer('updated_by')->nullable();
    $table->timestamps();
});
foreach ([
    '2018_04_11_040254_create_jobs_table.php' => 'CreateJobsTable',
    '2026_08_10_000001_create_topup_confirm_attempts_table.php' => 'CreateTopupConfirmAttemptsTable',
    '2026_11_03_000002_create_topup_processing_settings_table.php' => 'CreateTopupProcessingSettingsTable',
    '2026_11_03_000003_create_queued_topups_table.php' => 'CreateQueuedTopupsTable',
    '2026_11_03_000004_add_mobile_to_queued_topups_table.php' => 'AddMobileToQueuedTopupsTable',
] as $migration => $class) {
    require dirname(__DIR__) . '/database/migrations/' . $migration;
    (new $class)->up();
}

class QueuedControllerProbe extends TamaTopupV2BaseController
{
    public function __construct() {}
    protected function topupConfirmClientIp(Request $request) { return '192.0.2.10'; }
    public function isQueued($id) { return $this->queuedTopupModeEnabled($id); }
    public function prepare() { return $this->prepareQueuedTopup(Request::create('/confirm', 'POST'), 'ding', 4, 'TRACK-1'); }
    public function persist($fixture)
    {
        $this->dispatchQueuedTopup(Request::create('/confirm', 'POST', ['AccountNumber' => '33612345678']), [
            'provider' => 'ding', 'payload' => ['AccountNumber' => '33612345678', 'SendValue' => 10],
            'track_order_id' => $fixture['track'],
        ], $fixture['request'], 4, $fixture['order'], $fixture['reference']);
    }
}

function expect($condition, $message)
{
    if (!$condition) { throw new RuntimeException($message); }
}

function fixture($provider = 'ding', $queued = true)
{
    $reference = 'LOCAL-' . uniqid();
    $request = 'REQUEST-' . uniqid();
    $track = DB::table('track_orders')->insertGetId(['status' => 0]);
    $item = DB::table('order_items')->insertGetId(['order_id' => 0]);
    $order = DB::table('orders')->insertGetId([
        'user_id' => 4, 'txn_ref' => $reference, 'order_amount' => 9,
        'order_item_id' => $item,
    ]);
    DB::table('order_items')->where('id', $item)->update(['order_id' => $order]);
    foreach ([[4, 9], [3, 8]] as $refund) {
        DB::table('orders')->insert([
            'user_id' => $refund[0], 'txn_ref' => $reference, 'order_amount' => $refund[1],
            'order_item_id' => $item, 'is_parent_order' => 1,
        ]);
    }
    DB::table('topup_confirm_attempts')->insert([
        'request_id' => $request, 'provider' => $provider, 'mobile' => '33612345678', 'status' => 'QUEUED',
    ]);
    $row = $queued ? QueuedTopup::create([
        'request_id' => $request, 'provider' => $provider, 'user_id' => 4, 'order_id' => $order,
        'track_order_id' => $track, 'local_reference' => $reference, 'mobile' => '33612345678',
        'payload' => ['AccountNumber' => '33612345678', 'SendValue' => 10],
        'client_ip' => '192.0.2.10', 'user_agent' => 'Queue regression test',
    ]) : null;
    return compact('row', 'order', 'track', 'item', 'reference', 'request');
}

function mockedProcessor(array $replies, &$history)
{
    $history = [];
    $stack = HandlerStack::create(new MockHandler($replies));
    $stack->push(Middleware::history($history));
    return new QueuedTopupProcessor(new Client(['handler' => $stack, 'base_uri' => 'https://operator.invalid/']));
}

function successReply($reference = 'PROVIDER-123')
{
    return new Response(200, ['Content-Type' => 'application/json'], json_encode(['data' => [
        'transaction_id' => 101, 'order_id' => 202, 'txn_ref' => $reference, 'transRef' => 'TRANSFER-55',
        'local_amount' => 50, 'dest_currency' => 'XOF',
    ]]));
}

$passed = 0;
function checkCase($name, $test)
{
    global $passed;
    foreach (['jobs', 'queued_topups', 'orders', 'order_items', 'track_orders', 'payments', 'transactions', 'topup_confirm_attempts', 'topup_processing_settings'] as $table) {
        DB::table($table)->delete();
    }
    $test();
    $passed++;
    echo 'PASS ' . $name . PHP_EOL;
}

checkCase('per-user mode defaults to synchronous and users are independent', function () {
    $probe = new QueuedControllerProbe;
    expect(!$probe->isQueued(4), 'Default mode changed.');
    TopupProcessingSetting::create(['user_id' => 4, 'mode' => 'queued']);
    expect($probe->isQueued(4) && !$probe->isQueued(5), 'Per-user mode leaked.');
});

foreach (['ding', 'reloadly', 'transfer', 'tellus'] as $provider) {
    checkCase($provider . ' success, request metadata, duplicate job suppression', function () use ($provider) {
        $f = fixture($provider);
        $processor = mockedProcessor([successReply()], $history);
        $processor->process($f['row']->id);
        $processor->process($f['row']->id);
        expect(QueuedTopup::find($f['row']->id)->status === 'succeeded', 'Top-up not finalized.');
        expect(count($history) === 1, 'Duplicate job resent top-up.');
        expect((string) $history[0]['request']->getUri()->getPath() === '/topup/confirm/' . $provider, 'Wrong provider endpoint.');
        expect($history[0]['request']->getHeaderLine('X-Tamashop-Request-ID') === $f['request'], 'Request correlation lost.');
        expect(DB::table('orders')->where('order_item_id', $f['item'])->where('txn_ref', 'PROVIDER-123')->count() === 3, 'Order family not linked.');
        expect(DB::table('payments')->count() === 0, 'Success was refunded.');
        expect(DB::table('order_items')->where('id', $f['item'])->value('tt_dest_amount') == 50, 'Transfer destination was not updated.');
    });
}

checkCase('definite validation rejection refunds retailer and manager exactly once', function () {
    $f = fixture();
    $processor = mockedProcessor([new Response(422, [], '{"message":"Invalid amount"}')], $history);
    $processor->process($f['row']->id);
    $processor->process($f['row']->id);
    expect(QueuedTopup::find($f['row']->id)->status === 'refunded', 'Rejection not refunded.');
    expect(DB::table('payments')->count() === 2 && DB::table('payments')->sum('amount') == 17, 'Incorrect refund amounts.');
    expect(DB::table('orders')->where('order_status_id', 9)->count() === 3, 'Refunded status missing.');
});

foreach ([
    'HTTP 500' => new Response(500, [], '{"message":"server error"}'),
    'HTTP 429' => new Response(429, [], '{"message":"rate limit"}'),
    'HTTP 403 generic exception' => new Response(403, [], '{"message":"Unable to send topup, please try again later!"}'),
    'malformed JSON' => new Response(200, [], '<html>Error</html>'),
    'missing transaction ID' => new Response(200, [], '{"data":{"order_id":202}}'),
    'zero transaction sentinel' => new Response(200, [], '{"data":{"transaction_id":"000"}}'),
    'Tellus duplicate 4003' => new Response(400, [], '{"message":"Tellus API Error [4003]: duplicate"}'),
    'network timeout' => new GuzzleHttp\Exception\ConnectException('timeout', new GuzzleHttp\Psr7\Request('POST', 'https://operator.invalid/')),
] as $name => $reply) {
    checkCase($name . ' stays unknown without refund or resend', function () use ($reply) {
        $f = fixture('tellus');
        $processor = mockedProcessor([$reply], $history);
        $processor->process($f['row']->id);
        $processor->process($f['row']->id);
        expect(QueuedTopup::find($f['row']->id)->status === 'unknown', 'Ambiguous result was finalized.');
        expect(count($history) === 1 && DB::table('payments')->count() === 0, 'Ambiguous request resent or refunded.');
    });
}

checkCase('repeated failure callbacks refund a successful queued top-up once', function () {
    $f = fixture('transfer');
    mockedProcessor([successReply()], $history)->process($f['row']->id);
    $refund = new TopupRefundService;
    $refund->callback('transfer', 'PROVIDER-123', ['external_id' => 'PROVIDER-123']);
    $refund->callback('transfer', 'PROVIDER-123', ['external_id' => 'PROVIDER-123']);
    expect(QueuedTopup::find($f['row']->id)->status === 'refunded', 'Queue audit did not reflect callback refund.');
    expect(DB::table('payments')->count() === 2, 'Callback refunded twice.');
});

checkCase('legacy callback flow also refunds once', function () {
    $f = fixture('reloadly', false);
    $refund = new TopupRefundService;
    $refund->callback('reloadly', $f['reference'], ['external_id' => $f['reference']]);
    $refund->callback('reloadly', $f['reference'], ['external_id' => $f['reference']]);
    expect(DB::table('payments')->count() === 2, 'Legacy callback refund duplicated.');
});

checkCase('payment insert failure rolls back statuses and allows refund recovery', function () {
    $f = fixture();
    DB::statement("CREATE TRIGGER fail_refund BEFORE INSERT ON payments BEGIN SELECT RAISE(FAIL, 'test payment failure'); END");
    try {
        (new TopupRefundService)->queued($f['row']->id, 'rejected', []);
        throw new RuntimeException('Payment failure did not propagate.');
    } catch (Illuminate\Database\QueryException $expected) {
        expect(DB::table('orders')->where('order_status_id', 7)->count() === 3, 'Order marked failed without money refunded.');
    } finally {
        DB::statement('DROP TRIGGER fail_refund');
    }
    (new TopupRefundService)->queued($f['row']->id, 'rejected', []);
    expect(DB::table('payments')->count() === 2, 'Refund recovery failed.');
});

checkCase('queue insertion failure rolls back debit, orders, and queue record together', function () {
    DB::statement("CREATE TRIGGER fail_queue BEFORE INSERT ON jobs BEGIN SELECT RAISE(FAIL, 'test queue failure'); END");
    try {
        DB::transaction(function () {
            $f = fixture('ding', false);
            DB::table('transactions')->insert(['amount' => 9]);
            (new QueuedControllerProbe)->persist($f);
        });
        throw new RuntimeException('Queue insert failure was hidden.');
    } catch (Illuminate\Database\QueryException $expected) {
        expect(DB::table('orders')->count() === 0 && DB::table('transactions')->count() === 0
            && QueuedTopup::count() === 0 && DB::table('jobs')->count() === 0, 'Partial debit/order survived rollback.');
    } finally {
        DB::statement('DROP TRIGGER fail_queue');
    }
});

checkCase('queue dispatch is idempotent and creates a real Laravel database payload', function () {
    $f = fixture();
    $dispatcher = new QueuedTopupDispatcher;
    expect($dispatcher->dispatch($f['row']->id), 'First dispatch failed.');
    expect(!$dispatcher->dispatch($f['row']->id), 'Job dispatched twice.');
    $job = DB::table('jobs')->first();
    expect($job->queue === 'topups' && strpos($job->payload, 'ProcessQueuedTopup') !== false, 'Invalid queue payload.');
    expect(DB::table('jobs')->count() === 1, 'More than one database job exists.');
});

checkCase('worker refuses to send an already refunded local order', function () {
    $f = fixture();
    DB::table('orders')->where('id', $f['order'])->update(['order_status_id' => 9]);
    $processor = mockedProcessor([], $history);
    $processor->process($f['row']->id);
    expect(count($history) === 0 && QueuedTopup::find($f['row']->id)->status === 'unknown', 'Refunded order sent to provider.');
});

checkCase('worker heartbeat distinguishes online and stopped worker', function () {
    $health = new TopupWorkerHealth;
    $health->clear();
    expect(!$health->isRunning(), 'Stopped worker reported online.');
    $health->heartbeat();
    expect($health->isRunning(), 'Worker heartbeat missing.');
    $health->clear();
    expect(!$health->isRunning(), 'Shutdown heartbeat not cleared.');
});

checkCase('offline worker blocks queued acceptance before creating an order or debit', function () {
    $container = Container::getInstance();
    $container->instance('translator', new class {
        public function trans($key, $replace = [], $locale = null) { return $key; }
        public function get($key, $replace = [], $locale = null) { return $key; }
    });
    $container->instance('redirect', new class {
        public function to($path, $status = 302, $headers = [], $secure = null) {
            return new class('/' . $path, $status) extends Illuminate\Http\RedirectResponse {
                public function with($key, $value = null) { return $this; }
            };
        }
    });
    TopupProcessingSetting::create(['user_id' => 4, 'mode' => 'queued']);
    (new TopupWorkerHealth)->clear();
    $response = (new QueuedControllerProbe)->prepare();
    expect($response instanceof Illuminate\Http\RedirectResponse, 'Queued request accepted without worker.');
    expect(DB::table('orders')->count() === 0 && DB::table('transactions')->count() === 0, 'Offline acceptance charged money.');
});

checkCase('maintenance recovers undispatched rows and withholds stale processing rows', function () {
    $pending = fixture();
    DB::table('queued_topups')->where('id', $pending['row']->id)->update(['created_at' => date('Y-m-d H:i:s', time() - 120)]);
    $stale = fixture();
    DB::table('queued_topups')->where('id', $stale['row']->id)->update([
        'status' => 'processing', 'updated_at' => date('Y-m-d H:i:s', time() - 700),
    ]);
    (new TopupQueueMaintenance)->run();
    expect(QueuedTopup::find($pending['row']->id)->dispatched_at !== null && DB::table('jobs')->count() === 1, 'Lost dispatch was not recovered.');
    expect(QueuedTopup::find($stale['row']->id)->status === 'unknown' && DB::table('payments')->count() === 0, 'Stale request was resent or refunded.');
});

checkCase('provider success is retained when local finalization fails, without resending', function () {
    $f = fixture();
    DB::statement("CREATE TRIGGER fail_finalization BEFORE UPDATE ON order_items BEGIN SELECT RAISE(FAIL, 'test finalization failure'); END");
    $processor = mockedProcessor([successReply()], $history);
    try {
        $processor->process($f['row']->id);
        $processor->process($f['row']->id);
        $row = QueuedTopup::find($f['row']->id);
        expect($row->status === 'unknown' && $row->provider_response['classification'] === 'succeeded', 'Known provider reply lost.');
        expect(count($history) === 1 && DB::table('payments')->count() === 0, 'Local failure caused resend or refund.');
        expect(DB::table('orders')->where('id', $f['order'])->value('txn_ref') === $f['reference'], 'Partial finalization committed.');
    } finally {
        DB::statement('DROP TRIGGER fail_finalization');
    }
    expect($processor->reconcileStoredResponse($f['row']->id), 'Stored success could not be recovered.');
    expect(QueuedTopup::find($f['row']->id)->status === 'succeeded' && count($history) === 1, 'Recovery called the operator again.');
});

checkCase('explicit Tellus rejection refunds without changing transaction ID or retrying', function () {
    $f = fixture('tellus');
    $processor = mockedProcessor([new Response(400, [], '{"message":"Tellus API Error [4004]: Invalid product"}')], $history);
    $processor->process($f['row']->id);
    expect(QueuedTopup::find($f['row']->id)->status === 'refunded', 'Explicit failure not refunded.');
    expect(count($history) === 1 && DB::table('payments')->count() === 2, 'Unexpected retry or refund amount.');
});

checkCase('V2 user edit Blade compiles and queue messages exist in all supported languages', function () {
    $blade = new Illuminate\View\Compilers\BladeCompiler(new Illuminate\Filesystem\Filesystem, sys_get_temp_dir());
    $compiled = $blade->compileString(file_get_contents(dirname(__DIR__) . '/resources/views/v2/app/users/update.blade.php'));
    token_get_all($compiled, TOKEN_PARSE);
    foreach (['en', 'fr', 'pt'] as $locale) {
        $messages = require dirname(__DIR__) . '/resources/lang/' . $locale . '/topup_v2.php';
        $users = require dirname(__DIR__) . '/resources/lang/' . $locale . '/v2_users.php';
        expect(!empty($messages['queue_unavailable']) && !empty($messages['queue_already_processing'])
            && !empty($users['topup_processing_queued']), 'Queue translations missing for ' . $locale);
    }
});

echo $passed . ' regression scenarios passed using in-memory SQLite and mocked HTTP. No application database or real operator was contacted.' . PHP_EOL;

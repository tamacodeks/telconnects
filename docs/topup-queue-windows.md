# Mayadigital queued top-ups on Windows

Root selects a user's mode under Users V2 > Edit > Top-up processing. Standard is
the existing operator-first flow. Queued saves the normal successful order and
receipt, then sends to Ding, Reloadly, TransferTo or Tellus from a database worker.
Settings are in `topup_processing_settings`; no processing column is added to users.

## Setup

Deploy all changed and new files. Use the PHP 7 executable already used by this
Laravel 5.6 application. Do not switch the production PHP version for this feature.

Apply these feature migrations after your normal database backup/review:

```bat
php artisan migrate --path=database/migrations/2026_11_03_000002_create_topup_processing_settings_table.php
php artisan migrate --path=database/migrations/2026_11_03_000003_create_queued_topups_table.php
php artisan migrate --path=database/migrations/2026_11_03_000004_add_mobile_to_queued_topups_table.php
php artisan config:clear
php artisan view:clear
```

The existing `jobs` table must also exist. Its migration is
`database/migrations/2018_04_11_040254_create_jobs_table.php`; apply it only if missing.
`orders`, `transactions`, `payments`, `queued_topups` and `jobs` must use transactional
tables (InnoDB on MySQL), and `queue.connections.topups` uses the default application
database. That makes the debit, order, queue record and database job one commit.

Run the worker from the deployed application folder:

```bat
cd /d C:\wwwphp\mayadigital
set "MAYA_TOPUP_PHP=C:\path\to\your\php.exe"
topup-worker.bat
```

The batch file uses its own folder, regardless of installation path. Keep the
worker running or register the PHP executable as a Windows service with arguments
`artisan topups:work` and the application folder as its working directory.
Use `topups:work`, because the wrapper publishes worker health and runs top-up
maintenance. A plain `queue:work` process does not publish that heartbeat.

Only one `topups:work` instance can hold the application worker lock. The worker
handles one provider request at a time; under load, requests wait their turn.
After an application deployment, restart it with `php artisan queue:restart`.
The batch file restarts PHP after a normal worker exit.

## Keep the existing four-hour scheduler

The existing System Scheduler event does not need to change. While `topups:work`
is running it processes jobs continuously and, approximately once per minute
between jobs, also:

- Recovers old queue records that were never dispatched.
- Marks stale processing records unknown without re-sending them.
- Finalizes a saved, definite operator response after a temporary local database failure, without another API request.
- Checks Reloadly, TransferTo and Tellus failure callbacks for queued orders.
- Applies each confirmed refund once, using the existing payment refund amounts.

These queued-order checks run independently of `schedule:run`. Scheduled legacy
callback commands remain available and use the same transactional refund service.
Ding has no failure-callback endpoint in the current upstream API; a later Ding
failure or ambiguous Ding response needs reconciliation in the upstream system.

If the worker heartbeat is missing or older than five minutes, new queued top-ups
are rejected before the debit. Standard users continue using their current flow.
A worker that dies just after its last heartbeat can still leave jobs pending
until it restarts. Check worker status regularly.

## Monitoring and uncertain results

```bat
php artisan topups:status
php artisan topups:dispatch-pending
```

`topups:status` shows heartbeat, counts, and the first 20 unresolved requests with
their order and request IDs. Also inspect `storage/logs` for queue errors.

- `pending`: stored; waiting for a worker.
- `processing`: provider request claimed; another job will not re-send it.
- `succeeded`: upstream accepted the request and returned its transaction/reference.
- `refunded`: confirmed rejection/failure and local refund committed together.
- `unknown`: verify the upstream order/request ID before deciding what to do.

Never bulk retry unknown jobs. The upstream currently sometimes returns HTTP 403
or `transaction_id=000` after an internal exception, including after contacting an
operator. Those replies are not proof of failure. Timeouts, malformed responses,
HTTP 429/5xx and Tellus duplicate 4003 also stay unknown. The queued flow never
changes Tellus's transaction ID and re-sends after a duplicate response.

Validation/authentication rejection and explicit Tellus rejection are refunded.
Confirmed failure callbacks also refund the retailer/parent amounts. Duplicate
callbacks and duplicate queue deliveries do not charge, refund, or send twice.
The provider response is retained before local finalization for reconciliation.
A previous pending/processing/unknown queued top-up blocks another submission to
the same normalized mobile number, even after the short cache lock expires.

## Verification

```bat
php tests\topup_queue_regression.php
```

This runs the real queue database payload and application services against an
isolated in-memory SQLite database with mocked provider HTTP responses. It does
not load the application `.env` database or send any real recharge. MySQL locking
and a controlled staging top-up still need verification on the deployed server.

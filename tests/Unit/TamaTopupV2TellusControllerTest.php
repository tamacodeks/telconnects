<?php

namespace Tests\Unit;

use App\Http\Controllers\Service\V2\TamaTopupV2TellusController;
use Illuminate\Http\Request;
use Tests\TestCase;

class TamaTopupV2TellusControllerTest extends TestCase
{
    private function buildPayload(array $input)
    {
        $controller = new TamaTopupV2TellusController();
        $reflection = new \ReflectionClass($controller);
        $method = $reflection->getMethod('buildTellusTopupPayload');
        $method->setAccessible(true);

        return $method->invoke($controller, Request::create('/tellus', 'POST', $input));
    }

    public function test_it_builds_fixed_tellus_payload_from_product_name()
    {
        $payload = $this->buildPayload([
            'countryCode' => '221',
            'AccountNumber' => '221770000000',
            'SkuCode' => '4378',
            'SendValue' => '4.60',
            'sendValueOriginal' => '4.60',
            'local_amt' => '2500',
            'localAmount' => '2500',
            'productName' => '2500 XOF',
            'name' => 'Topup Plan - 2500 XOF',
            'currency' => 'XOF',
            'country' => 'Senegal',
            'operator' => 'Orange Senegal',
            'structure' => 'FIXED',
            'type' => 'AIRTIME',
        ]);

        $this->assertSame('2500', $payload['product']);
        $this->assertSame('2500', $payload['localAmount']);
        $this->assertSame('2500', $payload['local_amt']);
        $this->assertSame('4378', $payload['productId']);
        $this->assertSame('4.60', $payload['SendValue']);
    }

    public function test_it_builds_range_tellus_payload_from_local_bounds()
    {
        $payload = $this->buildPayload([
            'countryCode' => '221',
            'AccountNumber' => '221770000000',
            'SkuCode' => '5857',
            'SendValue' => '0.50',
            'sendValueOriginal' => '0.50',
            'minSendValue' => '0.50',
            'maxSendValue' => '3.10',
            'localAmountMin' => '100',
            'localAmountMax' => '1967',
            'productName' => '100 - 1967 XOF',
            'currency' => 'XOF',
            'country' => 'Senegal',
            'operator' => 'Orange Senegal',
            'structure' => 'RANGE',
            'type' => 'AIRTIME',
        ]);

        $this->assertSame('100', $payload['product']);
        $this->assertSame('100', $payload['localAmount']);
        $this->assertSame('100', $payload['local_amt']);
        $this->assertSame('5857', $payload['productId']);
    }

    public function test_it_keeps_explicit_sender_and_transaction_id()
    {
        $payload = $this->buildPayload([
            'countryCode' => '221',
            'recipientNumber' => '221770000000',
            'senderNumber' => '33612345678',
            'transactionId' => 'TEL_TEST_123',
            'product' => '1500',
            'productId' => '5857',
            'SendValue' => '1.20',
            'sendValueOriginal' => '1.20',
            'local_amt' => '1500',
            'currency' => 'XOF',
            'country' => 'Senegal',
            'operator' => 'Orange Senegal',
            'structure' => 'FIXED',
            'type' => 'AIRTIME',
        ]);

        $this->assertSame('33612345678', $payload['senderNumber']);
        $this->assertSame('221770000000', $payload['recipientNumber']);
        $this->assertSame('TEL_TEST_123', $payload['transactionId']);
        $this->assertSame('1500', $payload['product']);
        $this->assertSame('5857', $payload['productId']);
    }
}

<?php

require_once __DIR__ . '/../autoload.php';

use PayCow\Http\HttpClientInterface;
use PayCow\Http\Response;
use PayCow\PayCowClient;
use PayCow\Payments\PaymentService;
use PayCow\Webhooks\WebhookVerifier;

final class FakeHttpClient implements HttpClientInterface
{
    public array $requests = [];
    private array $responses;

    public function __construct(array $responses)
    {
        $this->responses = $responses;
    }

    public function request(string $method, string $url, array $headers = [], ?string $body = null, int $timeout = 20): Response
    {
        $this->requests[] = compact('method', 'url', 'headers', 'body', 'timeout');
        return array_shift($this->responses);
    }
}

function check(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException($message);
    }
}

$transport = new FakeHttpClient([
    new Response(200, json_encode([
        'sessionId' => 'session-1',
        'paymentUrl' => 'https://paycow.net/payment/session-1',
        'paymentReference' => 'PAY-1',
        'amountRequested' => '50.00',
        'payableAmount' => '50.001',
        'depositAddress' => 'TAddress',
        'expiresAt' => '2026-09-16T23:00:00.000Z',
    ])),
    new Response(200, json_encode([
        'sessionId' => 'session-1',
        'userId' => 'player-2048',
        'amountRequested' => '50.00',
        'payableAmount' => '50.001',
        'amountReceived' => '50.001',
        'status' => 'PAID',
        'confirmations' => 20,
        'paymentReference' => 'PAY-1',
        'txHash' => 'tx-1',
        'depositNetwork' => 'TRC20',
        'expiresAt' => '2026-09-16T23:00:00.000Z',
    ])),
]);

$client = new PayCowClient([
    'api_key' => 'key',
    'api_secret' => 'secret',
    'api_base_url' => 'https://example.test',
], $transport);

$session = $client->payments()->create([
    'userId' => 'player-2048',
    'amount' => '50.00',
    'network' => 'TRC20',
    'idempotencyKey' => 'topup-2048-001',
]);

check($session->getSessionId() === 'session-1', 'Create response was not mapped');
check($session->getPayableAmount() === '50.001', 'Payable amount lost precision');
check($transport->requests[0]['url'] === 'https://example.test/api/payment/create', 'Create endpoint is incorrect');
check($transport->requests[0]['headers']['Idempotency-Key'] === 'topup-2048-001', 'Idempotency header is missing');
check(json_decode($transport->requests[0]['body'], true)['idempotencyKey'] === 'topup-2048-001', 'Idempotency body value is missing');

$status = $client->payments()->getStatus('session-1');
check($status->isPaid(), 'Paid status was not recognized');
check($status->getTransactionHash() === 'tx-1', 'Transaction hash was not mapped');
check($transport->requests[1]['url'] === 'https://example.test/api/payment/status/session-1', 'Status endpoint is incorrect');

$timestamp = (string) time();
$rawBody = json_encode([
    'id' => 'evt-1',
    'type' => 'payment.paid',
    'data' => ['userId' => 'player-2048', 'paymentReference' => 'PAY-1'],
]);
$secret = 'whsec_test';
$signature = 'v1=' . hash_hmac('sha256', $timestamp . '.' . $rawBody, $secret);
$event = (new WebhookVerifier())->verify($rawBody, [
    'PayCow-Timestamp' => $timestamp,
    'PayCow-Signature' => $signature,
], $secret);

check($event->isPaid(), 'Paid webhook was not recognized');
check($event->getUserId() === 'player-2048', 'Webhook payload was not mapped');
check(PaymentService::compareDecimalStrings('50.0010', '50.001') === 0, 'Decimal comparison is incorrect');

$invalidAmountRejected = false;
try {
    $client->payments()->create([
        'userId' => 'player-2048',
        'amount' => '1e3',
        'network' => 'TRC20',
    ]);
} catch (PayCow\Exceptions\PayCowException $error) {
    $invalidAmountRejected = true;
}
check($invalidAmountRejected, 'Non-decimal payment amount was accepted');

echo "PayCow PHP SDK tests passed\n";

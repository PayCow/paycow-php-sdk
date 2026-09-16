<?php

namespace PayCow;

use PayCow\Http\CurlHttpClient;
use PayCow\Http\HttpClientInterface;
use PayCow\Payments\PaymentService;
use PayCow\Webhooks\WebhookVerifier;

class PayCowClient
{
    public const VERSION = '1.0.0';

    protected Config $config;
    protected HttpClientInterface $httpClient;
    protected PaymentService $paymentService;
    protected WebhookVerifier $webhookVerifier;



    public function __construct($config, ?HttpClientInterface $httpClient = null)
    {
        if ($config instanceof Config) {
            $this->config = $config;
        } else {
            $this->config = new Config((array) $config);
        }

        $this->httpClient = $httpClient ?? new CurlHttpClient();
        $this->paymentService = new PaymentService($this->config, $this->httpClient);
        $this->webhookVerifier = new WebhookVerifier();
    }



    public function payments(): PaymentService
    {
        return $this->paymentService;
    }



    public function webhooks(): WebhookVerifier
    {
        return $this->webhookVerifier;
    }



    public function getConfig(): Config
    {
        return $this->config;
    }
}

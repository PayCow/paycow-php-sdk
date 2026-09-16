<?php

namespace PayCow\Http;

use PayCow\Exceptions\ApiException;

interface HttpClientInterface
{


    public function request(string $method, string $url, array $headers = [], ?string $body = null, int $timeout = 20): Response;
}

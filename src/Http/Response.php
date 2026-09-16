<?php

namespace PayCow\Http;

use PayCow\Exceptions\ApiException;

class Response
{
    protected int $statusCode;
    protected string $body;
    protected array $headers;



    public function __construct(int $statusCode, string $body, array $headers = [])
    {
        $this->statusCode = $statusCode;
        $this->body = $body;
        $this->headers = $headers;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getBody(): string
    {
        return $this->body;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }

    public function isSuccess(): bool
    {
        return $this->statusCode >= 200 && $this->statusCode < 300;
    }



    public function json(): array
    {
        $decoded = json_decode($this->body, true);
        if (json_last_error() !== JSON_ERROR_NONE || !is_array($decoded)) {
            throw new ApiException('Invalid JSON response from PayCow API: ' . json_last_error_msg(), $this->statusCode, $this->body);
        }
        return $decoded;
    }
}

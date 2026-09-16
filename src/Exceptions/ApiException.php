<?php

namespace PayCow\Exceptions;

class ApiException extends PayCowException
{


    protected int $statusCode;



    protected ?string $responseBody;



    public function __construct(string $message, int $statusCode = 0, ?string $responseBody = null)
    {
        parent::__construct($message, $statusCode);
        $this->statusCode = $statusCode;
        $this->responseBody = $responseBody;
    }



    public function getStatusCode(): int
    {
        return $this->statusCode;
    }



    public function getResponseBody(): ?string
    {
        return $this->responseBody;
    }
}

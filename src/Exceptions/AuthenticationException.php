<?php

namespace PayCow\Exceptions;

class AuthenticationException extends ApiException
{


    public function __construct(string $message = 'Invalid PayCow API credentials', int $statusCode = 401, ?string $responseBody = null)
    {
        parent::__construct($message, $statusCode, $responseBody);
    }
}

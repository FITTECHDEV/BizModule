<?php

namespace App\Exceptions;

use Exception;
use Illuminate\Http\JsonResponse;

class CpanelException extends Exception
{
    protected $statusCode;
    protected $errorType;
    protected $context;

    /**
     * Create a new CpanelException instance.
     *
     * @param string $message
     * @param int $statusCode
     * @param string $errorType
     * @param array $context
     */
    public function __construct(
        string $message = 'cPanel API Error',
        int $statusCode = 500,
        string $errorType = 'api_error',
        array $context = []
    ) {
        parent::__construct($message);
        $this->statusCode = $statusCode;
        $this->errorType = $errorType;
        $this->context = $context;
    }

    /**
     * Get the HTTP status code.
     *
     * @return int
     */
    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * Get the error type.
     *
     * @return string
     */
    public function getErrorType(): string
    {
        return $this->errorType;
    }

    /**
     * Get the error context.
     *
     * @return array
     */
    public function getContext(): array
    {
        return $this->context;
    }

    /**
     * Render the exception as an HTTP response.
     *
     * @return JsonResponse
     */
    public function render(): JsonResponse
    {
        return response()->json([
            'success' => false,
            'error' => [
                'type' => $this->errorType,
                'message' => $this->message,
                'context' => $this->context,
            ],
        ], $this->statusCode);
    }

    /**
     * Authentication failed exception.
     */
    public static function authenticationFailed(string $message = 'cPanel authentication failed'): self
    {
        return new self($message, 401, 'authentication_failed');
    }

    /**
     * Email already exists exception.
     */
    public static function emailAlreadyExists(string $email): self
    {
        return new self(
            "Email account '{$email}' already exists",
            409,
            'email_already_exists',
            ['email' => $email]
        );
    }

    /**
     * Email not found exception.
     */
    public static function emailNotFound(string $email): self
    {
        return new self(
            "Email account '{$email}' not found",
            404,
            'email_not_found',
            ['email' => $email]
        );
    }

    /**
     * Invalid domain exception.
     */
    public static function invalidDomain(string $domain): self
    {
        return new self(
            "Domain '{$domain}' is invalid or not found",
            400,
            'invalid_domain',
            ['domain' => $domain]
        );
    }

    /**
     * Quota exceeded exception.
     */
    public static function quotaExceeded(int $requested, int $available): self
    {
        return new self(
            "Requested quota ({$requested}MB) exceeds available quota ({$available}MB)",
            400,
            'quota_exceeded',
            ['requested' => $requested, 'available' => $available]
        );
    }

    /**
     * Invalid password exception.
     */
    public static function invalidPassword(string $reason = 'Password does not meet requirements'): self
    {
        return new self($reason, 400, 'invalid_password');
    }

    /**
     * Mailing list already exists exception.
     */
    public static function mailingListAlreadyExists(string $listName): self
    {
        return new self(
            "Mailing list '{$listName}' already exists",
            409,
            'mailing_list_already_exists',
            ['list_name' => $listName]
        );
    }

    /**
     * Mailing list not found exception.
     */
    public static function mailingListNotFound(string $listName): self
    {
        return new self(
            "Mailing list '{$listName}' not found",
            404,
            'mailing_list_not_found',
            ['list_name' => $listName]
        );
    }

    /**
     * Subscriber already exists exception.
     */
    public static function subscriberAlreadyExists(string $email, string $listName): self
    {
        return new self(
            "Subscriber '{$email}' already exists in mailing list '{$listName}'",
            409,
            'subscriber_already_exists',
            ['email' => $email, 'list_name' => $listName]
        );
    }

    /**
     * Network error exception.
     */
    public static function networkError(string $message = 'Network error occurred'): self
    {
        return new self($message, 503, 'network_error');
    }

    /**
     * API request failed exception.
     */
    public static function apiRequestFailed(string $message, array $context = []): self
    {
        return new self($message, 500, 'api_request_failed', $context);
    }

    /**
     * Configuration error exception.
     */
    public static function configurationError(string $message = 'cPanel configuration error'): self
    {
        return new self($message, 500, 'configuration_error');
    }
}
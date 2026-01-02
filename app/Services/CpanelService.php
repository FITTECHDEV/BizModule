<?php

namespace App\Services;

use App\Exceptions\CpanelException;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Http\Client\Response;

class CpanelService
{
    protected string $baseUrl;
    protected int $port;
    protected string $username;
    protected string $apiToken;
    protected bool $mockMode;
    protected int $timeout;

    public function __construct()
    {
        $this->baseUrl = config('cpanel.url');
        $this->port = config('cpanel.port');
        $this->username = config('cpanel.username');
        $this->apiToken = config('cpanel.api_token');
        $this->mockMode = config('cpanel.mock_mode', true);
        $this->timeout = config('cpanel.timeout', 30);

        if (!$this->mockMode && empty($this->apiToken)) {
            throw CpanelException::configurationError('cPanel API token is not configured');
        }
    }

    /**
     * Create email account
     */
    public function createEmail(string $email, string $password, string $domain, int $quota = 0): array
    {
        if ($this->mockMode) {
            return $this->mockCreateEmail($email, $domain);
        }

        $endpoint = config('cpanel.endpoints.create_email');
        
        $params = [
            'email' => $email,
            'password' => $password,
            'quota' => $quota,
            'domain' => $domain,
        ];

        $response = $this->makeRequest($endpoint, $params);

        if (!$response['success']) {
            $this->handleError($response, "Failed to create email {$email}@{$domain}");
        }

        return [
            'email' => "{$email}@{$domain}",
            'quota' => $quota,
            'domain' => $domain,
            'created_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Delete email account
     */
    public function deleteEmail(string $email): array
    {
        if ($this->mockMode) {
            return $this->mockDeleteEmail($email);
        }

        $endpoint = config('cpanel.endpoints.delete_email');
        
        [$username, $domain] = $this->parseEmail($email);
        
        $params = [
            'email' => $username,
            'domain' => $domain,
        ];

        $response = $this->makeRequest($endpoint, $params);

        if (!$response['success']) {
            $this->handleError($response, "Failed to delete email {$email}");
        }

        return [
            'email' => $email,
            'deleted' => true,
            'deleted_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Update email password
     */
    public function updatePassword(string $email, string $password): array
    {
        if ($this->mockMode) {
            return $this->mockUpdatePassword($email);
        }

        $endpoint = config('cpanel.endpoints.change_password');
        
        [$username, $domain] = $this->parseEmail($email);
        
        $params = [
            'email' => $username,
            'password' => $password,
            'domain' => $domain,
        ];

        $response = $this->makeRequest($endpoint, $params);

        if (!$response['success']) {
            $this->handleError($response, "Failed to update password for {$email}");
        }

        return [
            'email' => $email,
            'password_updated' => true,
            'updated_at' => now()->toIso8601String(),
        ];
    }

    /**
     * List all emails
     */
    public function listEmails(string $domain = null): array
    {
        if ($this->mockMode) {
            return $this->mockListEmails($domain);
        }

        $endpoint = config('cpanel.endpoints.list_emails');
        
        $params = $domain ? ['domain' => $domain] : [];

        $response = $this->makeRequest($endpoint, $params);

        if (!$response['success']) {
            $this->handleError($response, "Failed to list emails");
        }

        return $response['data'] ?? [];
    }

    /**
     * Create mailing list
     */
    public function createMailingList(string $listName, string $password, string $domain, string $privacy = 'private'): array
    {
        if ($this->mockMode) {
            return $this->mockCreateMailingList($listName, $domain);
        }

        $endpoint = config('cpanel.endpoints.create_mailing_list');
        
        $params = [
            'list' => $listName,
            'password' => $password,
            'domain' => $domain,
            'private' => $privacy === 'private' ? 1 : 0,
        ];

        $response = $this->makeRequest($endpoint, $params);

        if (!$response['success']) {
            $this->handleError($response, "Failed to create mailing list {$listName}@{$domain}");
        }

        return [
            'list_name' => "{$listName}@{$domain}",
            'privacy' => $privacy,
            'domain' => $domain,
            'created_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Delete mailing list
     */
    public function deleteMailingList(string $listName): array
    {
        if ($this->mockMode) {
            return $this->mockDeleteMailingList($listName);
        }

        $endpoint = config('cpanel.endpoints.delete_mailing_list');
        
        [$name, $domain] = $this->parseEmail($listName);
        
        $params = [
            'list' => $name,
            'domain' => $domain,
        ];

        $response = $this->makeRequest($endpoint, $params);

        if (!$response['success']) {
            $this->handleError($response, "Failed to delete mailing list {$listName}");
        }

        return [
            'list_name' => $listName,
            'deleted' => true,
            'deleted_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Add subscriber to mailing list
     */
    public function addSubscriber(string $listName, string $subscriberEmail): array
    {
        if ($this->mockMode) {
            return $this->mockAddSubscriber($listName, $subscriberEmail);
        }

        $endpoint = config('cpanel.endpoints.add_subscriber');
        
        [$name, $domain] = $this->parseEmail($listName);
        
        $params = [
            'list' => $name,
            'domain' => $domain,
            'email' => $subscriberEmail,
        ];

        $response = $this->makeRequest($endpoint, $params);

        if (!$response['success']) {
            $this->handleError($response, "Failed to add subscriber {$subscriberEmail} to {$listName}");
        }

        return [
            'list_name' => $listName,
            'subscriber' => $subscriberEmail,
            'added' => true,
            'added_at' => now()->toIso8601String(),
        ];
    }

    /**
     * Remove subscriber from mailing list
     */
    public function removeSubscriber(string $listName, string $subscriberEmail): array
    {
        if ($this->mockMode) {
            return $this->mockRemoveSubscriber($listName, $subscriberEmail);
        }

        $endpoint = config('cpanel.endpoints.remove_subscriber');
        
        [$name, $domain] = $this->parseEmail($listName);
        
        $params = [
            'list' => $name,
            'domain' => $domain,
            'email' => $subscriberEmail,
        ];

        $response = $this->makeRequest($endpoint, $params);

        if (!$response['success']) {
            $this->handleError($response, "Failed to remove subscriber {$subscriberEmail} from {$listName}");
        }

        return [
            'list_name' => $listName,
            'subscriber' => $subscriberEmail,
            'removed' => true,
            'removed_at' => now()->toIso8601String(),
        ];
    }

    /**
     * List all mailing lists
     */
    public function listMailingLists(string $domain = null): array
    {
        if ($this->mockMode) {
            return $this->mockListMailingLists($domain);
        }

        $endpoint = config('cpanel.endpoints.list_mailing_lists');
        
        $params = $domain ? ['domain' => $domain] : [];

        $response = $this->makeRequest($endpoint, $params);

        if (!$response['success']) {
            $this->handleError($response, "Failed to list mailing lists");
        }

        return $response['data'] ?? [];
    }

    /**
     * Make HTTP request to cPanel API
     */
    protected function makeRequest(string $endpoint, array $params = []): array
    {
        try {
            $url = "https://{$this->baseUrl}:{$this->port}{$endpoint}";
            
            Log::info('cPanel API Request', [
                'url' => $url,
                'params' => $params,
            ]);

            $response = Http::timeout($this->timeout)
                ->withHeaders([
                    'Authorization' => 'cpanel ' . $this->username . ':' . $this->apiToken,
                ])
                ->get($url, $params);

            $data = $response->json();

            Log::info('cPanel API Response', [
                'status' => $response->status(),
                'data' => $data,
            ]);

            return $this->parseResponse($data);

        } catch (\Exception $e) {
            Log::error('cPanel API Error', [
                'message' => $e->getMessage(),
                'endpoint' => $endpoint,
            ]);
            throw CpanelException::networkError($e->getMessage());
        }
    }

    /**
     * Parse cPanel API response
     */
    protected function parseResponse(array $data): array
    {
        // cPanel UAPI response format
        if (isset($data['status']) && $data['status'] == 1) {
            return [
                'success' => true,
                'data' => $data['data'] ?? [],
                'metadata' => $data['metadata'] ?? [],
            ];
        }

        return [
            'success' => false,
            'errors' => $data['errors'] ?? ['Unknown error occurred'],
            'messages' => $data['messages'] ?? [],
        ];
    }

    /**
     * Handle API errors
     */
    protected function handleError(array $response, string $defaultMessage): void
    {
        $errorMessage = $response['errors'][0] ?? $defaultMessage;
        
        if (str_contains(strtolower($errorMessage), 'already exists')) {
            throw CpanelException::emailAlreadyExists($errorMessage);
        }
        
        if (str_contains(strtolower($errorMessage), 'not found')) {
            throw CpanelException::emailNotFound($errorMessage);
        }
        
        if (str_contains(strtolower($errorMessage), 'authentication') || str_contains(strtolower($errorMessage), 'unauthorized')) {
            throw CpanelException::authenticationFailed($errorMessage);
        }
        
        throw CpanelException::apiRequestFailed($errorMessage);
    }

    /**
     * Parse email into username and domain
     */
    protected function parseEmail(string $email): array
    {
        $parts = explode('@', $email);
        
        if (count($parts) !== 2) {
            throw CpanelException::apiRequestFailed("Invalid email format: {$email}");
        }
        
        return $parts;
    }

    // =========================================================================
    // MOCK RESPONSES - For testing without credentials
    // =========================================================================

    protected function mockCreateEmail(string $email, string $domain): array
    {
        Log::info('[MOCK MODE] Creating email', ['email' => "{$email}@{$domain}"]);
        
        return [
            'email' => "{$email}@{$domain}",
            'quota' => config('cpanel.default_quota'),
            'domain' => $domain,
            'created_at' => now()->toIso8601String(),
            'mock' => true,
        ];
    }

    protected function mockDeleteEmail(string $email): array
    {
        Log::info('[MOCK MODE] Deleting email', ['email' => $email]);
        
        return [
            'email' => $email,
            'deleted' => true,
            'deleted_at' => now()->toIso8601String(),
            'mock' => true,
        ];
    }

    protected function mockUpdatePassword(string $email): array
    {
        Log::info('[MOCK MODE] Updating password', ['email' => $email]);
        
        return [
            'email' => $email,
            'password_updated' => true,
            'updated_at' => now()->toIso8601String(),
            'mock' => true,
        ];
    }

    protected function mockListEmails(?string $domain): array
    {
        Log::info('[MOCK MODE] Listing emails', ['domain' => $domain]);
        
        return [
            [
                'email' => 'user1@' . ($domain ?? config('cpanel.default_domain')),
                'diskused' => '10M',
                'diskquota' => '250M',
            ],
            [
                'email' => 'user2@' . ($domain ?? config('cpanel.default_domain')),
                'diskused' => '25M',
                'diskquota' => '500M',
            ],
        ];
    }

    protected function mockCreateMailingList(string $listName, string $domain): array
    {
        Log::info('[MOCK MODE] Creating mailing list', ['list' => "{$listName}@{$domain}"]);
        
        return [
            'list_name' => "{$listName}@{$domain}",
            'privacy' => 'private',
            'domain' => $domain,
            'created_at' => now()->toIso8601String(),
            'mock' => true,
        ];
    }

    protected function mockDeleteMailingList(string $listName): array
    {
        Log::info('[MOCK MODE] Deleting mailing list', ['list' => $listName]);
        
        return [
            'list_name' => $listName,
            'deleted' => true,
            'deleted_at' => now()->toIso8601String(),
            'mock' => true,
        ];
    }

    protected function mockAddSubscriber(string $listName, string $subscriberEmail): array
    {
        Log::info('[MOCK MODE] Adding subscriber', [
            'list' => $listName,
            'subscriber' => $subscriberEmail,
        ]);
        
        return [
            'list_name' => $listName,
            'subscriber' => $subscriberEmail,
            'added' => true,
            'added_at' => now()->toIso8601String(),
            'mock' => true,
        ];
    }

    protected function mockRemoveSubscriber(string $listName, string $subscriberEmail): array
    {
        Log::info('[MOCK MODE] Removing subscriber', [
            'list' => $listName,
            'subscriber' => $subscriberEmail,
        ]);
        
        return [
            'list_name' => $listName,
            'subscriber' => $subscriberEmail,
            'removed' => true,
            'removed_at' => now()->toIso8601String(),
            'mock' => true,
        ];
    }

    protected function mockListMailingLists(?string $domain): array
    {
        Log::info('[MOCK MODE] Listing mailing lists', ['domain' => $domain]);
        
        return [
            [
                'list' => 'newsletter@' . ($domain ?? config('cpanel.default_domain')),
                'subscribers' => 150,
            ],
            [
                'list' => 'updates@' . ($domain ?? config('cpanel.default_domain')),
                'subscribers' => 89,
            ],
        ];
    }
}
<?php

namespace App\Http\Controllers;

use App\Services\CpanelService;
use App\Http\Requests\CreateEmailAccountRequest;
use App\Http\Requests\DeleteEmailRequest;
use App\Http\Requests\UpdateEmailPasswordRequest;
use App\Http\Resources\EmailAccountResource;
use App\Exceptions\CpanelException;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class EmailAccountController extends Controller
{
    protected CpanelService $cpanelService;

    /**
     * Inject the CpanelService
     */
    public function __construct(CpanelService $cpanelService)
    {
        $this->cpanelService = $cpanelService;
    }

    /**
     * Create a new email account
     * 
     * @param CreateEmailAccountRequest $request
     * @return JsonResponse
     * 
     * POST /api/email-accounts
     * 
     * Request Body:
     * {
     *   "email": "john.doe",
     *   "password": "SecureP@ss123",
     *   "domain": "example.com",  // optional, uses default from config
     *   "quota": 250,              // optional, in MB (0 = unlimited)
     *   "send_welcome_email": false // optional
     * }
     */
    public function store(CreateEmailAccountRequest $request): JsonResponse
    {
        try {
            // Extract validated data
            $email = $request->input('email');
            $password = $request->input('password');
            $domain = $request->input('domain', config('cpanel.default_domain'));
            $quota = $request->getQuota();

            Log::info('Creating email account', [
                'email' => "{$email}@{$domain}",
                'quota' => $quota,
            ]);

            // Call the service to create email
            $result = $this->cpanelService->createEmail($email, $password, $domain, $quota);

            // TODO: Optionally send welcome email if requested
            // if ($request->input('send_welcome_email', false)) {
            //     $this->sendWelcomeEmail($result['email'], $password);
            // }

            // Return formatted response
            return (new EmailAccountResource($result))
                ->response()
                ->setStatusCode(201); // HTTP 201 Created

        } catch (CpanelException $e) {
            Log::error('Failed to create email account', [
                'email' => $request->input('email'),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => [
                    'message' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'type' => class_basename($e),
                ],
                'timestamp' => now()->toIso8601String(),
            ], $e->getCode() ?: 500);
        }
    }

    /**
     * List all email accounts
     * 
     * @param Request $request
     * @return JsonResponse
     * 
     * GET /api/email-accounts?domain=example.com
     */
    public function index(Request $request): JsonResponse
    {
        try {
            $domain = $request->query('domain');

            Log::info('Listing email accounts', ['domain' => $domain]);

            $emails = $this->cpanelService->listEmails($domain);

            return response()->json([
                'success' => true,
                'data' => $emails,
                'count' => count($emails),
                'timestamp' => now()->toIso8601String(),
            ]);

        } catch (CpanelException $e) {
            Log::error('Failed to list email accounts', [
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => [
                    'message' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'type' => class_basename($e),
                ],
                'timestamp' => now()->toIso8601String(),
            ], $e->getCode() ?: 500);
        }
    }

    /**
     * Delete an email account
     * 
     * @param DeleteEmailRequest $request
     * @return JsonResponse
     * 
     * DELETE /api/email-accounts
     * 
     * Request Body:
     * {
     *   "email": "john.doe@example.com"
     * }
     */
    public function destroy(DeleteEmailRequest $request): JsonResponse
    {
        try {
            $email = $request->input('email');

            Log::info('Deleting email account', ['email' => $email]);

            $result = $this->cpanelService->deleteEmail($email);

            return (new EmailAccountResource($result))
                ->response()
                ->setStatusCode(200);

        } catch (CpanelException $e) {
            Log::error('Failed to delete email account', [
                'email' => $request->input('email'),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => [
                    'message' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'type' => class_basename($e),
                ],
                'timestamp' => now()->toIso8601String(),
            ], $e->getCode() ?: 500);
        }
    }

    /**
     * Update email account password
     * 
     * @param UpdateEmailPasswordRequest $request
     * @return JsonResponse
     * 
     * PATCH /api/email-accounts/password
     * 
     * Request Body:
     * {
     *   "email": "john.doe@example.com",
     *   "password": "NewSecureP@ss456"
     * }
     */
    public function updatePassword(UpdateEmailPasswordRequest $request): JsonResponse
    {
        try {
            $email = $request->input('email');
            $password = $request->input('password');

            Log::info('Updating email password', ['email' => $email]);

            $result = $this->cpanelService->updatePassword($email, $password);

            return (new EmailAccountResource($result))
                ->response()
                ->setStatusCode(200);

        } catch (CpanelException $e) {
            Log::error('Failed to update email password', [
                'email' => $request->input('email'),
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => [
                    'message' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'type' => class_basename($e),
                ],
                'timestamp' => now()->toIso8601String(),
            ], $e->getCode() ?: 500);
        }
    }

    /**
     * Get email account details (if needed in future)
     * 
     * @param string $email
     * @return JsonResponse
     * 
     * GET /api/email-accounts/{email}
     */
    public function show(string $email): JsonResponse
    {
        try {
            // Parse the email to get domain
            $parts = explode('@', $email);
            $domain = $parts[1] ?? config('cpanel.default_domain');

            Log::info('Getting email account details', ['email' => $email]);

            // List all emails and filter for the specific one
            $emails = $this->cpanelService->listEmails($domain);
            
            $emailData = collect($emails)->firstWhere('email', $email);

            if (!$emailData) {
                return response()->json([
                    'success' => false,
                    'error' => [
                        'message' => "Email account not found: {$email}",
                        'code' => 404,
                    ],
                    'timestamp' => now()->toIso8601String(),
                ], 404);
            }

            return (new EmailAccountResource($emailData))
                ->response()
                ->setStatusCode(200);

        } catch (CpanelException $e) {
            Log::error('Failed to get email account details', [
                'email' => $email,
                'error' => $e->getMessage(),
            ]);

            return response()->json([
                'success' => false,
                'error' => [
                    'message' => $e->getMessage(),
                    'code' => $e->getCode(),
                    'type' => class_basename($e),
                ],
                'timestamp' => now()->toIso8601String(),
            ], $e->getCode() ?: 500);
        }
    }

    // =========================================================================
    // FUTURE METHODS - Uncomment when ready to implement
    // =========================================================================

    /**
     * Send welcome email to new account (implement when needed)
     */
    // protected function sendWelcomeEmail(string $email, string $password): void
    // {
    //     // TODO: Implement welcome email logic
    //     // Use Laravel's Mail facade to send instructions
    //     // Mail::to($email)->send(new WelcomeEmail($email, $password));
    // }

    /**
     * Bulk create email accounts (implement when needed)
     */
    // public function bulkStore(Request $request): JsonResponse
    // {
    //     // TODO: Validate array of email accounts
    //     // Loop through and create each one
    //     // Return summary of successes/failures
    // }
}
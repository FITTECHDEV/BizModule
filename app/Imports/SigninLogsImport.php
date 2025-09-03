<?php

namespace App\Imports;

use App\Models\Logintracker\SigninLog;
use App\Models\Logintracker\System;
use App\Models\Logintracker\AzureUser;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class SigninLogsImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return DB::transaction(function () use ($row) {
            try {
                $row = array_change_key_case($row, CASE_LOWER);
                $userPrincipalName = trim($row['username'] ?? '');
                $userDisplayName = trim($row['user'] ?? '');
                $userIdFromCsv = trim($row['user id'] ?? '');

                // Skip if username is empty
                if (empty($userPrincipalName)) {
                    Log::info('Skipped import due to empty username', ['row' => array_keys($row)]);
                    return null;
                }

                // IMPROVEMENT: Add validation for userPrincipalName length
                if (strlen($userPrincipalName) > 255) {
                    Log::error('userPrincipalName too long, skipping signin log', [
                        'userPrincipalName' => $userPrincipalName,
                        'length' => strlen($userPrincipalName)
                    ]);
                    return null;
                }

                // Use the model method to find or create user
                $user = AzureUser::findOrCreateUser($userPrincipalName, $userDisplayName, $userIdFromCsv);
                
                if (!$user) {
                    Log::error("User could not be found or created for signin log", [
                        'username' => $userPrincipalName,
                        'display_name' => $userDisplayName,
                        'user_id_from_csv' => $userIdFromCsv
                    ]);
                    return null;
                }

                $userId = $user->id;

                // IMPROVEMENT: Log successful user resolution
                Log::debug('Successfully resolved user for signin log', [
                    'username' => $userPrincipalName,
                    'resolved_user_id' => $userId
                ]);

                // Map application to system
                $application = $row['application'] ?? null;
                $normalizedApp = strtolower(trim($application ?? ''));
                $systemMapping = config('systemmap', []);
                $system = $systemMapping[$normalizedApp] ?? null;

                // Partial match for system
                if (!$system && !empty($normalizedApp)) {
                    foreach ($systemMapping as $appKey => $systemName) {
                        if (strpos($normalizedApp, $appKey) !== false || strpos($appKey, $normalizedApp) !== false) {
                            $system = $systemName;
                            break;
                        }
                    }
                }

                $system = $system ?? 'Unknown';

                // Auto-create system and associate with user
                if ($system !== 'Unknown' && $userId) {
                    $systemModel = System::firstOrCreate(['name' => $system]);
                    if ($systemModel && !$user->systems()->where('application_id', $systemModel->id)->exists()) {
                        $user->systems()->attach($systemModel->id);
                        Log::info("Associated user {$userPrincipalName} with system {$system}");
                    }
                }

                // Extract and validate date
                $dateUtc = $this->extractDateUtc($row);
                if (!$dateUtc) {
                    Log::warning('Skipped import due to missing date_utc', [
                        'username' => $userPrincipalName, 
                        'available_keys' => array_keys($row)
                    ]);
                    return null;
                }

                $parsedDate = $this->validateAndParseDate($dateUtc, $userPrincipalName);
                if (!$parsedDate) {
                    return null; // Already logged in validateAndParseDate
                }

                // Check for duplicate signin log
                $existingLog = SigninLog::where([
                    'user_id' => $userId,
                    'date_utc' => $parsedDate,
                    'request_id' => $row['request id'] ?? null,
                ])->first();

                if ($existingLog) {
                    Log::info('Skipped duplicate signin log', [
                        'user_id' => $userId,
                        'date_utc' => $parsedDate,
                        'request_id' => $row['request id'] ?? null
                    ]);
                    return null;
                }

                // FIXED: Map fields to match the model's $fillable array
                return new SigninLog([
                    'date_utc'                                      => $parsedDate,
                    'request_id'                                    => $row['request id'] ?? null,
                    'user_agent'                                    => $row['user agent'] ?? null,
                    'correlation_id'                                => $row['correlation id'] ?? null,
                    'user_id'                                       => $userId,
                    'system'                                        => $system,
                    'user'                                          => $row['user'] ?? null,
                    'username'                                      => $row['username'] ?? null,
                    'user_type'                                     => $row['user type'] ?? null,
                    'cross_tenant_access_type'                      => $row['cross tenant access type'] ?? null,
                    'incoming_token_type'                           => $row['incoming token type'] ?? null,
                    'authentication_protocol'                       => $row['authentication protocol'] ?? null,
                    'unique_token_identifier'                       => $row['unique token identifier'] ?? null,
                    'original_transfer_method'                      => $row['original transfer method'] ?? null,
                    'client_credential_type'                        => $row['client credential type'] ?? null,
                    'token_protection_sign_in_session'              => $row['token protection - sign in session'] ?? null,
                    'application'                                   => $application,
                    'application_id'                                => $row['application id'] ?? null,
                    'resource'                                      => $row['resource'] ?? null,
                    'resource_id'                                   => $row['resource id'] ?? null,
                    'resource_tenant_id'                            => $row['resource tenant id'] ?? null,
                    'resource_owner_tenant_id'                      => $row['resource owner tenant id'] ?? null,
                    'home_tenant_id'                                => $row['home tenant id'] ?? null,
                    'home_tenant_name'                              => $row['home tenant name'] ?? null,
                    'ip_address'                                    => $row['ip address'] ?? null,
                    'location'                                      => $row['location'] ?? null,
                    'status'                                        => $row['status'] ?? null,
                    'sign_in_error_code'                            => $row['sign-in error code'] ?? null,
                    'failure_reason'                                => $row['failure reason'] ?? null,
                    'client_app'                                    => $row['client app'] ?? null,
                    'device_id'                                     => $row['device id'] ?? null,
                    'browser'                                       => $row['browser'] ?? null,
                    'operating_system'                              => $row['operating system'] ?? null,
                    'compliant'                                     => $row['compliant'] ?? null,
                    'managed'                                       => $row['managed'] ?? null,
                    'join_type'                                     => $row['join type'] ?? null,
                    
                    // FIXED: Corrected field names to match model
                    'multifactor_authentication_result'             => $row['multifactor authentication result'] ?? null,
                    'multifactor_authentication_auth_method'        => $row['multifactor authentication auth method'] ?? null,
                    'multifactor_authentication_auth_detail'        => $row['multifactor authentication auth detail'] ?? null,
                    
                    'authentication_requirement'                    => $row['authentication requirement'] ?? null,
                    'sign_in_identifier'                            => $row['sign-in identifier'] ?? null,
                    'session_id'                                    => $row['session id'] ?? null,
                    'ip_address_seen_by_resource'                   => $row['ip address (seen by resource)'] ?? null,
                    'through_global_secure_access'                  => $row['through global secure access'] ?? null,
                    
                    // FIXED: Corrected field name
                    'global_secure_access_ip_address'               => $row['global secure access ip address'] ?? null,
                    
                    'autonomous_system_number'                      => $row['autonomous system number'] ?? null,
                    'flagged_for_review'                            => $row['flagged for review'] ?? null,
                    'token_issuer_type'                             => $row['token issuer type'] ?? null,
                    
                    // FIXED: Removed incorrect field mapping
                    'incoming_token_type_duplicate'                 => null, // This field exists in model but no CSV mapping
                    
                    'token_issuer_name'                             => $row['token issuer name'] ?? null,
                    'latency'                                       => $row['latency'] ?? null,
                    'conditional_access'                            => $row['conditional access'] ?? null,
                    'managed_identity_type'                         => $row['managed identity type'] ?? null,
                    'associated_resource_id'                        => $row['associated resource id'] ?? null,
                    'federated_token_id'                            => $row['federated token id'] ?? null,
                    
                    // FIXED: Corrected field name
                    'federated_token_issuer'                        => $row['federated token issuer'] ?? null,
                ]);

            } catch (\Exception $e) {
                Log::error('Failed to process sign-in log import row', [
                    'username' => $row['username'] ?? 'unknown',
                    'display_name' => $row['user'] ?? 'unknown',
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'row_keys' => array_keys($row),
                ]);
                return null;
            }
        });
    }

    /**
     * Extract date_utc from row data
     */
    private function extractDateUtc($row)
    {
        // Try common date field names
        $dateFields = ['date_utc', 'date (utc)', 'date', 'created date', 'created_date', 'timestamp'];
        
        foreach ($dateFields as $field) {
            if (isset($row[$field]) && !empty(trim($row[$field]))) {
                return trim($row[$field]);
            }
        }
        
        return null;
    }

    /**
     * Validate and parse date string
     */
    private function validateAndParseDate($dateString, $username = null)
    {
        try {
            // Handle various date formats
            $formats = [
                'Y-m-d H:i:s',
                'Y-m-d\TH:i:s\Z',
                'Y-m-d\TH:i:s.u\Z',
                'Y-m-d\TH:i:s',
                'm/d/Y H:i:s',
                'd/m/Y H:i:s',
                'Y-m-d',
                'm/d/Y',
                'd/m/Y'
            ];
            
            foreach ($formats as $format) {
                try {
                    $date = Carbon::createFromFormat($format, $dateString);
                    if ($date) {
                        return $date->format('Y-m-d H:i:s');
                    }
                } catch (\Exception $e) {
                    // Continue to next format
                    continue;
                }
            }
            
            // Try Carbon's built-in parsing as fallback
            $date = Carbon::parse($dateString);
            if ($date) {
                return $date->format('Y-m-d H:i:s');
            }
            
        } catch (\Exception $e) {
            Log::warning('Failed to parse date', [
                'username' => $username,
                'date_string' => $dateString,
                'error' => $e->getMessage()
            ]);
        }
        
        return null;
    }
}
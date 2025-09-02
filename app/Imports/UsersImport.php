<?php

namespace App\Imports;

use App\Models\Logintracker\AzureUser;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class UsersImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        return DB::transaction(function () use ($row) {
            try {
                $row = array_change_key_case($row, CASE_LOWER);
                $userPrincipalName = trim($row['userprincipalname'] ?? '');

                // Skip if no userPrincipalName
                if (empty($userPrincipalName)) {
                    Log::info('Skipped import due to empty userPrincipalName', ['row' => $row]);
                    return null;
                }

                // Enhanced duplicate check with better logging
                $existingUser = AzureUser::whereRaw('LOWER(TRIM(userPrincipalName)) = ?', [strtolower($userPrincipalName)])->first();
                if ($existingUser) {
                    Log::info('Skipped import due to existing userPrincipalName', [
                        'userPrincipalName' => $userPrincipalName,
                        'existing_user_id' => $existingUser->id
                    ]);
                    return null;
                }

                // Validate and generate UUID for id
                $id = $this->generateValidUuid($row['id'] ?? null);

                // Ensure ID doesn't already exist
                while (AzureUser::where('id', $id)->exists()) {
                    $id = (string) Str::uuid();
                    Log::warning('Generated new UUID due to ID conflict', ['new_id' => $id]);
                }

                // Process usageLocation (2-character country code or null)
                $usageLocation = $this->processUsageLocation($row['usagelocation'] ?? null);

                // Format createdDateTime with better error handling
                $createdDateTime = $this->formatCreatedDateTime($row['createddatetime'] ?? null);

                // Enhanced data validation and sanitization
                $azureUser = new AzureUser([
                    'id'                        => $id,
                    'userPrincipalName'         => $userPrincipalName,
                    'displayName'               => $this->sanitizeString($row['displayname'] ?? null, 'Unknown'),
                    'surname'                   => $this->sanitizeString($row['surname'] ?? null, 'Unknown'),
                    'mail'                      => $this->sanitizeEmail($row['mail'] ?? null),
                    'givenName'                 => $this->sanitizeString($row['givenname'] ?? null, 'Unknown'),
                    'userType'                  => $this->sanitizeString($row['usertype'] ?? null, 'Member'),
                    'jobTitle'                  => $this->sanitizeString($row['jobtitle'] ?? null, 'Unknown'),
                    'department'                => $this->sanitizeString($row['department'] ?? null, 'Unknown'),
                    'accountEnabled'            => $this->processAccountEnabled($row['accountenabled'] ?? null),
                    'usageLocation'             => $usageLocation,
                    'streetAddress'             => $this->sanitizeString($row['streetaddress'] ?? null),
                    'state'                     => $this->sanitizeString($row['state'] ?? null),
                    'country'                   => $this->sanitizeString($row['country'] ?? null, 'Unknown'),
                    'officeLocation'            => $this->sanitizeString($row['officelocation'] ?? null),
                    'city'                      => $this->sanitizeString($row['city'] ?? null),
                    'postalCode'                => $this->sanitizeString($row['postalcode'] ?? null),
                    'telephoneNumber'           => $this->sanitizeString($row['telephonenumber'] ?? null),
                    'mobilePhone'               => $this->sanitizeString($row['mobilephone'] ?? null),
                    'alternateEmailAddress'     => $this->sanitizeEmail($row['alternateemailaddress'] ?? null),
                    'ageGroup'                  => $this->sanitizeString($row['agegroup'] ?? null),
                    'consentProvidedForMinor'   => $this->sanitizeString($row['consentprovidedforminor'] ?? null),
                    'legalAgeGroupClassification' => $this->sanitizeString($row['legalagegroupclassification'] ?? null),
                    'companyName'               => $this->sanitizeString($row['companyname'] ?? null, 'Unknown'),
                    'creationType'              => $this->sanitizeString($row['creationtype'] ?? null),
                    'directorySynced'           => $this->processDirectorySynced($row['directorysynced'] ?? null),
                    'invitationState'           => $this->sanitizeString($row['invitationstate'] ?? null),
                    'identityIssuer'            => $this->sanitizeString($row['identityissuer'] ?? null),
                ]);

                // Set createdDateTime explicitly
                $azureUser->setAttribute('createdDateTime', $createdDateTime);

                Log::info('Created AzureUser for import', [
                    'userPrincipalName' => $userPrincipalName,
                    'id' => $id,
                    'displayName' => $azureUser->displayName
                ]);

                return $azureUser;
            } catch (\Exception $e) {
                Log::error('Failed to process user import row', [
                    'userPrincipalName' => $row['userprincipalname'] ?? 'unknown',
                    'displayName' => $row['displayname'] ?? 'unknown',
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                    'row_keys' => array_keys($row),
                ]);
                return null;
            }
        });
    }
}
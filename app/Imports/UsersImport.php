<?php

namespace App\Imports;

use App\Models\Logintracker\AzureUser;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToCollection;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class UsersImport implements ToCollection, WithHeadingRow
{
    private $processed = 0;
    private $skipped = 0;
    private $importStartTime;

    public function __construct()
    {
        $this->importStartTime = now();
        Log::info("Starting UsersImport at " . $this->importStartTime);
    }

    /**
     * FIXED: Use collection method instead of model to prevent multiple processing
     */
    public function collection(Collection $rows)
    {
        Log::info("Processing collection with " . $rows->count() . " rows");
        
        foreach ($rows as $index => $row) {
            try {
                $this->processRow($row->toArray(), $index + 2); // +2 for header row and 0-based index
            } catch (\Exception $e) {
                Log::error("Failed to process row " . ($index + 2), [
                    'error' => $e->getMessage(),
                    'row' => $row->toArray()
                ]);
                $this->skipped++;
            }
        }
        
        Log::info("Import completed", [
            'processed' => $this->processed,
            'skipped' => $this->skipped,
            'duration' => $this->importStartTime->diffInSeconds(now()) . ' seconds'
        ]);
    }

    private function processRow(array $row, int $rowNumber)
    {
        $row = array_change_key_case($row, CASE_LOWER);
        $userPrincipalName = trim($row['userprincipalname'] ?? '');

        // Skip if no userPrincipalName
        if (empty($userPrincipalName)) {
            Log::debug("Row {$rowNumber}: Skipped due to empty userPrincipalName");
            $this->skipped++;
            return;
        }

        // Validate data lengths early
        if (strlen($userPrincipalName) > 255) {
            Log::error("Row {$rowNumber}: userPrincipalName too long", [
                'userPrincipalName' => substr($userPrincipalName, 0, 50) . '...',
                'length' => strlen($userPrincipalName)
            ]);
            $this->skipped++;
            return;
        }

        // FIXED: Use updateOrCreate to prevent duplicates
        $user = $this->upsertAzureUser($userPrincipalName, $row, $rowNumber);
        
        if ($user) {
            $this->processed++;
            
            if ($this->processed % 50 == 0) {
                Log::info("Progress: {$this->processed} users processed");
            }
        } else {
            $this->skipped++;
        }
    }

    /**
     * SIMPLIFIED: Use updateOrCreate for atomic upsert
     */
    private function upsertAzureUser($userPrincipalName, $row, $rowNumber)
    {
        try {
            $userData = $this->prepareUserData($row);
            
            // Use updateOrCreate for atomic operation
            $user = AzureUser::updateOrCreate(
                ['userPrincipalName' => $userPrincipalName],
                $userData
            );
            
            $action = $user->wasRecentlyCreated ? 'created' : 'updated';
            
            Log::debug("Row {$rowNumber}: {$action} user", [
                'userPrincipalName' => $userPrincipalName,
                'displayName' => $user->displayName,
                'azure_id' => $user->azure_id
            ]);
            
            return $user;
            
        } catch (\Exception $e) {
            Log::error("Row {$rowNumber}: Failed to upsert user", [
                'userPrincipalName' => $userPrincipalName,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);
            return null;
        }
    }

    /**
     * Keep your existing prepareUserData method - it's good
     */
    private function prepareUserData($row)
    {
        $azureId = $this->generateValidUuid($row['id'] ?? null);
        $createdDateTime = $this->formatCreatedDateTime($row['createddatetime'] ?? null);
        
        return [
            'azure_id' => $azureId,
            'displayName' => $this->sanitizeString($row['displayname'] ?? null, 'Unknown User', 255),
            'surname' => $this->sanitizeString($row['surname'] ?? null, null, 100),
            'mail' => $this->sanitizeEmail($row['mail'] ?? null),
            'givenName' => $this->sanitizeString($row['givenname'] ?? null, null, 100),
            'userType' => $this->sanitizeString($row['usertype'] ?? null, 'Member', 50),
            'jobTitle' => $this->sanitizeString($row['jobtitle'] ?? null, null, 255),
            'department' => $this->sanitizeString($row['department'] ?? null, null, 255),
            'accountEnabled' => $this->processAccountEnabled($row['accountenabled'] ?? null),
            'usageLocation' => $this->processUsageLocation($row['usagelocation'] ?? null),
            'streetAddress' => $this->sanitizeString($row['streetaddress'] ?? null, null, 500),
            'state' => $this->sanitizeString($row['state'] ?? null, null, 100),
            'country' => $this->sanitizeString($row['country'] ?? null, null, 100),
            'officeLocation' => $this->sanitizeString($row['officelocation'] ?? null, null, 255),
            'city' => $this->sanitizeString($row['city'] ?? null, null, 100),
            'postalCode' => $this->sanitizeString($row['postalcode'] ?? null, null, 20),
            'telephoneNumber' => $this->sanitizeString($row['telephonenumber'] ?? null, null, 50),
            'mobilePhone' => $this->sanitizeString($row['mobilephone'] ?? null, null, 50),
            'alternateEmailAddress' => $this->sanitizeEmail($row['alternateemailaddress'] ?? null),
            'ageGroup' => $this->sanitizeString($row['agegroup'] ?? null, null, 50),
            'consentProvidedForMinor' => $this->sanitizeString($row['consentprovidedforminor'] ?? null, null, 50),
            'legalAgeGroupClassification' => $this->sanitizeString($row['legalagegroupclassification'] ?? null, null, 100),
            'companyName' => $this->sanitizeString($row['companyname'] ?? null, 'Freight in Time', 255),
            'creationType' => $this->sanitizeString($row['creationtype'] ?? null, null, 50),
            'directorySynced' => $this->processDirectorySynced($row['directorysynced'] ?? null),
            'invitationState' => $this->sanitizeString($row['invitationstate'] ?? null, null, 50),
            'identityIssuer' => $this->sanitizeString($row['identityissuer'] ?? null, null, 255),
            'createdDateTime' => $createdDateTime,
            'updated_at' => now()
        ];
    }

    // Keep all your existing helper methods (generateValidUuid, sanitizeString, etc.)
    private function generateValidUuid($id)
    {
        if (!empty($id) && is_string($id) && strlen($id) <= 100) {
            if (preg_match('/^[0-9a-f]{8}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{4}-[0-9a-f]{12}$/i', $id)) {
                return $id;
            }
            return $id;
        }
        return (string) Str::uuid();
    }

    private function sanitizeString($value, $default = null, $maxLength = null)
    {
        if ($value === null || $value === '' || trim($value) === '') {
            return $default;
        }

        $sanitized = trim($value);
        
        if ($maxLength && strlen($sanitized) > $maxLength) {
            Log::warning('String field truncated during import', [
                'original_length' => strlen($sanitized),
                'max_length' => $maxLength,
                'original_preview' => substr($sanitized, 0, 50) . '...'
            ]);
            $sanitized = substr($sanitized, 0, $maxLength);
        }
        
        return $sanitized;
    }

    private function sanitizeEmail($email)
    {
        if (empty($email)) return null;
        
        $email = trim($email);
        
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            Log::warning('Invalid email format during import', ['email' => $email]);
            return null;
        }
        
        if (strlen($email) > 255) {
            Log::warning('Email address too long during import', [
                'email' => substr($email, 0, 50) . '...',
                'length' => strlen($email)
            ]);
            return null;
        }
        
        return $email;
    }

    private function processUsageLocation($value)
    {
        if (empty($value)) return null;
        $cleaned = trim(strtoupper($value));
        
        if (strlen($cleaned) === 2 && ctype_alpha($cleaned)) {
            return $cleaned;
        }
        
        Log::debug('Invalid usage location format', ['value' => $value]);
        return null;
    }

    private function formatCreatedDateTime($dateTime)
    {
        if (empty($dateTime)) return null;
        
        try {
            $parsed = Carbon::parse($dateTime);
            return $parsed;
        } catch (\Exception $e) {
            Log::warning('Invalid date format during import', [
                'dateTime' => $dateTime,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    private function processAccountEnabled($value)
    {
        if ($value === null || $value === '') return true;
        
        if (is_string($value)) {
            $value = strtolower(trim($value));
            if (in_array($value, ['true', '1', 'yes', 'enabled'])) return true;
            if (in_array($value, ['false', '0', 'no', 'disabled'])) return false;
        }
        
        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE) ?? true;
    }

    private function processDirectorySynced($value)
    {
        if ($value === null || $value === '') return null;
        
        if (is_string($value)) {
            $value = strtolower(trim($value));
            if (in_array($value, ['true', '1', 'yes'])) return true;
            if (in_array($value, ['false', '0', 'no'])) return false;
        }
        
        return filter_var($value, FILTER_VALIDATE_BOOLEAN, FILTER_NULL_ON_FAILURE);
    }
}
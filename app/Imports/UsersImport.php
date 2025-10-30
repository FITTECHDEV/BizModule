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
 * Validates user data before database operations to prevent empty records
 */
private function validateUserBeforeUpsert($userPrincipalName, $userData, $rowNumber)
{
    // Critical field validation
    if (empty($userPrincipalName) || trim($userPrincipalName) === '') {
        throw new \InvalidArgumentException("Row {$rowNumber}: userPrincipalName cannot be empty");
    }
    
    if (empty($userData['displayName']) || trim($userData['displayName']) === '' || $userData['displayName'] === 'Unknown User') {
        Log::warning("Row {$rowNumber}: displayName is empty or default value", [
            'userPrincipalName' => $userPrincipalName,
            'displayName' => $userData['displayName']
        ]);
        // Don't throw error, but log it
    }
    
    // Validate email format
    if (!filter_var($userPrincipalName, FILTER_VALIDATE_EMAIL)) {
        throw new \InvalidArgumentException("Row {$rowNumber}: userPrincipalName must be a valid email format");
    }
    
  if (strpos($userPrincipalName, 'User-') === 0) {
    Log::warning("Row {$rowNumber}: Rejecting userPrincipalName with User- prefix", [
        'userPrincipalName' => $userPrincipalName,
        'reason' => 'System-generated ID format detected'
    ]);
    throw new \InvalidArgumentException("Row {$rowNumber}: userPrincipalName starting with 'User-' appears to be a system-generated ID, not a valid email");
}
    
    // Ensure azure_id is valid
    if (empty($userData['azure_id'])) {
        Log::warning("Row {$rowNumber}: azure_id is empty, generating new UUID", [
            'userPrincipalName' => $userPrincipalName
        ]);
        $userData['azure_id'] = (string) Str::uuid();
    }
    
    return $userData;
}

  /**
 * Enhanced upsertAzureUser method with validation
 */
private function upsertAzureUser($userPrincipalName, $row, $rowNumber)
{
    try {
        $userData = $this->prepareUserData($row);
        
        // Validate before database operation
        $userData = $this->validateUserBeforeUpsert($userPrincipalName, $userData, $rowNumber);
        
        // Use updateOrCreate with explicit where clause
        $user = AzureUser::updateOrCreate(
            ['userPrincipalName' => $userPrincipalName],
            $userData
        );
        
        // Verify the created/updated user has required data
        if (empty($user->userPrincipalName) || empty($user->displayName)) {
            Log::error("Row {$rowNumber}: Created user has empty critical fields", [
                'user_id' => $user->id,
                'userPrincipalName' => $user->userPrincipalName,
                'displayName' => $user->displayName
            ]);
            
            // Delete the malformed record
            $user->delete();
            throw new \Exception("Created user record is invalid - deleted to prevent corruption");
        }
        
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
 * Clean up empty or malformed user records
 */
public function cleanupEmptyUsers()
{
    try {
        Log::info("Starting cleanup of empty user records");
        
        $emptyUsers = AzureUser::where(function($query) {
            $query->whereNull('userPrincipalName')
                  ->orWhere('userPrincipalName', '')
                  ->orWhere('userPrincipalName', 'like', 'User-%')
                  ->orWhereNull('displayName')
                  ->orWhere('displayName', '')
                  ->orWhere('displayName', 'Unknown User');
        })->get();
        
        $deletedCount = 0;
        foreach ($emptyUsers as $user) {
            Log::info("Deleting empty user record", [
                'id' => $user->id,
                'userPrincipalName' => $user->userPrincipalName,
                'displayName' => $user->displayName
            ]);
            
            $user->delete();
            $deletedCount++;
        }
        
        Log::info("Cleanup completed", ['deleted_count' => $deletedCount]);
        
        return redirect()->back()->with('success', "Cleaned up {$deletedCount} empty user records.");
        
    } catch (\Exception $e) {
        Log::error('Error during user cleanup', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString(),
        ]);
        return redirect()->back()->with('error', 'An error occurred during cleanup.');
    }
}

 private function prepareUserData($row)
{
    $azureId = $this->generateValidUuid($row['id'] ?? null);
    $createdDateTime = $this->formatCreatedDateTime($row['createddatetime'] ?? null);
    
    // FIXED: Handle displayName properly
    $displayName = $this->sanitizeString($row['displayname'] ?? null, null, 255);
    $userPrincipalName = trim($row['userprincipalname'] ?? '');
    
    // If displayName is empty, generic, or looks problematic, extract from email
    if ($this->isGenericDisplayName($displayName)) {
        $displayName = $this->extractDisplayNameFromEmail($userPrincipalName);
        Log::info("Generated displayName from email", [
            'userPrincipalName' => $userPrincipalName,
            'generatedDisplayName' => $displayName
        ]);
    }
    
    return [
         'id' => $azureId,
        'azure_id' => $azureId,
        'displayName' => $displayName,
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



/**
 * Check if display name is generic or problematic
 */
private function isGenericDisplayName(?string $displayName): bool
{
    if (empty($displayName)) {
        return true;
    }
    
    $genericNames = [
        // 'Marilign Anawte',
        'Unknown User',
        'User',
        'Guest',
        'Unknown',
        'N/A'
    ];
    
    return in_array(trim($displayName), $genericNames);
}


/**
 * Extract display name from email
 */
private function extractDisplayNameFromEmail(string $email): string
{
    if (strpos($email, '@') !== false) {
        $username = explode('@', $email)[0];
        // Truncate to 255 chars and capitalize first letter
        return ucfirst(substr($username, 0, 255));
    }
    
    return substr($email, 0, 255);
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
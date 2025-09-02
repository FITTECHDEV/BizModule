<?php

namespace App\Imports;

use App\Models\Logintracker\AzureUser;  // ✅ Correct: Use AzureUser, not User
use Illuminate\Support\Str;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class UsersImport implements ToModel, WithHeadingRow
{
    public function model(array $row)
    {
        $row = array_change_key_case($row, CASE_LOWER);
        
        // Skip if no UPN
        if (empty($row['userprincipalname'])) {
            Log::info('Skipped import due to empty userPrincipalName', ['row' => $row]);
            return null;
        }
        
        // Skip if user already exists by UPN - use AzureUser instead of User
        if (AzureUser::where('userPrincipalName', $row['userprincipalname'])->exists()) {
            Log::info('Skipped import due to existing userPrincipalName', ['userPrincipalName' => $row['userprincipalname']]);
            return null;
        }
        
        // Ensure a proper UUID
        $id = !empty($row['id']) ? $row['id'] : (string) Str::uuid();
        
        return new AzureUser([  // ✅ Changed from User to AzureUser
            'id'                        => $id,
            'userPrincipalName'         => $row['userprincipalname'],
            'displayName'               => $row['displayname'],
            'surname'                   => $row['surname'] ?? 'Unknown',
            'mail'                      => $row['mail'] ?? 'Unknown',
            'givenName'                 => $row['givenname'] ?? 'Unknown',
            'userType'                  => $row['usertype'] ?? 'Member',
            'jobTitle'                  => $row['jobtitle'] ?? 'Unknown',
            'department'                => $row['department'] ?? 'Unknown',
            'accountEnabled'            => isset($row['accountenabled']) ? filter_var($row['accountenabled'], FILTER_VALIDATE_BOOLEAN) : true,
            'usageLocation'             => $row['usagelocation'] ?? 'Unknown',
            'streetAddress'             => $row['streetaddress'] ?? null,
            'state'                     => $row['state'] ?? null,
            'country'                   => $row['country'] ?? 'Unknown',
            'officeLocation'            => $row['officelocation'] ?? null,
            'city'                      => $row['city'] ?? null,
            'postalCode'                => $row['postalcode'] ?? null,
            'telephoneNumber'           => $row['telephonenumber'] ?? null,
            'mobilePhone'               => $row['mobilephone'] ?? null,
            'alternateEmailAddress'     => $row['alternateemailaddress'] ?? null,
            'ageGroup'                  => $row['agegroup'] ?? null,
            'consentProvidedForMinor'   => $row['consentprovidedforminor'] ?? null,
            'legalAgeGroupClassification'=> $row['legalagegroupclassification'] ?? null,
            'companyName'               => $row['companyname'] ?? 'Unknown',
            'creationType'              => $row['creationtype'] ?? null,
            'directorySynced'           => !empty($row['directorysynced']) ? 1 : 0,
            'invitationState'           => $row['invitationstate'] ?? null,
            'identityIssuer'            => $row['identityissuer'] ?? null,
            'createdDateTime'           => !empty($row['createddatetime']) ? Carbon::parse($row['createddatetime']) : Carbon::now(),
        ]);
    }
}
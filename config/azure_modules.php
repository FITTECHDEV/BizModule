<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Azure Module to ApplicationCode Mapping
    |--------------------------------------------------------------------------
    |
    | This maps module names from Azure_SoftwareMaster table to their
    | corresponding ApplicationCode values used in Azure_Users_Role table.
    |
    | When a module is selected in the UI, the controller uses this mapping
    | to determine which ApplicationCode to pass to sp_UserCreation_ResourceRole
    | stored procedure for fetching roles.
    |
    | Format: 'Module Name' => 'ApplicationCode'
    |
    */
    
    'module_mapping' => [
        // Native mappings (module has own roles in Azure_Users_Role)
        'OPS' => 'OPS',                          // 31 roles
        'OPSUAT' => 'OPS UAT',                   // 16 roles - maps to "OPS UAT" in DB
        'BIZPRO' => 'BIZ PRO',                   // 15 roles - maps to "BIZ PRO" in DB
        'D365' => 'D365 LIVE',                   // 8 roles - default D365 maps to LIVE
        'D365LIVE' => 'D365 LIVE',               // 8 roles - maps to "D365 LIVE" in DB
        'D365UAT' => 'D365 UAT',                 // 8 roles - maps to "D365 UAT" in DB
        'FITEXPRESS' => 'FIT Express',           // 5 roles - maps to "FIT Express" in DB
        'FITEXPRESSUAT' => 'FIT Express UAT',    // 5 roles - maps to "FIT Express UAT" in DB
        
        // Mapped modules (uses roles from another application)
        'BIZUAT' => 'BIZ PRO',         // Maps to BIZ PRO (15 roles)
        'ODOO' => 'BIZ PRO',           // Maps to BIZ PRO (15 roles)
        'WINDOWSSIGNIN' => 'OPS',      // Maps to OPS (31 roles)
    ],

    /*
    |--------------------------------------------------------------------------
    | Debug Mode
    |--------------------------------------------------------------------------
    |
    | When enabled, additional logging will be added to help troubleshoot
    | module mapping issues.
    |
    */
    'debug' => env('AZURE_MODULE_DEBUG', false),
];
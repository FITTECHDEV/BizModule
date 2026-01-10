<?php

return [
    
    'module_mapping' => [
        // Each module uses its OWN roles
        'OPS' => 'OPS',
        'OPS UAT' => 'OPS UAT',           // ✅ Has its own 16 roles
        'BIZ PRO' => 'BIZ PRO',           // ✅ Has its own 15 roles
        'BIZ UAT' => 'BIZ UAT',           // ✅ Should have its own roles
        'D365 LIVE' => 'D365 LIVE',       // ✅ Has its own 8 roles
        'D365 UAT' => 'D365 UAT',         // ✅ Has its own 8 roles
        'FIT Express' => 'FIT Express',   // ✅ Has its own 5 roles
        'FIT Express UAT' => 'FIT Express UAT', // ✅ Has its own 5 roles
        'Odoo' => 'Odoo',
        'Windows Sign In' => 'Windows Sign In',
    ],
    
    'scm_modules' => [
        'OPS',
        'OPS UAT',
    ],
    
    'debug' => false,
    
];
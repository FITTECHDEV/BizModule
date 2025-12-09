<?php

return [
    
    'module_mapping' => [
        // SCM Modules (work directly with stored procedure)
        'OPS' => 'OPS',
        'OPS UAT' => 'OPS',  // ✅ CHANGE THIS from 'OPSUAT' to 'OPS'
        
        // Other modules - map to OPS (which has GUIDs)
        'Odoo' => 'OPS',
        'D365 LIVE' => 'OPS',
        'D365 UAT' => 'OPS',
        'BIZ PRO' => 'OPS',
        'BIZ UAT' => 'OPS',
        'FIT Express' => 'OPS',
        'FIT Express UAT' => 'OPS',
        'Windows Sign In' => 'OPS',
    ],
    
    'scm_modules' => [
        'OPS',
        'OPS UAT',
    ],
    
    'debug' => false,
    
];
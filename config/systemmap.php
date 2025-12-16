<?php
return [
    // ========================================================================
    // CRITICAL FIX: Corrected BIZ PRO and BIZ UAT mappings
    // ========================================================================
    
    // BIZ PRO - FIXED: All map to 'BIZ PRO' (was 'Biz PRO')
    'BIZ PRO' => 'BIZ PRO',
    'biz pro' => 'BIZ PRO',
    'Biz Pro' => 'BIZ PRO',
    'BizPro' => 'BIZ PRO',
    'bizpro' => 'BIZ PRO',
    'BIZ Pro' => 'BIZ PRO',
    'biz_pro' => 'BIZ PRO',
    'biz-pro' => 'BIZ PRO',
    
    // ========================================================================
    // 🔥 CRITICAL FIX: FIT ERP maps to BIZ PRO (was incorrectly mapping to Odoo)
    // ========================================================================
    'FIT ERP' => 'BIZ PRO',
    'fit erp' => 'BIZ PRO',
    'FitERP' => 'BIZ PRO',
    'fiterp' => 'BIZ PRO',
    'fit_erp' => 'BIZ PRO',
    'fit-erp' => 'BIZ PRO',
    
    // BIZ UAT - FIXED: All map to 'BIZ UAT' (was 'BI UAT')
    'BIZ UAT' => 'BIZ UAT',
    'biz uat' => 'BIZ UAT',
    'Biz UAT' => 'BIZ UAT',
    'BizUAT' => 'BIZ UAT',
    'bizuat' => 'BIZ UAT',
    'BIZ Uat' => 'BIZ UAT',
    'biz_uat' => 'BIZ UAT',
    'biz-uat' => 'BIZ UAT',
    
    // D365 UAT
    'D365 UAT' => 'D365 UAT',
    'd365 uat' => 'D365 UAT',
    'D365UAT' => 'D365 UAT',
    'd365uat' => 'D365 UAT',
    'Dynamics 365 UAT' => 'D365 UAT',
    'dynamics 365 uat' => 'D365 UAT',
    'D365 Test' => 'D365 UAT',
    'd365 test' => 'D365 UAT',
    
    // ========================================================================
    // EXISTING MAPPINGS (unchanged)
    // ========================================================================
    
    // Windows Authentication variations
    'Windows Sign In' => 'Windows Sign In',
    'windows sign in' => 'Windows Sign In',
    'Windows Sign in' => 'Windows Sign In',
    'windows signin' => 'Windows Sign In',
    'Microsoft Authentication Broker' => 'Windows Sign In',
    'microsoft authentication broker' => 'Windows Sign In',
    'Microsoft Account Controls v2' => 'Windows Sign In',
    'microsoft account controls v2' => 'Windows Sign In',
    'Microsoft Device Registration Client' => 'Windows Sign In',
    'microsoft device registration client' => 'Windows Sign In',
    
    // D365 LIVE variations
    'D365 LIVE' => 'D365 LIVE',
    'd365 live' => 'D365 LIVE',
    'D365 Live' => 'D365 LIVE',
    'd365live' => 'D365 LIVE',
    'Dynamics 365' => 'D365 LIVE',
    'dynamics 365' => 'D365 LIVE',
    'dynamics365' => 'D365 LIVE',
    'Microsoft Dynamics 365' => 'D365 LIVE',
    'microsoft dynamics 365' => 'D365 LIVE',
    
    // FIT Express variations
    'FIT Express' => 'FIT Express',
    'fit express' => 'FIT Express',
    ' FIT Express' => 'FIT Express',
    ' fit express' => 'FIT Express',
    'FitExpress' => 'FIT Express',
    'fitexpress' => 'FIT Express',
    'fit_express' => 'FIT Express',
    'fit-express' => 'FIT Express',
    
    // FIT Express UAT variations
    'FIT Express UAT' => 'FIT Express UAT',
    'fit express uat' => 'FIT Express UAT',
    'FitExpress UAT' => 'FIT Express UAT',
    'fitexpressuat' => 'FIT Express UAT',
    'fit express test' => 'FIT Express UAT',
    'FIT Express Test' => 'FIT Express UAT',
    
    // OPS variations
    'OPS' => 'OPS',
    'ops' => 'OPS',
    'Operations' => 'OPS',
    'operations' => 'OPS',
    'OPS Portal' => 'OPS',
    'ops portal' => 'OPS',
    
    // OPS UAT
    'OPS UAT' => 'OPS UAT',
    'ops uat' => 'OPS UAT',
    'Operations UAT' => 'OPS UAT',
    'operations uat' => 'OPS UAT',
    
    // ========================================================================
    // 🔥 FIXED: Enhanced Odoo variations with robust mappings
    // ========================================================================
    'Odoo' => 'Odoo',
    'odoo' => 'Odoo',
    'ODOO' => 'Odoo',
    ' Odoo' => 'Odoo',          // with leading space
    'Odoo ' => 'Odoo',          // with trailing space
    ' Odoo ' => 'Odoo',         // with both spaces
    ' odoo' => 'Odoo',          // lowercase with leading space
    'odoo ' => 'Odoo',          // lowercase with trailing space
    ' odoo ' => 'Odoo',         // lowercase with both spaces
    'Odoo ERP' => 'Odoo',
    'odoo erp' => 'Odoo',
    'ODOO ERP' => 'Odoo',
    'Odoo System' => 'Odoo',
    'odoo system' => 'Odoo',
    'Odoo Production' => 'Odoo',
    'odoo production' => 'Odoo',
    'Odoo Live' => 'Odoo',
    'odoo live' => 'Odoo',
    
    // GeNeSIS variations
    'GeNeSIS' => 'GeNeSIS',
    'genesis' => 'GeNeSIS',
    'GENESIS' => 'GeNeSIS',
    'GeneIS' => 'GeNeSIS',
    
    // SCM (Supply Chain Management) → Maps to OPS
    'SCM' => 'OPS',
    'scm' => 'OPS',
    'Supply Chain Management' => 'OPS',
    'supply chain management' => 'OPS',
    'SCM Portal' => 'OPS',
    'scm portal' => 'OPS',
    'SCM System' => 'OPS',
    'scm system' => 'OPS',
    'Supply Chain' => 'OPS',
    'supply chain' => 'OPS',
    
    // Microsoft Office Suite
    'Microsoft 365 Support Service' => 'Office 365',
    'microsoft 365 support service' => 'Office 365',
    'Office 365' => 'Office 365',
    'office 365' => 'Office 365',
    'O365' => 'Office 365',
    'o365' => 'Office 365',
    'Microsoft 365' => 'Office 365',
    'microsoft 365' => 'Office 365',
    'M365' => 'Office 365',
    'm365' => 'Office 365',
    'OfficeHome' => 'Office 365',
    'officehome' => 'Office 365',
    'Office Online Core SSO' => 'Office 365',
    'office online core sso' => 'Office 365',
    'Microsoft App Access Panel' => 'Office 365',
    'microsoft app access panel' => 'Office 365',

    // Microsoft Office 365 Portal variations
    'Microsoft Office 365 Portal' => 'Office 365',
    'microsoft office 365 portal' => 'Office 365',
    'Office 365 Portal' => 'Office 365',
    'office 365 portal' => 'Office 365',

    // Microsoft AAD App Registration
    'Microsoft_AAD_RegisteredApps' => 'IT Infrastructure',
    'microsoft_aad_registeredapps' => 'IT Infrastructure',
    'Microsoft AAD RegisteredApps' => 'IT Infrastructure',
    'microsoft aad registeredapps' => 'IT Infrastructure',
    'Azure AD App Registration' => 'IT Infrastructure',
    'azure ad app registration' => 'IT Infrastructure',
    
    // Microsoft Teams variations
    'Microsoft Teams' => 'Microsoft Teams',
    'microsoft teams' => 'Microsoft Teams',
    'Microsoft Teams Web Client' => 'Microsoft Teams',
    'microsoft teams web client' => 'Microsoft Teams',
    'Teams' => 'Microsoft Teams',
    'teams' => 'Microsoft Teams',
    'MS Teams' => 'Microsoft Teams',
    'ms teams' => 'Microsoft Teams',
    'M365ChatClient' => 'Microsoft Teams',
    'm365chatclient' => 'Microsoft Teams',
    'Microsoft 365 Chat Client' => 'Microsoft Teams',
    'microsoft 365 chat client' => 'Microsoft Teams',
    
    // Azure and IT Infrastructure
    'Azure Portal' => 'IT Infrastructure',
    'azure portal' => 'IT Infrastructure',
    'Microsoft Azure CLI' => 'IT Infrastructure',
    'microsoft azure cli' => 'IT Infrastructure',
    'Azure Active Directory PowerShell' => 'IT Infrastructure',
    'azure active directory powershell' => 'IT Infrastructure',
    'Microsoft Azure PowerShell' => 'IT Infrastructure',
    'microsoft azure powershell' => 'IT Infrastructure',
    'TrendMicroZTSA' => 'IT Infrastructure',
    'trendmicroztsa' => 'IT Infrastructure',
    'Trend Micro ZTSA' => 'IT Infrastructure',
    'trend micro ztsa' => 'IT Infrastructure',
    'Cascade Authentication' => 'IT Infrastructure',
    'cascade authentication' => 'IT Infrastructure',
    
    // Browser and Web Applications
    'Microsoft Edge' => 'Web Browser',
    'microsoft edge' => 'Web Browser',
    'Chrome' => 'Web Browser',
    'chrome' => 'Web Browser',
    'Firefox' => 'Web Browser',
    'firefox' => 'Web Browser',
    
    // Handle empty, null, or "none" entries
    'none' => 'Unknown',
    'None' => 'Unknown',
    'NONE' => 'Unknown',
    '' => 'Unknown',
    null => 'Unknown',
    'N/A' => 'Unknown',
    'n/a' => 'Unknown',
    'Not Available' => 'Unknown',
    'not available' => 'Unknown',
    
    // Common Azure AD entries
    'Windows Azure Active Directory' => 'IT Infrastructure',
    'windows azure active directory' => 'IT Infrastructure',
    'Microsoft Entra ID' => 'IT Infrastructure',
    'microsoft entra id' => 'IT Infrastructure',
    'Azure AD' => 'IT Infrastructure',
    'azure ad' => 'IT Infrastructure',
    
    // Mobile and Desktop client applications
    'Other. Mobile Apps and Desktop clients' => 'Mobile/Desktop Client',
    'other. mobile apps and desktop clients' => 'Mobile/Desktop Client',
    'Mobile Apps and Desktop clients' => 'Mobile/Desktop Client',
    'mobile apps and desktop clients' => 'Mobile/Desktop Client',
    
    // Power Platform applications
    'PowerBI' => 'Power Platform',
    'powerbi' => 'Power Platform',
    'Power BI' => 'Power Platform',
    'power bi' => 'Power Platform',
    'PowerApps' => 'Power Platform',
    'powerapps' => 'Power Platform',
    'Power Apps' => 'Power Platform',
    'power apps' => 'Power Platform',
    'PowerAutomate' => 'Power Platform',
    'powerautomate' => 'Power Platform',
    'Power Automate' => 'Power Platform',
    'power automate' => 'Power Platform',
    
    // SharePoint variations
    'SharePoint' => 'SharePoint',
    'sharepoint' => 'SharePoint',
    'SharePoint Online' => 'SharePoint',
    'sharepoint online' => 'SharePoint',
    'Microsoft SharePoint Online' => 'SharePoint',
    'microsoft sharepoint online' => 'SharePoint',
    
    // OneDrive variations  
    'OneDrive' => 'OneDrive',
    'onedrive' => 'OneDrive',
    'OneDrive for Business' => 'OneDrive',
    'onedrive for business' => 'OneDrive',
    'Microsoft OneDrive' => 'OneDrive',
    'microsoft onedrive' => 'OneDrive',
    
    // ========================================================================
    // FITERPUAT → Still maps to Odoo (these are UAT/test instances)
    // ========================================================================
    'FITERPUAT' => 'Odoo',
    'fiterpuat' => 'Odoo',
    'FITERP UAT' => 'Odoo',
    'fiterp uat' => 'Odoo',
    'FIT ERP UAT' => 'Odoo',
    'fit erp uat' => 'Odoo',
];
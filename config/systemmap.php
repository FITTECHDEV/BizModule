<?php
return [
    // EXACT MATCHES from your actual CSV data
    // These are the precise application names found in your data
    
    // Windows Authentication variations (from your Mark Kariuki data)
    'Windows Sign In' => 'Windows Authentication',
    'windows sign in' => 'Windows Authentication',
    'Windows Sign in' => 'Windows Authentication',
    'windows signin' => 'Windows Authentication',
    'Microsoft Authentication Broker' => 'Windows Authentication',
    'microsoft authentication broker' => 'Windows Authentication',
    'Microsoft Account Controls v2' => 'Windows Authentication',
    'microsoft account controls v2' => 'Windows Authentication',
    'Microsoft Device Registration Client' => 'Windows Authentication',
    'microsoft device registration client' => 'Windows Authentication',
    
    // D365 LIVE variations (case-sensitive matches)
    'D365 LIVE' => 'D365 Live',
    'd365 live' => 'D365 Live',
    'D365 Live' => 'D365 Live',
    'd365live' => 'D365 Live',
    'Dynamics 365' => 'D365 Live',
    'dynamics 365' => 'D365 Live',
    'dynamics365' => 'D365 Live',
    'Microsoft Dynamics 365' => 'D365 Live',
    'microsoft dynamics 365' => 'D365 Live',
    
    // FIT Express variations (including the space-prefixed version from your data)
    'FIT Express' => 'Fit Express',
    'fit express' => 'Fit Express',
    ' FIT Express' => 'Fit Express', // Leading space version from your data
    ' fit express' => 'Fit Express',
    'FitExpress' => 'Fit Express',
    'fitexpress' => 'Fit Express',
    'fit_express' => 'Fit Express',
    'fit-express' => 'Fit Express',
    
    // FIT Express UAT variations
    'FIT Express UAT' => 'Fit Express UAT',
    'fit express uat' => 'Fit Express UAT',
    'FitExpress UAT' => 'Fit Express UAT',
    'fitexpressuat' => 'Fit Express UAT',
    'fit express test' => 'Fit Express UAT',
    'FIT Express Test' => 'Fit Express UAT',
    
    // FIT ERP variations
    'FIT ERP' => 'FIT ERP',
    'fit erp' => 'FIT ERP',
    'FitERP' => 'FIT ERP',
    'fiterp' => 'FIT ERP',
    'fit_erp' => 'FIT ERP',
    'fit-erp' => 'FIT ERP',
    
    // FITERPUAT (exact as it appears in your data)
    'FITERPUAT' => 'FITerp UAT',
    'fiterpuat' => 'FITerp UAT',
    'FITERP UAT' => 'FITerp UAT',
    'fiterp uat' => 'FITerp UAT',
    'FIT ERP UAT' => 'FITerp UAT',
    'fit erp uat' => 'FITerp UAT',
    
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
    
    // Odoo variations
    'Odoo' => 'Odoo',
    'odoo' => 'Odoo',
    'ODOO' => 'Odoo',
    
    // GeNeSIS variations
    'GeNeSIS' => 'GeNeSIS',
    'genesis' => 'GeNeSIS',
    'GENESIS' => 'GeNeSIS',
    'GeneIS' => 'GeNeSIS',
    
    // SCM (Supply Chain Management) - ADD THESE MAPPINGS
    'SCM' => 'SCM',
    'scm' => 'SCM',
    'Supply Chain Management' => 'SCM',
    'supply chain management' => 'SCM',
    'SCM Portal' => 'SCM',
    'scm portal' => 'SCM',
    'SCM System' => 'SCM',
    'scm system' => 'SCM',
    'Supply Chain' => 'SCM',
    'supply chain' => 'SCM',
    
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
    
    // Common Azure AD entries that might appear
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
];
@echo off
cd /d "C:\inetpub\wwwroot\Projects\UAT\erp_uat"
php artisan schedule:run 1>> NUL 2>&1

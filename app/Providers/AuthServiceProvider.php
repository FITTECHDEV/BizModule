<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;
use App\Models\AzureUser;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
    ];

    public function boot()
    {
        $this->registerPolicies();

        Gate::define('view-quotation', function ($user) {
            return Cache::remember("quotation_access_{$user->ID}", 300, function () use ($user) {
                $azureUser = AzureUser::where(function ($q) use ($user) {
                    $q->whereRaw('BizUserID = ?', [$user->ID])
                        ->orWhereRaw('LOWER(LTRIM(RTRIM(userPrincipalName))) = ?', [
                            strtolower(trim($user->Email ?? ''))
                        ]);
                })->first();

                if (!$azureUser || empty($azureUser->AzureId)) {
                    return false;
                }

                return DB::table('Azure_Application_UserMapping')
                    ->whereRaw('AzureUserID = CAST(? AS UNIQUEIDENTIFIER)', [$azureUser->AzureId])
                    ->whereRaw('UPPER(LTRIM(RTRIM(RoleCode))) = ?', ['QUOTATIONUSER'])
                    ->where('IsActive', 1)
                    ->where('IsDelete', 0)
                    ->exists();
            });
        });
    }
}

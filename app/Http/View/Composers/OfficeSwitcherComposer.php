<?php

namespace App\Http\View\Composers;

use Illuminate\View\View;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OfficeSwitcherComposer
{
    public function compose(View $view): void
    {
        if (!Auth::check()) {
            $view->with('switcherBaseOffice', null);
            $view->with('switcherOtherOffices', []);
            $view->with('switcherActiveCode', null);
            return;
        }

        try {
            $authUser  = Auth::user();

            $azureUser = DB::table('Azure_Users')
                ->where(function ($q) use ($authUser) {
                    $q->whereRaw('BizUserID = ?', [$authUser->ID])
                        ->orWhereRaw('LOWER(LTRIM(RTRIM(userPrincipalName))) = ?', [strtolower(trim($authUser->Email ?? ''))]);
                })
                ->whereNotNull('AzureId')
                ->first();

            if (!$azureUser) {
                $view->with('switcherBaseOffice', null);
                $view->with('switcherOtherOffices', []);
                $view->with('switcherActiveCode', null);
                return;
            }

            $activeCode = session('active_office_code');
            $azureId    = $azureUser->AzureId;
            $office     = '';
            $outputJson = null;

            $pdo  = DB::connection()->getPdo();
            $stmt = $pdo->prepare("
                DECLARE @OutputJson NVARCHAR(MAX);
                EXEC sp_Azure_BIZOfficeandModuleList
                    @AzureID    = ?,
                    @OfficeCode = ?,
                    @OutputJson = @OutputJson OUTPUT;
                SELECT @OutputJson AS OutputJson;
            ");
            $stmt->bindParam(1, $azureId, \PDO::PARAM_STR);
            $stmt->bindParam(2, $office,  \PDO::PARAM_STR);
            $stmt->execute();

            do {
                $row = $stmt->fetch(\PDO::FETCH_OBJ);
                if ($row && isset($row->OutputJson)) {
                    $outputJson = $row->OutputJson;
                    break;
                }
            } while ($stmt->nextRowset());

            if (empty($outputJson)) {
                $view->with('switcherBaseOffice', null);
                $view->with('switcherOtherOffices', []);
                $view->with('switcherActiveCode', $activeCode);
                return;
            }

            $data = json_decode($outputJson, true);

            if (!is_array($data) || (isset($data['Status']) && $data['Status'] === 'Failed')) {
                $view->with('switcherBaseOffice', null);
                $view->with('switcherOtherOffices', []);
                $view->with('switcherActiveCode', $activeCode);
                return;
            }

            $baseOffice   = $data['Basedoffice'] ?? $azureUser->BaseOfficeCode ?? null;
            $otherOffices = $data['OtherOffices'] ?? [];

            if (!$activeCode && $baseOffice) {
                $activeCode = $baseOffice;
                session(['active_office_code' => $activeCode]);
            }

            $view->with('switcherBaseOffice',   $baseOffice);
            $view->with('switcherOtherOffices', $otherOffices);
            $view->with('switcherActiveCode',   $activeCode);
        } catch (\Exception $e) {
            Log::error('OfficeSwitcherComposer failed: ' . $e->getMessage());
            $view->with('switcherBaseOffice',   null);
            $view->with('switcherOtherOffices', []);
            $view->with('switcherActiveCode',   null);
        }
    }
}

<?php

namespace App\Http\Controllers;

use App\Models\AzureUser;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

/**
 * Stored Procedure Tester Controller
 * 
 * Routes to add to web.php:
 * 
 * Route::prefix('admin/sp-test')->middleware(['auth'])->group(function () {
 *     Route::get('/', [StoredProcedureTestController::class, 'index'])->name('sp-test.index');
 *     Route::get('/user/{user}', [StoredProcedureTestController::class, 'testUser'])->name('sp-test.user');
 *     Route::post('/d365/{user}', [StoredProcedureTestController::class, 'testD365'])->name('sp-test.d365');
 *     Route::post('/scm/{user}', [StoredProcedureTestController::class, 'testSCM'])->name('sp-test.scm');
 *     Route::post('/biz/{user}', [StoredProcedureTestController::class, 'testBIZ'])->name('sp-test.biz');
 *     Route::get('/check-sp-exists/{spName}', [StoredProcedureTestController::class, 'checkSPExists'])->name('sp-test.check-exists');
 * });
 */
class StoredProcedureTestController extends Controller
{
    /**
     * Show SP test dashboard
     * 
     * GET /admin/sp-test
     */
    public function index()
    {
        // Get sample pending users for testing
        $pendingUsers = DB::connection('sqlsrv')
            ->table('Azure_Users')
            ->where('Status', 'Pending')
            ->whereNotNull('AzureId')
            ->limit(10)
            ->get();

        // Check which SPs exist
        $spStatus = [
            'd365' => $this->checkStoredProcedureExists('sp_Azure_D365UserCreation'),
            'scm' => $this->checkStoredProcedureExists('sp_Azure_SCMUserCreation'),
            'biz' => $this->checkStoredProcedureExists('sp_Azure_FITSYSUserCreation'),
        ];

        return view('azure-management.admin.sp-test-index', compact('pendingUsers', 'spStatus'));
    }

    /**
     * Show detailed test page for specific user
     * 
     * GET /admin/sp-test/user/{user}
     */
    public function testUser(AzureUser $user)
    {
        // Get user's module assignments
        $d365Assignments = DB::connection('sqlsrv')
            ->table('Azure_Application_UserMapping')
            ->where('AzureUserID', $user->AzureId)
            ->where('ApplicationName', 'LIKE', '%D365%')
            ->where('IsActive', 1)
            ->where('IsDelete', 0)
            ->count();

        $scmAssignments = DB::connection('sqlsrv')
            ->table('Azure_Application_UserMapping')
            ->where('AzureUserID', $user->AzureId)
            ->where(function($q) {
                $q->where('ApplicationName', 'LIKE', '%OPS%')
                  ->orWhere('ApplicationName', 'LIKE', '%SCM%');
            })
            ->where('IsActive', 1)
            ->where('IsDelete', 0)
            ->count();

        $bizAssignments = DB::connection('sqlsrv')
            ->table('Azure_Application_UserMapping')
            ->where('AzureUserID', $user->AzureId)
            ->where(function($q) {
                $q->where('ApplicationName', 'LIKE', '%BIZ%')
                  ->orWhere('ApplicationName', 'LIKE', '%FITSYS%');
            })
            ->where('IsActive', 1)
            ->where('IsDelete', 0)
            ->count();

        // Check SP existence
        $spStatus = [
            'd365' => $this->checkStoredProcedureExists('sp_Azure_D365UserCreation'),
            'scm' => $this->checkStoredProcedureExists('sp_Azure_SCMUserCreation'),
            'biz' => $this->checkStoredProcedureExists('sp_Azure_FITSYSUserCreation'),
        ];

        return view('azure-management.admin.sp-test-user', compact(
            'user', 
            'd365Assignments', 
            'scmAssignments', 
            'bizAssignments',
            'spStatus'
        ));
    }

    /**
     * Test D365 SP in DRY RUN mode
     * 
     * POST /admin/sp-test/d365/{user}
     */
    public function testD365(AzureUser $user)
    {
        try {
            Log::info('=== D365 SP TEST (DRY RUN) ===', [
                'user_id' => $user->Id,
                'azure_id' => $user->AzureId,
                'tested_by' => Auth::id(),
            ]);

            // Validation
            if (empty($user->AzureId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'User has no Azure ID',
                    'details' => ['azure_id' => 'NULL'],
                ], 400);
            }

            // Check if SP exists
            $spExists = $this->checkStoredProcedureExists('sp_Azure_D365UserCreation');
            if (!$spExists['exists']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Stored procedure does not exist',
                    'details' => $spExists,
                ], 404);
            }

            // Check for D365 assignments
            $hasD365 = DB::connection('sqlsrv')
                ->table('Azure_Application_UserMapping')
                ->where('AzureUserID', $user->AzureId)
                ->where('ApplicationName', 'LIKE', '%D365%')
                ->where('IsActive', 1)
                ->where('IsDelete', 0)
                ->exists();

            if (!$hasD365) {
                return response()->json([
                    'success' => false,
                    'message' => 'User has no D365 assignments',
                    'recommendation' => 'Assign D365 LIVE module first',
                ], 400);
            }

            // Test SP execution (with transaction rollback to prevent actual changes)
            DB::connection('sqlsrv')->beginTransaction();

            try {
                $result = DB::connection('sqlsrv')->select('
                    SET NOCOUNT ON;
                    DECLARE @Status VARCHAR(15), @Remark VARCHAR(200);
                    EXEC sp_Azure_D365UserCreation ?, @Status OUTPUT, @Remark OUTPUT;
                    SELECT @Status as Status, @Remark as Remark;
                ', [$user->AzureId]);

                // ROLLBACK to prevent actual changes
                DB::connection('sqlsrv')->rollBack();

                $status = $result[0]->Status ?? 'UNKNOWN';
                $remark = $result[0]->Remark ?? 'No output';

                Log::info('D365 SP test completed', [
                    'status' => $status,
                    'remark' => $remark,
                ]);

                return response()->json([
                    'success' => true,
                    'message' => 'SP executed successfully (DRY RUN - no changes saved)',
                    'sp_status' => $status,
                    'sp_remark' => $remark,
                    'sp_exists' => $spExists,
                    'note' => 'Transaction was rolled back - no actual changes were made',
                ]);

            } catch (\Exception $spError) {
                DB::connection('sqlsrv')->rollBack();
                
                Log::error('D365 SP execution error', [
                    'error' => $spError->getMessage(),
                    'code' => $spError->getCode(),
                ]);

                return response()->json([
                    'success' => false,
                    'message' => 'SP execution failed',
                    'error' => $spError->getMessage(),
                    'error_code' => $spError->getCode(),
                    'sp_exists' => $spExists,
                ], 500);
            }

        } catch (\Exception $e) {
            Log::error('D365 SP test error', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Test failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Test SCM SP in DRY RUN mode
     * 
     * POST /admin/sp-test/scm/{user}
     */
    public function testSCM(AzureUser $user)
    {
        try {
            Log::info('=== SCM SP TEST (DRY RUN) ===', [
                'user_id' => $user->Id,
                'azure_id' => $user->AzureId,
                'tested_by' => Auth::id(),
            ]);

            if (empty($user->AzureId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'User has no Azure ID',
                ], 400);
            }

            $spExists = $this->checkStoredProcedureExists('sp_Azure_SCMUserCreation');
            if (!$spExists['exists']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Stored procedure does not exist',
                    'details' => $spExists,
                ], 404);
            }

            // Check for SCM assignments
            $hasSCM = DB::connection('sqlsrv')
                ->table('Azure_Application_UserMapping')
                ->where('AzureUserID', $user->AzureId)
                ->where(function($q) {
                    $q->where('ApplicationName', 'LIKE', '%OPS%')
                      ->orWhere('ApplicationName', 'LIKE', '%SCM%');
                })
                ->where('IsActive', 1)
                ->where('IsDelete', 0)
                ->exists();

            if (!$hasSCM) {
                return response()->json([
                    'success' => false,
                    'message' => 'User has no SCM/OPS assignments',
                    'recommendation' => 'Assign OPS module first',
                ], 400);
            }

            DB::connection('sqlsrv')->beginTransaction();

            try {
                $statement = DB::connection('sqlsrv')->getPdo()->prepare('
                    SET NOCOUNT ON;
                    DECLARE @Status VARCHAR(15), @Remark VARCHAR(200);
                    EXEC sp_Azure_SCMUserCreation ?, @Status OUTPUT, @Remark OUTPUT;
                    SELECT @Status as Status, @Remark as Remark;
                ');

                $statement->execute([$user->AzureId]);
                $result = $statement->fetchAll(\PDO::FETCH_OBJ);

                DB::connection('sqlsrv')->rollBack();

                $status = $result[0]->Status ?? 'UNKNOWN';
                $remark = $result[0]->Remark ?? 'No output';

                return response()->json([
                    'success' => true,
                    'message' => 'SP executed successfully (DRY RUN)',
                    'sp_status' => $status,
                    'sp_remark' => $remark,
                    'sp_exists' => $spExists,
                    'note' => 'Transaction was rolled back - no changes made',
                ]);

            } catch (\Exception $spError) {
                DB::connection('sqlsrv')->rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'SP execution failed',
                    'error' => $spError->getMessage(),
                    'sp_exists' => $spExists,
                ], 500);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Test failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Test BIZ/FITSYS SP in DRY RUN mode
     * 
     * POST /admin/sp-test/biz/{user}
     */
    public function testBIZ(AzureUser $user)
    {
        try {
            Log::info('=== BIZ/FITSYS SP TEST (DRY RUN) ===', [
                'user_id' => $user->Id,
                'azure_id' => $user->AzureId,
                'tested_by' => Auth::id(),
            ]);

            if (empty($user->AzureId)) {
                return response()->json([
                    'success' => false,
                    'message' => 'User has no Azure ID',
                ], 400);
            }

            $spExists = $this->checkStoredProcedureExists('sp_Azure_FITSYSUserCreation');
            if (!$spExists['exists']) {
                return response()->json([
                    'success' => false,
                    'message' => 'Stored procedure does not exist',
                    'details' => $spExists,
                ], 404);
            }

            // Get BIZ application
            $bizApp = DB::connection('sqlsrv')
                ->table('Azure_SoftwareMaster')
                ->where(function ($q) {
                    $q->where('Name', 'LIKE', '%FITSYS%')
                      ->orWhere('Name', 'LIKE', '%BIZ%');
                })
                ->where('IsActive', 1)
                ->where('IsDeleted', 0)
                ->first();

            if (!$bizApp) {
                return response()->json([
                    'success' => false,
                    'message' => 'BIZ/FITSYS application not found',
                ], 400);
            }

            // Check for BIZ assignments
            $hasBIZ = DB::connection('sqlsrv')
                ->table('Azure_Application_UserMapping')
                ->where('AzureUserID', $user->AzureId)
                ->where('application_id', $bizApp->ApplicationId)
                ->where('IsActive', 1)
                ->where('IsDelete', 0)
                ->exists();

            if (!$hasBIZ) {
                return response()->json([
                    'success' => false,
                    'message' => 'User has no BIZ/FITSYS assignments',
                    'recommendation' => 'Assign BIZ/FITSYS module first',
                ], 400);
            }

            DB::connection('sqlsrv')->beginTransaction();

            try {
                $statement = DB::connection('sqlsrv')->getPdo()->prepare('
                    SET NOCOUNT ON;
                    DECLARE @Status VARCHAR(15), @Remark VARCHAR(200);
                    EXEC sp_Azure_FITSYSUserCreation ?, ?, @Status OUTPUT, @Remark OUTPUT;
                    SELECT @Status as Status, @Remark as Remark;
                ');

                $statement->execute([$user->AzureId, $bizApp->ApplicationId]);
                $result = $statement->fetchAll(\PDO::FETCH_OBJ);

                DB::connection('sqlsrv')->rollBack();

                $status = $result[0]->Status ?? 'UNKNOWN';
                $remark = $result[0]->Remark ?? 'No output';

                return response()->json([
                    'success' => true,
                    'message' => 'SP executed successfully (DRY RUN)',
                    'sp_status' => $status,
                    'sp_remark' => $remark,
                    'sp_exists' => $spExists,
                    'application_id' => $bizApp->ApplicationId,
                    'note' => 'Transaction was rolled back - no changes made',
                ]);

            } catch (\Exception $spError) {
                DB::connection('sqlsrv')->rollBack();

                return response()->json([
                    'success' => false,
                    'message' => 'SP execution failed',
                    'error' => $spError->getMessage(),
                    'sp_exists' => $spExists,
                ], 500);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Test failed: ' . $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Check if a stored procedure exists (API endpoint)
     * 
     * GET /admin/sp-test/check-sp-exists/{spName}
     */
    public function checkSPExists(string $spName)
    {
        $result = $this->checkStoredProcedureExists($spName);
        
        return response()->json([
            'success' => true,
            'data' => $result,
        ]);
    }

    /**
     * Helper: Check if a stored procedure exists
     */
    private function checkStoredProcedureExists(string $spName): array
    {
        try {
            $query = "
                SELECT 
                    o.object_id,
                    o.name,
                    o.type_desc,
                    o.create_date,
                    o.modify_date,
                    SCHEMA_NAME(o.schema_id) as schema_name,
                    OBJECTPROPERTY(o.object_id, 'ExecIsStartUp') as is_startup_procedure,
                    CASE 
                        WHEN EXISTS (
                            SELECT 1 
                            FROM sys.sql_modules sm 
                            WHERE sm.object_id = o.object_id
                        ) THEN 1 
                        ELSE 0 
                    END as has_definition
                FROM sys.objects o
                WHERE o.name = ?
                AND o.type = 'P'
            ";

            $result = DB::connection('sqlsrv')->selectOne($query, [$spName]);

            if ($result) {
                // Get parameter info
                $parameters = DB::connection('sqlsrv')->select("
                    SELECT 
                        p.name as parameter_name,
                        TYPE_NAME(p.user_type_id) as data_type,
                        p.max_length,
                        p.is_output
                    FROM sys.parameters p
                    WHERE p.object_id = OBJECT_ID(?)
                    ORDER BY p.parameter_id
                ", [$spName]);

                return [
                    'exists' => true,
                    'name' => $result->name,
                    'schema' => $result->schema_name,
                    'type' => $result->type_desc,
                    'created' => $result->create_date,
                    'modified' => $result->modify_date,
                    'has_definition' => (bool)$result->has_definition,
                    'parameters' => $parameters,
                    'parameter_count' => count($parameters),
                ];
            }

            return [
                'exists' => false,
                'name' => $spName,
                'message' => 'Stored procedure not found',
            ];

        } catch (\Exception $e) {
            return [
                'exists' => false,
                'name' => $spName,
                'error' => $e->getMessage(),
            ];
        }
    }
}
<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

class ProcessCRMSCMBooking extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'crmscm:Booking
        {--debug : Enable debug mode}
        {--test-auth : Test authentication only}
        {--booking= : Process specific booking number}
        {--skip-delay : Run without 17-minute delay}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'This command Create Booking to SCM from CRM';

    private $startTime;
    private $debugMode = false;

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        // === INITIAL SETUP ===
        $this->startTime = now();
        $this->debugMode = $this->option('debug');

        Log::info("CRMSCM Command Started at: " . $this->startTime);
        if ($this->debugMode) {
            $this->info("Debug Mode is ENABLED");
        }

        // === CONDITIONAL DELAY ===
        if (!$this->option('skip-delay')) {
            if ($this->debugMode) {
                $this->info("Delaying execution for 17 minutes...");
            }
            Log::info("CRMSCM: Delaying execution for 17 minutes");
            sleep(1020); // 17 minutes
            Log::info("CRMSCM: Delay completed, proceeding with command execution");
        } else {
            Log::info("CRMSCM: Skip-delay option enabled, running immediately");
            if ($this->debugMode) {
                $this->info("⏩ Running without delay (skip-delay enabled)");
            }
        }

        // === TEST AUTH MODE SHORT CIRCUIT ===
        if ($this->option('test-auth')) {
            Log::info("CRMSCM: Running in TEST-AUTH mode");
            return $this->testAuthentication();
        }

        try {
            // === VALIDATE ENVIRONMENT ===
            $this->validateEnvironment();

            // === CONFIGURATION ===
            $batchSize = 10; // can be configured via config
            $specificBooking = $this->option('booking');

            Log::info("CRMSCM: Batch size set to {$batchSize}");
            if ($specificBooking) {
                Log::info("CRMSCM: Processing specific Booking ID: {$specificBooking}");
            }

            // === FETCH RECORDS ===
            $records = $this->getRecordsToProcess($batchSize, $specificBooking);
            $totalRecords = $records->count();
            Log::info("CRMSCM: Found {$totalRecords} record(s) to process");

            if ($totalRecords === 0) {
                $this->info("No records found to process");
                Log::info("CRMSCM: No records found - ending command");
                return;
            }

            // === PROCESS RECORDS ===
            $results = $this->processRecords($records);

            // === LOG SUMMARY & OUTPUT ===
            $this->logSummary($this->startTime, $totalRecords, $results);
            $this->displayResults($totalRecords, $results);
        } catch (\Exception $e) {
            $this->handleCommandException($e);
        }

        Log::info("CRMSCM Command Completed at: " . now());
    }

    // --- rest of your command logic unchanged until the end of the class ---

    private function cleanErrorDescription($description)
    {
        if (empty($description)) {
            return '';
        }

        if (is_array($description)) {
            $description = implode(' ', array_filter($description));
        }

        $description = (string) $description;
        $description = trim($description);
        $description = preg_replace('/\s+/', ' ', $description);
        $description = str_replace(["\r\n", "\n", "\r"], ' ', $description);
        $description = preg_replace('/<[^>]*>/', '', $description);
        $description = html_entity_decode($description);

        return trim($description);
    }

    public function fixUserIds(\Illuminate\Http\Request $request)
    {
        $usersWithoutId = DB::table('Azure_Users')
            ->whereNull('id')
            ->orWhere('id', '')
            ->count();

        if ($usersWithoutId === 0) {
            return response()->json([
                'status' => 'success',
                'message' => 'All users already have IDs',
                'users_checked' => DB::table('Azure_Users')->count()
            ]);
        }

        // Generate UUIDs for users without IDs
        $fixed = 0;
        $users = DB::table('Azure_Users')
            ->whereNull('id')
            ->orWhere('id', '')
            ->select('userPrincipalName')
            ->get();

        foreach ($users as $user) {
            $newId = (string) \Illuminate\Support\Str::uuid();
            DB::table('Azure_Users')
                ->where('userPrincipalName', $user->userPrincipalName)
                ->update(['id' => $newId]);
            $fixed++;
        }

        return response()->json([
            'status' => 'success',
            'message' => 'User IDs generated successfully',
            'users_fixed' => $fixed,
            'total_users' => DB::table('Azure_Users')->count()
        ]);
    }
}

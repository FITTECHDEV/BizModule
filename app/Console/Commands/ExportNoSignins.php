<?php
// ExportNoSignins.php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class ExportNoSignins extends Command
{
    protected $signature = 'export:no-signins';
    protected $description = 'Export users who have no sign-ins to a CSV file';

    public function handle()
    {
        $this->info('🔍 Fetching users without sign-ins...');

        // FIXED: Use correct table names
        $noSignins = DB::table('Azure_Users')
            ->whereNotIn('id', function ($q) {
                $q->select('user_id')->from('Azure_LoginDetails'); // FIXED: Use correct table name
            })
            ->orderBy('userPrincipalName')
            ->pluck('userPrincipalName');

        if ($noSignins->isEmpty()) {
            $this->warn('✅ All users have sign-ins. Nothing to export.');
            return;
        }

        // Compose CSV content
        $csvContent = "userPrincipalName\n" . $noSignins->implode("\n");

        // Save to public disk
        $filePath = 'no_signins.csv';
        Storage::disk('public')->put($filePath, $csvContent);

        $this->info("✅ Exported {$noSignins->count()} users to storage/app/public/{$filePath}");
    }
}
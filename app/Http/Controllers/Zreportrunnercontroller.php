<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Log;

class ZReportRunnerController extends Controller
{
    /**
     * Show the Z-Report runner UI.
     */
    public function index()
    {
        return view('zreport.Zreport_runner');  // resources/views/zreport-runner.blade.php
    }

    /**
     * Execute the appropriate Artisan command and return structured output.
     *
     * POST /zreport/run
     * Params: env (tz|ar), mode (auto|single|backfill), date?, backfill?
     */
    public function run(Request $request)
    {
        $env      = $request->query('env');       // 'tz' or 'ar'
        $mode     = $request->query('mode');      // 'auto' | 'single' | 'backfill'
        $date     = $request->query('date');      // Y-m-d or null
        $backfill = $request->query('backfill');  // Y-m-d, '' or null

        // Validate env
        if (!in_array($env, ['tz', 'ar'])) {
            return response()->json(['success' => false, 'error' => 'Invalid environment.'], 422);
        }

        // Resolve command signature
        $commandMap = [
            'tz' => 'z_report_json_tz',
            'ar' => 'z_report_json_ar',
        ];
        $command = $commandMap[$env];

        // Build Artisan arguments / options
        $arguments = [];
        $options   = [];

        if ($mode === 'single') {
            if (empty($date)) {
                return response()->json(['success' => false, 'error' => 'Date is required for single mode.'], 422);
            }
            $arguments['date'] = $date;

        } elseif ($mode === 'backfill') {
            // Pass backfill option; empty string uses the command's default
            $options['--backfill'] = $backfill ?? '';
        }
        // 'auto' mode: no arguments/options needed

        try {
            Log::info("ZReportRunner: dispatching command", compact('env', 'mode', 'command', 'arguments', 'options'));

            $exitCode = Artisan::call($command, array_merge($arguments, $options));
            $rawOutput = Artisan::output();

            // Parse output lines into typed objects for the terminal UI
            $lines = $this->parseOutputLines($rawOutput);

            Log::info("ZReportRunner: command finished", ['exit_code' => $exitCode, 'env' => $env]);

            return response()->json([
                'success' => $exitCode === 0,
                'lines'   => $lines,
                'exit_code' => $exitCode,
            ]);

        } catch (\Throwable $e) {
            Log::error("ZReportRunner: exception", [
                'env'     => $env,
                'message' => $e->getMessage(),
                'file'    => $e->getFile(),
                'line'    => $e->getLine(),
            ]);

            return response()->json([
                'success' => false,
                'error'   => $e->getMessage(),
                'lines'   => [
                    ['text' => 'Exception: ' . $e->getMessage(), 'type' => 'error'],
                ],
            ], 500);
        }
    }

    /**
     * Convert raw Artisan output into typed terminal lines.
     */
    private function parseOutputLines(string $raw): array
    {
        $lines = [];

        foreach (explode("\n", $raw) as $line) {
            $line = trim($line);
            if ($line === '') continue;

            // Detect type from content keywords
            $type = 'info';
            $lower = strtolower($line);

            if (str_contains($lower, 'error') || str_contains($lower, 'failed') || str_contains($lower, 'exception')) {
                $type = 'error';
            } elseif (str_contains($lower, 'success') || str_contains($lower, 'processed successfully') || str_contains($lower, 'finished')) {
                $type = 'success';
            } elseif (str_contains($lower, 'warn') || str_contains($lower, 'skip')) {
                $type = 'warn';
            }

            $lines[] = ['text' => $line, 'type' => $type];
        }

        return $lines;
    }
}

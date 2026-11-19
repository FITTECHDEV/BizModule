<?php

namespace App\Http\Controllers;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Throwable;

class OdooController extends Controller
{
    /**
     * POST /api/odoo/quotation
     *
     * Calls sp_Quote_QuotationCreate (insert-only, result via @OutputJson):
     *   { "Status": "Success"|"Failed", "QuoteNo": "...", "QuoteID": "...", "Remarks": [ { "Remark": "..." } ] }
     *
     * Logging (storage/logs, default channel). Every line carries [Odoo][<rid>]:
     *   grep "[Odoo][<rid>]"  -> the full story of one request
     *   grep "RESULT"         -> one summary line per request
     */
    public function createQuotation(Request $request): JsonResponse
    {
        $rid   = (string) Str::uuid();
        $start = microtime(true);
        $log   = Log::channel(config('logging.default'));

        // ---- 0. Log the raw request FIRST, before anything can reject it ----
        $raw       = $request->getContent();
        $decoded   = json_decode($raw, true);
        $jsonValid = json_last_error() === JSON_ERROR_NONE && is_array($decoded);
        $key       = (string) $request->header('X-API-KEY');
        $expected  = (string) config('services.odoo.api_key');
        $bookingNo = $request->input('bookingno');

        $log->info("[Odoo][$rid] >>> Incoming request", [
            'method'        => $request->method(),
            'url'           => $request->fullUrl(),
            'ip'            => $request->ip(),
            'forwarded_for' => $request->header('X-Forwarded-For'),
            'user_agent'    => $request->userAgent(),
            'content_type'  => $request->header('Content-Type'),
            'body_bytes'    => strlen($raw),
            'json_valid'    => $jsonValid,
            'bookingno'     => $bookingNo,
            'key_present'   => $key !== '',
            'key_fp'        => $key !== '' ? substr(hash('sha256', $key), 0, 8) : null,   // fingerprint only
            'app_env'       => config('app.env'),
            'database'      => $this->databaseName(),
        ]);

        $log->info("[Odoo][$rid] Raw payload received", [
            'bookingno' => $bookingNo,
            'raw_body'  => Str::limit($raw, 20000),
        ]);

        if (!$jsonValid) {
            $log->warning("[Odoo][$rid] Body is not a valid JSON object", [
                'json_error' => json_last_error_msg(),
            ]);
        }

        // ---- 1. Auth ----
        if ($expected === '' || !hash_equals($expected, $key)) {
            $log->warning("[Odoo][$rid] Unauthorized", [
                'ip'                => $request->ip(),
                'key_present'       => $key !== '',
                'key_configured'    => $expected !== '',
                'sent_key_fp'       => $key !== '' ? substr(hash('sha256', $key), 0, 8) : null,
                'expected_key_fp'   => $expected !== '' ? substr(hash('sha256', $expected), 0, 8) : null,
            ]);

            return $this->respond($log, $rid, $start, 'unauthorized', $bookingNo, [
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        // ---- 2. Minimal shape validation (the procedure does the business validation) ----
        $validator = Validator::make($request->all(), [
            'bookingno'                     => 'required|numeric',
            'team_id'                       => 'required|numeric',
            'ContainerList'                 => 'nullable|array',
            'ContainerList.*.ContainerType' => 'required_with:ContainerList|string|max:50',
            'ContainerList.*.Quantity'      => 'required_with:ContainerList|integer|min:1',
        ]);

        if ($validator->fails()) {
            $log->warning("[Odoo][$rid] Payload shape validation failed", [
                'errors'  => $validator->errors()->toArray(),
                'payload' => $request->all(),
            ]);

            return $this->respond($log, $rid, $start, 'invalid_payload', $bookingNo, [
                'success' => false,
                'message' => 'Invalid payload: ' . implode('; ', $validator->errors()->all()),
                'errors'  => $validator->errors(),
            ], 422);
        }

        $payload   = $request->all();
        $bookingNo = $payload['bookingno'];

        // SQL Server "datetime" only takes 3 decimal places; Odoo sends microseconds
        foreach (['createdon', 'lastmodifiedon'] as $field) {
            if (!empty($payload[$field])) {
                $before = $payload[$field];
                try {
                    $payload[$field] = Carbon::parse($before)->format('Y-m-d H:i:s.v');
                    if ($payload[$field] !== $before) {
                        $log->info("[Odoo][$rid] Normalised $field", ['from' => $before, 'to' => $payload[$field]]);
                    }
                } catch (Throwable $e) {
                    $log->warning("[Odoo][$rid] Could not parse $field, leaving as sent", ['value' => $before]);
                }
            }
        }

        try {
            // ---- 3. Call the procedure ----
            $json = json_encode($payload, JSON_UNESCAPED_UNICODE);

            $log->info("[Odoo][$rid] Calling sp_Quote_QuotationCreate", [
                'bookingno'  => $bookingNo,
                'database'   => $this->databaseName(),
                'json_bytes' => strlen($json),
                'payload'    => $payload,   // exactly what the procedure receives
            ]);

            $t          = microtime(true);
            $outputJson = $this->callProcedure($json);

            $log->info("[Odoo][$rid] Procedure returned", [
                'ms'           => round((microtime(true) - $t) * 1000),
                'output_bytes' => strlen((string) $outputJson),
                'output'       => Str::limit((string) $outputJson, 8000),
            ]);

            // ---- 4. Parse the result ----
            $repaired = false;
            $result   = $this->decodeOutput($outputJson, $repaired);

            if ($repaired) {
                $log->warning("[Odoo][$rid] @OutputJson was malformed (multiple remarks) and was repaired");
            }

            if ($result === null) {
                $log->error("[Odoo][$rid] Could not parse @OutputJson", ['raw' => $outputJson]);

                return $this->respond($log, $rid, $start, 'unreadable_output', $bookingNo, [
                    'success'   => false,
                    'bookingno' => $bookingNo,
                    'message'   => 'Quotation request processed but the response could not be read. Reference: ' . $rid,
                ], 500);
            }

            $status  = $result['Status']  ?? null;
            $quoteNo = $result['QuoteNo'] ?? null;
            $quoteId = $result['QuoteID'] ?? null;
            $remarks = $result['Remarks'] ?? [];

            // A single remark comes back as an object rather than a list
            if (isset($remarks['Remark'])) {
                $remarks = [$remarks];
            }
            $messages = array_map(fn ($r) => $r['Remark'] ?? (string) $r, is_array($remarks) ? $remarks : []);

            // ---- 5. Failure from the procedure ----
            if ($status !== 'Success') {
                $duplicate = stripos(implode(' ', $messages), 'already exists') !== false;

                $log->warning("[Odoo][$rid] Quotation not created", [
                    'bookingno' => $bookingNo,
                    'status'    => $status,
                    'duplicate' => $duplicate,
                    'remarks'   => $messages,
                ]);

                return $this->respond($log, $rid, $start, $duplicate ? 'duplicate' : 'validation_failed', $bookingNo, [
                    'success'   => false,
                    'bookingno' => $bookingNo,
                    'message'   => $messages ? implode('; ', $messages) : 'Validation failed',
                    'remarks'   => $remarks,
                ], 422);
            }

            // ---- 6. Success ----
            $log->info("[Odoo][$rid] Quotation created", [
                'bookingno' => $bookingNo,
                'quote_no'  => $quoteNo,
                'quote_id'  => $quoteId,
            ]);

            return $this->respond($log, $rid, $start, 'created', $bookingNo, [
                'success'   => true,
                'bookingno' => $bookingNo,
                'quote_no'  => $quoteNo,
                'quote_id'  => $quoteId,
                'message'   => $messages ? implode('; ', $messages) : 'Quotation created',
            ], 200);
        } catch (Throwable $e) {
            $log->error("[Odoo][$rid] Exception", [
                'bookingno' => $bookingNo,
                'class'     => get_class($e),
                'error'     => $e->getMessage(),
                'where'     => $e->getFile() . ':' . $e->getLine(),
                'trace'     => Str::limit($e->getTraceAsString(), 3000),
            ]);

            return $this->respond($log, $rid, $start, 'exception', $bookingNo, [
                'success'   => false,
                'bookingno' => $bookingNo,
                'message'   => 'Server error while creating quotation. Reference: ' . $rid,
            ], 500);
        }
    }

    /**
     * Calls the procedure and returns @OutputJson. A local variable receives the
     * OUTPUT value and is SELECTed back, which avoids fragile PDO OUTPUT binding.
     */
    private function callProcedure(string $json): ?string
    {
        $sql = <<<SQL
            SET NOCOUNT ON;
            DECLARE @out varchar(max);
            EXEC dbo.sp_Quote_QuotationCreate @Inputjson = ?, @OutputJson = @out OUTPUT;
            SELECT @out AS Result;
        SQL;

        $rows = DB::connection('sqlsrv')->select($sql, [$json]);

        return $rows[0]->Result ?? null;
    }

    /**
     * Decodes @OutputJson. When the procedure returns 2+ remarks, its Remarks
     * fragment is invalid JSON (WITHOUT_ARRAY_WRAPPER); repair that shape and retry.
     * $repaired is set to true when the repair was needed.
     */
    private function decodeOutput(?string $raw, bool &$repaired = false): ?array
    {
        $repaired = false;

        if (empty($raw)) {
            return null;
        }

        $decoded = json_decode($raw, true);
        if (json_last_error() === JSON_ERROR_NONE) {
            return $decoded;
        }

        $fixed = preg_replace('/"Remarks":\{/', '"Remarks":[{', $raw, 1);
        $fixed = preg_replace('/\}\}\s*$/', '}]}', $fixed, 1);

        $decoded = json_decode($fixed, true);

        if (json_last_error() === JSON_ERROR_NONE) {
            $repaired = true;
            return $decoded;
        }

        return null;
    }

    private function databaseName(): ?string
    {
        try {
            return DB::connection('sqlsrv')->getDatabaseName();
        } catch (Throwable $e) {
            return 'unavailable: ' . $e->getMessage();
        }
    }

    /**
     * Logs the full response body plus a one-line RESULT summary, then returns it.
     * The request id is also sent back as the X-Request-Id header.
     */
    private function respond($log, string $rid, float $start, string $outcome, $bookingNo, array $body, int $status): JsonResponse
    {
        $ms = round((microtime(true) - $start) * 1000);

        $log->info("[Odoo][$rid] <<< Response", [
            'http_status' => $status,
            'ms'          => $ms,
            'body'        => $body,
        ]);

        $log->info("[Odoo][$rid] RESULT outcome=$outcome bookingno=" . ($bookingNo ?? 'null') . " http=$status ms=$ms");

        return response()->json($body, $status)->header('X-Request-Id', $rid);
    }
}

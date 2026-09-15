<?php

namespace App\Services;

/**
 * Maps internal field names to the exact JSON keys expected by
 * [dbo].[sp_Contract_List].
 *
 * This SP is NOT routed through sp_Contract_Main and has NO @OutputJson
 * OUTPUT parameter — see ContractListClient for the calling convention.
 *
 * KNOWN SP QUIRKS (flagged, not fixed here — DBA-owned):
 * - COO is mandatory only when process is Export/Local; COD only when
 *   Import. Never both. (Contradicts earlier verbal spec of "4 always
 *   mandatory" — this mapper follows the SP as written.)
 * - Process = 'Local' appears to match zero rows in 'Parameter' mode
 *   (the WHERE clause has no branch for it) — a likely SP bug, not
 *   something this mapper works around.
 * - FromDate/ToDate must be YYYYMMDD (no dashes) — the SP inserts
 *   dashes itself via STUFF().
 */
class ContractListMapper
{
    private const FIELD_MAP = [
        'from_date'      => 'FromDate',      // YYYYMMDD, no dashes
        'to_date'        => 'ToDate',        // YYYYMMDD, no dashes
        'service'        => 'Service',       // 'Air'|'Sea'|'Road'
        'process'        => 'Process',       // 'Export'|'Import'|'Local'
        'coo'            => 'COO',           // mandatory iff process in (Export,Local)
        'cod'            => 'COD',           // mandatory iff process = Import
        'por'            => 'POR',
        'poe'            => 'POE',
        'pol'            => 'POL',
        'pod'            => 'POD',
        'inco_term'      => 'IncoTerm',
        'service_class'  => 'ServiceClass',
        'shipment_mode'  => 'ShipmentMode',
        'shipment_type'  => 'ShipmentType',
        'movement_type'  => 'MovementType',
    ];

    /** Weights used by the SP's own MatchScore calculation — kept here so
     *  the "exact match" ceiling can be computed client-side without
     *  duplicating SP logic elsewhere in the app. */
    public const MATCH_WEIGHTS = [
        'inco_term'     => 5,
        'por'           => 10,
        'poe'           => 10,
        'pol'           => 15,
        'pod'           => 15,
        'service_class' => 5,
        'shipment_mode' => 5,
        'shipment_type' => 5,
        'movement_type' => 5,
    ];

    /**
     * @param  string  $usedFor  'List' | 'Parameter'
     * @param  array<string,mixed>  $data
     */
    public static function build(string $usedFor, array $data, ?string $submittedBy = null): array
    {
        $payload = [
            'OperationType' => 'ContractInfoList',
            'SubmittedBy'   => $submittedBy ?? '',
            'UsedFor'       => $usedFor,
        ];

        foreach (self::FIELD_MAP as $internalKey => $spKey) {
            $payload[$spKey] = $data[$internalKey] ?? '';
        }

        return $payload;
    }

    /**
     * The maximum MatchScore achievable given which optional filters were
     * actually supplied — a row scoring this equals an "exact match" on
     * every optional filter provided.
     *
     * @param  array<string,mixed>  $data  Same shape passed to build().
     */
    public static function maxPossibleScore(array $data): int
    {
        $max = 0;
        foreach (self::MATCH_WEIGHTS as $key => $weight) {
            if (!empty($data[$key])) {
                $max += $weight;
            }
        }
        return $max;
    }
}
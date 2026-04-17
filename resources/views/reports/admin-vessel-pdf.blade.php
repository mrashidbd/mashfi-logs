<!DOCTYPE html>
<html>

<head>
    <title>Admin Report — {{ $vessel->vessel_name ?? 'N/A' }}</title>
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif; font-size: 9px; color: #1e293b; }
        .page-header { padding: 20px 25px; border-bottom: 3px solid #0f172a; margin-bottom: 10px; }
        .page-header h1 { font-size: 18px; font-weight: 800; color: #0f172a; text-transform: uppercase; letter-spacing: 1px; margin-bottom: 8px; }
        table { width: 100%; border-collapse: collapse; margin: 0 25px; }
        thead th {
            background-color: #0f172a; color: #e2e8f0; border-bottom: 2px solid #334155;
            padding: 6px 5px; text-align: left; font-size: 8px;
            font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px;
        }
        thead th.r { text-align: right; }
        tbody td { padding: 4px 5px; border-bottom: 1px solid #e2e8f0; font-size: 9px; }
        tbody td.r { text-align: right; font-family: 'Courier New', monospace; }
        tbody td.mono { font-family: 'Courier New', monospace; }
        tbody td.bold { font-weight: 700; }
        tbody tr:nth-child(even) { background-color: #f8fafc; }
        .match { color: #16a34a; font-weight: 700; }
        .diff { color: #dc2626; font-weight: 700; }
        .pending { color: #94a3b8; }
        .summary-bar {
            margin: 15px 25px 0; padding: 12px 15px;
            background: #0f172a; color: #e2e8f0; border-radius: 4px;
        }
        .summary-bar table { margin: 0; }
        .summary-bar td { border: none; padding: 3px 8px; font-size: 10px; font-weight: 700; color: #e2e8f0; }
        .footer {
            position: fixed; bottom: 0; width: 100%; text-align: center;
            font-size: 8px; color: #94a3b8; border-top: 1px solid #e2e8f0; padding: 6px 0;
        }
    </style>
</head>

<body>
    <div class="page-header">
        <h1>Vessel Survey Report — Admin</h1>
        <div style="font-size: 10px; color: #64748b; margin-top: 8px;">
            <p><strong>Vessel:</strong> {{ $vessel->vessel_name ?? $vessel->vessel_code }} &nbsp;|&nbsp; <strong>Arrival:</strong> {{ $vessel->arrival_date ? $vessel->arrival_date->format('d M Y') : 'N/A' }} &nbsp;|&nbsp; <strong>Generated:</strong> {{ now()->format('d M Y H:i') }}</p>
        </div>
    </div>

    <table>
        <thead>
            <tr>
                <th>SN</th>
                <th>LOG#</th>
                <th>TAG REF</th>
                <th>SPECIES</th>
                <th>ORIGIN</th>
                <th class="r">LEN</th>
                <th class="r">GB</th>
                <th class="r">PB</th>
                <th class="r">DIA</th>
                <th class="r">L.REF</th>
                <th class="r">D.REF</th>
                <th class="r">C.LEN</th>
                <th class="r">CBM</th>
                <th>BUYER</th>
                <th>STATUS</th>
                <th>SURVEYOR</th>
            </tr>
        </thead>
        <tbody>
            @php $totalVolume = 0; $surveyedCount = 0; @endphp
            @foreach($logs as $log)
            @php
                $totalVolume += $log->vol_cbm;
                if ($log->inspection) $surveyedCount++;
            @endphp
            <tr>
                <td class="mono">{{ $log->serial_no }}</td>
                <td class="mono">{{ $log->log_no ?? '-' }}</td>
                <td class="mono bold">{{ $log->tag_no }}</td>
                <td class="bold" style="text-transform: uppercase;">{{ $log->species }}</td>
                <td style="text-transform: uppercase;">{{ $log->origin ?? '-' }}</td>
                <td class="r mono">{{ $log->length ? number_format($log->length, 2) : '-' }}</td>
                <td class="r mono">{{ $log->girth_butt ? number_format($log->girth_butt, 2) : '-' }}</td>
                <td class="r mono">{{ $log->girth_top ? number_format($log->girth_top, 2) : '-' }}</td>
                <td class="r mono">{{ $log->diameter ? number_format($log->diameter, 2) : '-' }}</td>
                <td class="r mono" style="color: #ef4444;">{{ $log->l_ref ? number_format($log->l_ref, 2) : '-' }}</td>
                <td class="r mono" style="color: #ef4444;">{{ $log->d_ref ? number_format($log->d_ref, 2) : '-' }}</td>
                <td class="r mono">{{ $log->calc_length ? number_format($log->calc_length, 2) : '-' }}</td>
                <td class="r mono bold">{{ $log->vol_cbm ? number_format($log->vol_cbm, 3) : '-' }}</td>
                <td style="font-size: 8px;">{{ $log->buyer_name ?? '-' }}</td>
                <td>
                    @if(!$log->inspection)
                        <span class="pending">Pending</span>
                    @elseif($log->inspection->is_match)
                        <span class="match">✓ Match</span>
                    @else
                        <span class="diff">⚠ Diff</span>
                    @endif
                </td>
                <td style="font-size: 8px;">{{ $log->inspection?->surveyor?->name ?? '-' }}</td>
            </tr>
            @endforeach
        </tbody>
    </table>

    <div class="summary-bar">
        <table>
            <tr>
                <td>Total Logs: {{ $logs->count() }}</td>
                <td>Surveyed: {{ $surveyedCount }}</td>
                <td>Pending: {{ $logs->count() - $surveyedCount }}</td>
                <td style="text-align: right;">Total Volume: {{ number_format($totalVolume, 3) }} CBM</td>
            </tr>
        </table>
    </div>

    <div class="footer">
        Generated by TimberLog Verify System | Confidential — For Admin Use Only
    </div>
</body>

</html>

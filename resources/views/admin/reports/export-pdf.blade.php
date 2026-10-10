<!DOCTYPE html>
<html>
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>Laporan Rekapitulasi Fasilitas - {{ $dateStr }}</title>
    <style>
        body {
            font-family: 'Helvetica Neue', Helvetica, Arial, sans-serif;
            font-size: 9pt;
            color: #333333;
            margin: 15px;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #0d9488;
            padding-bottom: 10px;
            margin-bottom: 15px;
        }
        .header h1 {
            font-size: 15pt;
            margin: 0 0 4px 0;
            color: #0f172a;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }
        .header h2 {
            font-size: 11pt;
            margin: 0 0 5px 0;
            color: #0f766e;
            font-weight: normal;
        }
        .header p {
            font-size: 8pt;
            color: #64748b;
            margin: 0;
        }
        .meta-info {
            width: 100%;
            margin-bottom: 12px;
            font-size: 8.5pt;
        }
        .meta-info td {
            padding: 2px 4px;
        }
        table.data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 5px;
        }
        table.data-table th {
            background-color: #0f766e;
            color: #ffffff;
            font-weight: bold;
            font-size: 8pt;
            text-transform: uppercase;
            padding: 6px 5px;
            border: 1px solid #0d9488;
            text-align: center;
        }
        table.data-table td {
            padding: 5px 6px;
            border: 1px solid #cbd5e1;
            font-size: 8pt;
        }
        table.data-table tr:nth-child(even) {
            background-color: #f8fafc;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .text-left { text-align: left; }
        .font-bold { font-weight: bold; }
        .bg-total {
            background-color: #e2e8f0 !important;
            font-weight: bold;
        }
        .badge {
            display: inline-block;
            padding: 1px 5px;
            border-radius: 4px;
            font-size: 7.5pt;
            font-weight: bold;
        }
        .badge-active { background-color: #d1fae5; color: #065f46; }
        .badge-maintenance { background-color: #fef3c7; color: #92400e; }
        .badge-inactive { background-color: #f1f5f9; color: #475569; }
        .footer {
            margin-top: 25px;
            width: 100%;
            font-size: 8pt;
        }
        .footer td {
            vertical-align: top;
        }
        .signature-box {
            text-align: right;
            padding-right: 20px;
        }
    </style>
</head>
<body>

    <div class="header">
        <h1>SISTEM INFORMASI RESERVASI & PELAPORAN FASILITAS</h1>
        <h2>Laporan Rekapitulasi Okupansi & Frekuensi Kerusakan Fasilitas</h2>
        <p>Kampus Terpadu &bull; Jam Operasional: 07.00 - 20.00 WIB (13 Jam per Hari)</p>
    </div>

    <table class="meta-info">
        <tr>
            <td style="width: 15%;"><strong>Periode Data</strong></td>
            <td style="width: 35%;">: {{ ($from && $to) ? "{$from} s/d {$to}" : 'Keseluruhan / 30 Hari Terakhir' }}</td>
            <td style="width: 15%;"><strong>Dicetak Pada</strong></td>
            <td style="width: 35%;">: {{ now()->translatedFormat('d F Y, H:i') }} WIB</td>
        </tr>
        <tr>
            <td><strong>Total Fasilitas</strong></td>
            <td>: {{ $recapData->count() }} Fasilitas</td>
            <td><strong>Administrator</strong></td>
            <td>: {{ $adminName }}</td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 12%;">Tipe</th>
                <th style="width: 22%;">Nama Fasilitas</th>
                <th style="width: 16%;">Lokasi</th>
                <th style="width: 8%;">Kapasitas</th>
                <th style="width: 10%;">Status</th>
                <th style="width: 9%;">Reservasi Approved</th>
                <th style="width: 8%;">Jam Pakai</th>
                <th style="width: 8%;">Okupansi</th>
                <th style="width: 7%;">Laporan Kerusakan</th>
            </tr>
        </thead>
        <tbody>
            @foreach($recapData as $row)
                @php
                    $badgeClass = match($row['status']) {
                        'active' => 'badge-active',
                        'maintenance' => 'badge-maintenance',
                        default => 'badge-inactive',
                    };
                @endphp
                <tr>
                    <td>{{ $row['type'] }}</td>
                    <td class="font-bold">{{ $row['name'] }}</td>
                    <td>{{ $row['location'] }}</td>
                    <td class="text-center">{{ $row['capacity'] }} org</td>
                    <td class="text-center">
                        <span class="badge {{ $badgeClass }}">{{ $row['status_label'] }}</span>
                    </td>
                    <td class="text-center font-bold" style="color: #0f766e;">{{ $row['approved_reservations_count'] }}</td>
                    <td class="text-center">{{ $row['approved_hours'] }} Jam</td>
                    <td class="text-center font-bold" style="color: #4338ca;">{{ $row['occupancy_rate'] }}%</td>
                    <td class="text-center font-bold" style="color: #e11d48;">{{ $row['reports_count'] }}</td>
                </tr>
            @endforeach
        </tbody>
        <tfoot>
            <tr class="bg-total">
                <td colspan="5" class="text-right font-bold">TOTAL & RATA-RATA:</td>
                <td class="text-center font-bold">{{ $recapData->sum('approved_reservations_count') }}</td>
                <td class="text-center font-bold">{{ round($recapData->sum('approved_hours'), 1) }} Jam</td>
                <td class="text-center font-bold">{{ $recapData->count() > 0 ? round($recapData->avg('occupancy_rate'), 1) : 0 }}%</td>
                <td class="text-center font-bold">{{ $recapData->sum('reports_count') }}</td>
            </tr>
        </tfoot>
    </table>

    <table class="footer">
        <tr>
            <td style="width: 60%; color: #64748b; font-size: 7.5pt;">
                Dokumen ini digenerate secara otomatis oleh Sistem Reservasi Ruangan pada {{ now()->format('d/m/Y H:i:s') }}.<br>
                Data okupansi dihitung proporsional terhadap rentang hari dan jam operasional fasilitas kampus.
            </td>
            <td style="width: 40%;" class="signature-box">
                <p style="margin: 0 0 45px 0;">Mengetahui,<br><strong>Kepala Bagian Fasilitas & Aset</strong></p>
                <p style="margin: 0; font-weight: bold; text-decoration: underline;">{{ $adminName }}</p>
                <p style="margin: 0; font-size: 7.5pt; color: #64748b;">NIP. 19850412 201012 1 001</p>
            </td>
        </tr>
    </table>

</body>
</html>

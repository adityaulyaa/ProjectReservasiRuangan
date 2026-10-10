<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Rekap Okupansi & Kerusakan Fasilitas</title>
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
            font-family: Arial, sans-serif;
            font-size: 11pt;
        }
        th, td {
            border: 1px solid #999999;
            padding: 8px 10px;
        }
        th {
            background-color: #0f766e;
            color: #ffffff;
            font-weight: bold;
            text-align: center;
        }
        .header-title {
            font-size: 16pt;
            font-weight: bold;
            text-align: center;
            border: none;
            padding-bottom: 5px;
        }
        .header-sub {
            font-size: 10pt;
            color: #555555;
            text-align: center;
            border: none;
            padding-bottom: 15px;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .bg-total {
            background-color: #f1f5f9;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <table>
        <tr>
            <td colspan="9" class="header-title">REKAPITULASI OKUPANSI & KERUSAKAN FASILITAS KAMPUS</td>
        </tr>
        <tr>
            <td colspan="9" class="header-sub">
                Tanggal Ekspor: {{ $dateStr }} 
                @if($from && $to)
                    | Periode: {{ $from }} s/d {{ $to }}
                @else
                    | Periode: Keseluruhan / 30 Hari Terakhir
                @endif
                 | Jam Operasional: 07.00 - 20.00 WIB
            </td>
        </tr>
        <tr>
            <th>Tipe Fasilitas</th>
            <th>Nama Fasilitas</th>
            <th>Lokasi</th>
            <th>Kapasitas</th>
            <th>Status</th>
            <th>Jumlah Reservasi Approved</th>
            <th>Total Jam Penggunaan</th>
            <th>Okupansi (%)</th>
            <th>Jumlah Laporan Kerusakan</th>
        </tr>
        @foreach($recapData as $row)
            <tr>
                <td>{{ $row['type'] }}</td>
                <td>{{ $row['name'] }}</td>
                <td>{{ $row['location'] }}</td>
                <td class="text-center">{{ $row['capacity'] }}</td>
                <td class="text-center">{{ $row['status_label'] }}</td>
                <td class="text-center">{{ $row['approved_reservations_count'] }}</td>
                <td class="text-center">{{ $row['approved_hours'] }} Jam</td>
                <td class="text-center">{{ $row['occupancy_rate'] }}%</td>
                <td class="text-center">{{ $row['reports_count'] }}</td>
            </tr>
        @endforeach
        <tr class="bg-total">
            <td colspan="5" class="text-right font-bold">TOTAL & RATA-RATA:</td>
            <td class="text-center font-bold">{{ $recapData->sum('approved_reservations_count') }}</td>
            <td class="text-center font-bold">{{ round($recapData->sum('approved_hours'), 1) }} Jam</td>
            <td class="text-center font-bold">{{ $recapData->count() > 0 ? round($recapData->avg('occupancy_rate'), 1) : 0 }}%</td>
            <td class="text-center font-bold">{{ $recapData->sum('reports_count') }}</td>
        </tr>
    </table>
</body>
</html>

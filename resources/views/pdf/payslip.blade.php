<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <title>Slip Gaji</title>
    <style>
        body {
            font-family: 'Helvetica', 'Arial', sans-serif;
            font-size: 12px;
            color: #333;
            line-height: 1.5;
        }
        .page-break {
            page-break-after: always;
        }
        .header {
            text-align: center;
            border-bottom: 2px solid #333;
            padding-bottom: 10px;
            margin-bottom: 20px;
        }
        .header h1 {
            margin: 0;
            font-size: 20px;
            color: #111;
        }
        .header p {
            margin: 5px 0 0 0;
            font-size: 14px;
            color: #555;
        }
        .info-table {
            width: 100%;
            margin-bottom: 20px;
        }
        .info-table td {
            padding: 3px 0;
        }
        .info-label {
            width: 120px;
            font-weight: bold;
        }
        .content-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 20px;
        }
        .content-table th, .content-table td {
            border: 1px solid #ccc;
            padding: 8px;
        }
        .content-table th {
            background-color: #f5f5f5;
            font-weight: bold;
            text-align: left;
        }
        .amount {
            text-align: right;
        }
        .total-row {
            font-weight: bold;
            background-color: #f9f9f9;
        }
        .net-pay {
            margin-top: 30px;
            border: 2px solid #333;
            padding: 15px;
            text-align: center;
            background-color: #fdfdfd;
        }
        .net-pay h2 {
            margin: 0;
            font-size: 18px;
        }
        .net-pay .amount {
            font-size: 24px;
            font-weight: bold;
            color: #000;
            margin-top: 10px;
            display: block;
            text-align: center;
        }
        .footer {
            margin-top: 50px;
            width: 100%;
        }
        .signature-box {
            width: 250px;
            float: right;
            text-align: center;
        }
        .signature-line {
            border-top: 1px solid #333;
            margin-top: 60px;
            padding-top: 5px;
            font-weight: bold;
        }
        .clear {
            clear: both;
        }
    </style>
</head>
<body>

    @foreach($data as $index => $row)
        <div class="header">
            <h1>PT SURYA KOMPONEN NUSANTARA</h1>
            <p>Slip Gaji Karyawan - Periode {{ $periodText }}</p>
        </div>

        <table class="info-table">
            <tr>
                <td class="info-label">Nama Karyawan</td>
                <td>: {{ $row['name'] }}</td>
                <td class="info-label">Departemen</td>
                <td>: {{ $row['dept'] }}</td>
            </tr>
            <tr>
                <td class="info-label">NIK</td>
                <td>: {{ $row['nik'] }}</td>
                <td class="info-label">Jabatan</td>
                <td>: {{ $row['class'] }}</td>
            </tr>
        </table>

        <table class="content-table">
            <thead>
                <tr>
                    <th colspan="2">PENGHASILAN</th>
                    <th colspan="2">POTONGAN</th>
                </tr>
            </thead>
            <tbody>
                @php
                    $earnings = [
                        ['name' => 'Gaji Pokok', 'amount' => $row['basic']],
                    ];
                    if (($row['allowance'] ?? 0) > 0) {
                        $earnings[] = ['name' => 'Tunjangan Jabatan', 'amount' => $row['allowance']];
                    }
                    if (($row['overtime'] ?? 0) > 0 || ($row['overtime_hours'] ?? 0) > 0) {
                        $earnings[] = ['name' => 'Honor Lembur (' . ($row['overtime_hours'] ?? 0) . ' Jam)', 'amount' => $row['overtime']];
                    }
                    if (($row['reimburse'] ?? 0) > 0) {
                        $earnings[] = ['name' => 'Reimbursement (Tugas)', 'amount' => $row['reimburse']];
                    }

                    $deductions = [
                        ['name' => 'BPJS Kesehatan', 'amount' => $row['ded_bpjs_kes'] ?? 0],
                        ['name' => 'BPJS Ketenagakerjaan', 'amount' => $row['ded_bpjs_tk'] ?? 0],
                        ['name' => 'PPh 21', 'amount' => $row['ded_pph'] ?? 0],
                    ];
                    if (($row['ded_late'] ?? 0) > 0 || ($row['late_frequency'] ?? 0) > 0) {
                        $deductions[] = ['name' => 'Keterlambatan (' . ($row['late_frequency'] ?? 0) . ' Kali)', 'amount' => $row['ded_late'] ?? 0];
                    }

                    $maxRows = max(count($earnings), count($deductions));
                @endphp

                @for($i = 0; $i < $maxRows; $i++)
                <tr>
                    <td>{{ $earnings[$i]['name'] ?? '' }}</td>
                    <td class="amount">{{ isset($earnings[$i]) ? 'Rp ' . number_format($earnings[$i]['amount'], 0, ',', '.') : '' }}</td>
                    <td>{{ $deductions[$i]['name'] ?? '' }}</td>
                    <td class="amount">{{ isset($deductions[$i]) ? 'Rp ' . number_format($deductions[$i]['amount'], 0, ',', '.') : '' }}</td>
                </tr>
                @endfor

                <tr class="total-row">
                    <td>TOTAL PENGHASILAN</td>
                    <td class="amount">Rp {{ number_format($row['basic'] + ($row['allowance'] ?? 0) + $row['overtime'] + ($row['reimburse'] ?? 0), 0, ',', '.') }}</td>
                    <td>TOTAL POTONGAN</td>
                    <td class="amount">Rp {{ number_format($row['deductions'], 0, ',', '.') }}</td>
                </tr>
            </tbody>
        </table>

        <div class="net-pay">
            <h2>TAKE HOME PAY</h2>
            <span class="amount">Rp {{ number_format($row['thp'], 0, ',', '.') }}</span>
        </div>

        <div class="footer">
            <div class="signature-box">
                <p>Mengetahui,</p>
                <div class="signature-line">Finance & HRD</div>
            </div>
            <div class="clear"></div>
        </div>

        @if(!$loop->last)
            <div class="page-break"></div>
        @endif
    @endforeach

    <script>
        window.onload = function() {
            window.print();
        }
    </script>
</body>
</html>

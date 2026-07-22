<!DOCTYPE html>
<html lang="th">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>{{ $reportTitle }}</title>
    <style>
        @font-face {
            font-family: 'THSarabunNew';
            font-style: normal;
            font-weight: normal;
            src: url("{{ public_path('fonts/THSarabunNew.ttf') }}") format('truetype');
        }
        @font-face {
            font-family: 'THSarabunNew';
            font-style: normal;
            font-weight: bold;
            src: url("{{ public_path('fonts/THSarabunNew-Bold.ttf') }}") format('truetype');
        }
        @page {
            margin: 15mm;
        }
        body {
            font-family: 'THSarabunNew', 'Garuda', sans-serif;
            font-size: 15px;
            line-height: 1.4;
            color: #000;
        }
        .header {
            text-align: center;
            margin-bottom: 15px;
        }
        .header h2 {
            margin: 0;
            font-size: 20px;
        }
        .header p {
            margin: 3px 0;
            color: #333;
        }
        .meta-table {
            width: 100%;
            margin-bottom: 10px;
            font-size: 13px;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }
        .data-table th, .data-table td {
            border: 1px solid #333;
            padding: 5px 6px;
            text-align: left;
        }
        .data-table th {
            background-color: #e5e7eb;
            font-weight: bold;
            text-align: center;
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            font-size: 11px;
            color: #777;
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="header">
        <h2>บริษัท เอ็นเตอร์ไพรส์ จำกัด</h2>
        <p><strong>{{ $reportTitle }}</strong></p>
    </div>

    <table class="meta-table">
        <tr>
            <td>ผู้ส่งออกรายงาน: {{ $exporter }}</td>
            <td style="text-align: right;">วันที่พิมพ์: {{ $exportDate }}</td>
        </tr>
    </table>

    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 12%;">เลขที่เอกสาร</th>
                <th style="width: 10%;">วันที่ทำ OT</th>
                <th style="width: 15%;">แผนก</th>
                <th style="width: 15%;">ประเภท OT</th>
                <th style="width: 12%;">เวลาปฏิบัติงาน</th>
                <th style="width: 8%;">จำนวนคน</th>
                <th style="width: 10%;">ชั่วโมงรวม</th>
                <th style="width: 18%;">สถานะ</th>
            </tr>
        </thead>
        <tbody>
            @foreach($reportData as $row)
                @if($row instanceof \App\Models\OvertimeRequest)
                    <tr>
                        <td class="text-center">{{ $row->document_no }}</td>
                        <td class="text-center">{{ $row->request_date->format('d/m/Y') }}</td>
                        <td>{{ $row->department?->name_th }}</td>
                        <td>{{ $row->overtimeType?->name_th }}</td>
                        <td class="text-center">{{ substr($row->start_time, 0, 5) }} - {{ substr($row->end_time, 0, 5) }}</td>
                        <td class="text-center">{{ $row->employees->count() }}</td>
                        <td class="text-right"><strong>{{ $row->total_hours }}</strong></td>
                        <td class="text-center">{{ $row->status->label() }}</td>
                    </tr>
                @endif
            @endforeach
        </tbody>
    </table>

    <div class="footer">
        Enterprise Overtime Management System - {{ $reportTitle }}
    </div>
</body>
</html>

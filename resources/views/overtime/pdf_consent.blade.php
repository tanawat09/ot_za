<!DOCTYPE html>
<html lang="th">
<head>
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
    <title>เอกสารยินยอมทำ OT - {{ $overtime->document_no }}</title>
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
            margin: 15mm 15mm 15mm 15mm;
        }
        body {
            font-family: 'THSarabunNew', 'Garuda', sans-serif;
            font-size: 16px;
            line-height: 1.4;
            color: #000;
        }
        .header-container {
            width: 100%;
            margin-bottom: 15px;
            text-align: center;
        }
        .logo-img {
            max-height: 55px;
            max-width: 180px;
            margin-bottom: 5px;
        }
        .header-title {
            font-size: 22px;
            font-weight: bold;
            text-align: center;
            margin-bottom: 3px;
        }
        .header-sub {
            font-size: 18px;
            text-align: center;
            margin-bottom: 15px;
            font-weight: bold;
            text-decoration: underline;
        }
        .info-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 15px;
        }
        .info-table td {
            padding: 4px 8px;
            vertical-align: top;
        }
        .info-label {
            font-weight: bold;
            width: 140px;
        }
        .data-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
            margin-bottom: 25px;
        }
        .data-table th, .data-table td {
            border: 1px solid #000;
            padding: 6px 8px;
            text-align: left;
        }
        .data-table th {
            background-color: #f2f2f2;
            font-weight: bold;
            text-align: center;
        }
        .text-center {
            text-align: center;
        }
        .signature-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 30px;
        }
        .signature-table td {
            width: 50%;
            text-align: center;
            vertical-align: bottom;
            padding-top: 50px;
        }
        .footer {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            font-size: 12px;
            color: #666;
            text-align: right;
        }
    </style>
</head>
<body>
    <div class="header-container">
        @if(!empty($logoBase64))
            <img src="{{ $logoBase64 }}" class="logo-img" alt="Logo"><br>
        @endif
        <div class="header-title">{{ $companyName }}</div>
        <div class="header-sub">หนังสือยินยอมปฏิบัติงานล่วงเวลา (Overtime Consent Form)</div>
    </div>

    <table class="info-table">
        <tr>
            <td class="info-label">เลขที่เอกสาร:</td>
            <td><strong>{{ $overtime->document_no }}</strong></td>
            <td class="info-label">วันที่พิมพ์:</td>
            <td>{{ $printDate }}</td>
        </tr>
        <tr>
            <td class="info-label">แผนก / ทีม:</td>
            <td>{{ $overtime->department?->name_th }} ({{ $overtime->team?->name_th ?? 'ทั้งแผนก' }})</td>
            <td class="info-label">วันที่ปฏิบัติงาน:</td>
            <td><strong>{{ $overtime->request_date->format('d/m/Y') }}</strong></td>
        </tr>
        <tr>
            <td class="info-label">ประเภท OT:</td>
            <td>{{ $overtime->overtimeType?->name_th }} ({{ $overtime->overtimeType?->multiplier }} เท่า)</td>
            <td class="info-label">เวลาปฏิบัติงาน:</td>
            <td>{{ substr($overtime->start_time, 0, 5) }} - {{ substr($overtime->end_time, 0, 5) }} น. ({{ $overtime->total_hours }} ชม.)</td>
        </tr>
        <tr>
            <td class="info-label">สถานที่ปฏิบัติงาน:</td>
            <td colspan="3">{{ $overtime->location ?? '-' }}</td>
        </tr>
        <tr>
            <td class="info-label">เหตุผลในการทำ OT:</td>
            <td colspan="3">{{ $overtime->reason }}</td>
        </tr>
    </table>

    <div style="font-weight: bold; margin-top: 10px;">รายชื่อพนักงานผู้ลงนามยินยอมปฏิบัติงานล่วงเวลา:</div>
    <table class="data-table">
        <thead>
            <tr>
                <th style="width: 5%;">ลำดับ</th>
                <th style="width: 15%;">รหัสพนักงาน</th>
                <th style="width: 35%;">ชื่อ-นามสกุล</th>
                <th style="width: 20%;">ตำแหน่ง</th>
                <th style="width: 25%;">ลายมือชื่อยินยอม</th>
            </tr>
        </thead>
        <tbody>
            @foreach($overtime->employees as $index => $empReq)
                <tr>
                    <td class="text-center">{{ $index + 1 }}</td>
                    <td class="text-center">{{ $empReq->employee?->emp_code }}</td>
                    <td>{{ $empReq->employee?->full_name }}</td>
                    <td>{{ $empReq->employee?->position?->title_th ?? '-' }}</td>
                    <td class="text-center">
                        @if($empReq->consent_status === 'CONSENTED')
                            ยินยอมแล้ว ({{ $empReq->consent_signed_at?->format('d/m/Y') }})
                        @else
                            ____________________
                        @endif
                    </td>
                </tr>
            @endforeach
        </tbody>
    </table>

    <table class="signature-table">
        <tr>
            <td>
                ลงชื่อ...................................................<br>
                ( {{ $overtime->creator?->name }} )<br>
                หัวหน้างานผู้ขออนุมัติ<br>
                วันที่ ......./......./.......
            </td>
            <td>
                ลงชื่อ...................................................<br>
                ( {{ $overtime->manager?->name ?? '...................................................' }} )<br>
                ผู้จัดการผู้อนุมัติ<br>
                วันที่ ......./......./.......
            </td>
        </tr>
    </table>

    <div class="footer">
        เอกสารสร้างโดยระบบ {{ $companyName }} | เลขที่: {{ $overtime->document_no }}
    </div>
</body>
</html>

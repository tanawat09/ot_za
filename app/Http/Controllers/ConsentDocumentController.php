<?php

namespace App\Http\Controllers;

use App\Models\OvertimeConsent;
use App\Models\OvertimeRequest;
use App\Models\OvertimeRequestEmployee;
use App\Models\SystemSetting;
use App\Services\AuditLogService;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class ConsentDocumentController extends Controller
{
    /**
     * Download or Print PDF Consent Document with Company Logo and Thai font.
     */
    public function downloadPdf(OvertimeRequest $overtime)
    {
        $overtime->load(['department', 'team', 'overtimeType', 'creator', 'manager', 'employees.employee.position']);

        $companyName = SystemSetting::get('company_name', 'บริษัท เอ็นเตอร์ไพรส์ จำกัด');
        $logoPath = SystemSetting::get('company_logo');
        $logoBase64 = null;

        if ($logoPath && file_exists(public_path($logoPath))) {
            $type = pathinfo(public_path($logoPath), PATHINFO_EXTENSION);
            $data = file_get_contents(public_path($logoPath));
            $logoBase64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        }

        $pdf = Pdf::loadView('overtime.pdf_consent', [
            'overtime' => $overtime,
            'companyName' => $companyName,
            'logoBase64' => $logoBase64,
            'printDate' => now()->format('d/m/Y H:i'),
        ])->setPaper('a4', 'portrait');

        AuditLogService::log(action: 'Download PDF Consent Form', module: 'Consent Document', recordId: (string)$overtime->id);

        return $pdf->download("Consent_{$overtime->document_no}.pdf");
    }

    /**
     * Update employee consent status.
     */
    public function updateConsentStatus(Request $request, OvertimeRequest $overtime)
    {
        $validated = $request->validate([
            'employee_id' => ['required', 'exists:employees,id'],
            'consent_status' => ['required', 'in:CONSENTED,REFUSED,PENDING'],
            'remarks' => ['nullable', 'string'],
        ]);

        $reqEmp = OvertimeRequestEmployee::where('overtime_request_id', $overtime->id)
            ->where('employee_id', $validated['employee_id'])
            ->firstOrFail();

        $reqEmp->update([
            'consent_status' => $validated['consent_status'],
            'consent_signed_at' => $validated['consent_status'] === 'CONSENTED' ? now() : null,
            'remarks' => $validated['remarks'] ?? null,
        ]);

        AuditLogService::log(action: 'Update Consent Status', module: 'Consent Document', recordId: (string)$overtime->id);

        return redirect()->back()->with('success', 'อัปเดตสถานะการยินยอมเรียบร้อยแล้ว');
    }

    /**
     * Upload signed consent document file.
     */
    public function uploadSignedDocument(Request $request, OvertimeRequest $overtime)
    {
        $request->validate([
            'file' => ['required', 'file', 'mimes:pdf,jpg,png', 'max:10240'], // Max 10MB
        ], [
            'file.required' => 'กรุณาเลือกไฟล์เอกสารที่เซ็นแล้ว',
            'file.mimes' => 'อนุญาตเฉพาะไฟล์ PDF, JPG, PNG เท่านั้น',
            'file.max' => 'ขนาดไฟล์ต้องไม่เกิน 10MB',
        ]);

        $file = $request->file('file');
        $filename = "signed_{$overtime->document_no}_" . time() . '.' . $file->getClientOriginalExtension();
        $path = $file->storeAs('consents', $filename, 'local');

        $consent = OvertimeConsent::create([
            'overtime_request_id' => $overtime->id,
            'file_path' => $path,
            'file_name' => $file->getClientOriginalName(),
            'file_size' => $file->getSize(),
            'uploaded_by_user_id' => Auth::id(),
        ]);

        AuditLogService::log(action: 'Upload Signed Consent Document', module: 'Consent Document', recordId: (string)$overtime->id);

        return redirect()->back()->with('success', 'อัปเดตไฟล์เอกสารยินยอมเรียบร้อยแล้ว');
    }

    /**
     * Secure file download for uploaded consent documents.
     */
    public function downloadFile(OvertimeConsent $consent)
    {
        if (!Storage::disk('local')->exists($consent->file_path)) {
            abort(404, 'ไม่พบไฟล์ในระบบ');
        }

        AuditLogService::log(action: 'Download Signed Consent File', module: 'Consent Document', recordId: (string)$consent->id);

        return Storage::disk('local')->download($consent->file_path, $consent->file_name);
    }
}

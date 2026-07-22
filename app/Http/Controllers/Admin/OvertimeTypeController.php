<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\OvertimeType;
use App\Services\AuditLogService;
use Illuminate\Http\Request;

class OvertimeTypeController extends Controller
{
    public function index(Request $request)
    {
        $query = OvertimeType::query();

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name_th', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $overtimeTypes = $query->orderBy('code')->paginate(15);
        return view('admin.overtime_types.index', compact('overtimeTypes'));
    }

    public function create()
    {
        return view('admin.overtime_types.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:overtime_types,code'],
            'name_th' => ['required', 'string', 'max:255'],
            'multiplier' => ['required', 'numeric', 'min:0.5', 'max:10.0'],
            'start_time_limit' => ['nullable'],
            'end_time_limit' => ['nullable'],
            'max_hours_per_day' => ['required', 'numeric', 'min:0.5', 'max:24.0'],
            'requires_document' => ['boolean'],
            'is_active' => ['boolean'],
        ]);

        $otType = OvertimeType::create([
            'code' => strtoupper($validated['code']),
            'name_th' => $validated['name_th'],
            'multiplier' => $validated['multiplier'],
            'start_time_limit' => $validated['start_time_limit'] ?? null,
            'end_time_limit' => $validated['end_time_limit'] ?? null,
            'max_hours_per_day' => $validated['max_hours_per_day'],
            'requires_document' => $request->boolean('requires_document', true),
            'is_active' => $request->boolean('is_active', true),
        ]);

        AuditLogService::log(action: 'Create Overtime Type', module: 'Master Data', recordId: (string)$otType->id, newValues: $validated);

        return redirect()->route('admin.overtime-types.index')->with('success', 'เพิ่มประเภท OT สำเร็จ');
    }

    public function edit(OvertimeType $overtimeType)
    {
        return view('admin.overtime_types.edit', compact('overtimeType'));
    }

    public function update(Request $request, OvertimeType $overtimeType)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:overtime_types,code,' . $overtimeType->id],
            'name_th' => ['required', 'string', 'max:255'],
            'multiplier' => ['required', 'numeric', 'min:0.5', 'max:10.0'],
            'start_time_limit' => ['nullable'],
            'end_time_limit' => ['nullable'],
            'max_hours_per_day' => ['required', 'numeric', 'min:0.5', 'max:24.0'],
            'requires_document' => ['boolean'],
            'is_active' => ['boolean'],
        ]);

        $oldValues = $overtimeType->toArray();

        $overtimeType->update([
            'code' => strtoupper($validated['code']),
            'name_th' => $validated['name_th'],
            'multiplier' => $validated['multiplier'],
            'start_time_limit' => $validated['start_time_limit'] ?? null,
            'end_time_limit' => $validated['end_time_limit'] ?? null,
            'max_hours_per_day' => $validated['max_hours_per_day'],
            'requires_document' => $request->boolean('requires_document'),
            'is_active' => $request->boolean('is_active'),
        ]);

        AuditLogService::log(action: 'Update Overtime Type', module: 'Master Data', recordId: (string)$overtimeType->id, oldValues: $oldValues, newValues: $validated);

        return redirect()->route('admin.overtime-types.index')->with('success', 'อัปเดตประเภท OT สำเร็จ');
    }

    public function destroy(OvertimeType $overtimeType)
    {
        $overtimeType->delete();
        AuditLogService::log(action: 'Delete Overtime Type', module: 'Master Data', recordId: (string)$overtimeType->id);

        return redirect()->route('admin.overtime-types.index')->with('success', 'ลบประเภท OT สำเร็จ');
    }
}

<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Holiday;
use App\Services\AuditLogService;
use Illuminate\Http\Request;

class HolidayController extends Controller
{
    public function index(Request $request)
    {
        $query = Holiday::query();

        if ($request->filled('year')) {
            $query->whereYear('holiday_date', $request->input('year'));
        }

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where('name_th', 'like', "%{$search}%");
        }

        $holidays = $query->orderBy('holiday_date', 'asc')->paginate(15);
        return view('admin.holidays.index', compact('holidays'));
    }

    public function create()
    {
        return view('admin.holidays.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'holiday_date' => ['required', 'date'],
            'name_th' => ['required', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'is_recurring' => ['boolean'],
        ]);

        $holiday = Holiday::create([
            'holiday_date' => $validated['holiday_date'],
            'name_th' => $validated['name_th'],
            'name_en' => $validated['name_en'] ?? null,
            'is_recurring' => $request->boolean('is_recurring'),
        ]);

        AuditLogService::log(action: 'Create Holiday', module: 'Master Data', recordId: (string)$holiday->id, newValues: $validated);

        return redirect()->route('admin.holidays.index')->with('success', 'เพิ่มวันหยุดสำเร็จ');
    }

    public function edit(Holiday $holiday)
    {
        return view('admin.holidays.edit', compact('holiday'));
    }

    public function update(Request $request, Holiday $holiday)
    {
        $validated = $request->validate([
            'holiday_date' => ['required', 'date'],
            'name_th' => ['required', 'string', 'max:255'],
            'name_en' => ['nullable', 'string', 'max:255'],
            'is_recurring' => ['boolean'],
        ]);

        $oldValues = $holiday->toArray();

        $holiday->update([
            'holiday_date' => $validated['holiday_date'],
            'name_th' => $validated['name_th'],
            'name_en' => $validated['name_en'] ?? null,
            'is_recurring' => $request->boolean('is_recurring'),
        ]);

        AuditLogService::log(action: 'Update Holiday', module: 'Master Data', recordId: (string)$holiday->id, oldValues: $oldValues, newValues: $validated);

        return redirect()->route('admin.holidays.index')->with('success', 'อัปเดตวันหยุดสำเร็จ');
    }

    public function destroy(Holiday $holiday)
    {
        $holiday->delete();
        AuditLogService::log(action: 'Delete Holiday', module: 'Master Data', recordId: (string)$holiday->id);

        return redirect()->route('admin.holidays.index')->with('success', 'ลบวันหยุดสำเร็จ');
    }
}

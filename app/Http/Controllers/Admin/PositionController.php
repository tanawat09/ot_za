<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Position;
use App\Services\AuditLogService;
use Illuminate\Http\Request;

class PositionController extends Controller
{
    public function index(Request $request)
    {
        $query = Position::withCount('employees');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('title_th', 'like', "%{$search}%")
                  ->orWhere('code', 'like', "%{$search}%");
            });
        }

        $positions = $query->orderBy('code')->paginate(15);
        return view('admin.positions.index', compact('positions'));
    }

    public function create()
    {
        return view('admin.positions.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:positions,code'],
            'title_th' => ['required', 'string', 'max:255'],
            'title_en' => ['nullable', 'string', 'max:255'],
            'is_active' => ['boolean'],
        ]);

        $position = Position::create([
            'code' => strtoupper($validated['code']),
            'title_th' => $validated['title_th'],
            'title_en' => $validated['title_en'] ?? null,
            'is_active' => $request->boolean('is_active', true),
        ]);

        AuditLogService::log(action: 'Create Position', module: 'Master Data', recordId: (string)$position->id, newValues: $validated);

        return redirect()->route('admin.positions.index')->with('success', 'เพิ่มตำแหน่งสำเร็จ');
    }

    public function edit(Position $position)
    {
        return view('admin.positions.edit', compact('position'));
    }

    public function update(Request $request, Position $position)
    {
        $validated = $request->validate([
            'code' => ['required', 'string', 'max:50', 'unique:positions,code,' . $position->id],
            'title_th' => ['required', 'string', 'max:255'],
            'title_en' => ['nullable', 'string', 'max:255'],
            'is_active' => ['boolean'],
        ]);

        $oldValues = $position->toArray();

        $position->update([
            'code' => strtoupper($validated['code']),
            'title_th' => $validated['title_th'],
            'title_en' => $validated['title_en'] ?? null,
            'is_active' => $request->boolean('is_active'),
        ]);

        AuditLogService::log(action: 'Update Position', module: 'Master Data', recordId: (string)$position->id, oldValues: $oldValues, newValues: $validated);

        return redirect()->route('admin.positions.index')->with('success', 'อัปเดตตำแหน่งสำเร็จ');
    }

    public function destroy(Position $position)
    {
        if ($position->employees()->count() > 0) {
            return redirect()->back()->with('error', 'ไม่สามารถลบตำแหน่งที่มีพนักงานสังกัดอยู่ได้');
        }

        $position->delete();
        AuditLogService::log(action: 'Delete Position', module: 'Master Data', recordId: (string)$position->id);

        return redirect()->route('admin.positions.index')->with('success', 'ลบตำแหน่งสำเร็จ');
    }
}

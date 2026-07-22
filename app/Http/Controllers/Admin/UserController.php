<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Spatie\Permission\Models\Role;

class UserController extends Controller
{
    /**
     * Display a listing of users.
     */
    public function index(Request $request)
    {
        $query = User::with('roles');

        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('email', 'like', "%{$search}%")
                  ->orWhere('emp_code', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->role($request->input('role'));
        }

        if ($request->filled('status')) {
            $query->where('is_active', $request->input('status') === '1');
        }

        $users = $query->orderBy('created_at', 'desc')->paginate(15);
        $roles = Role::all();

        return view('admin.users.index', compact('users', 'roles'));
    }

    /**
     * Show the form for creating a new user.
     */
    public function create()
    {
        $roles = Role::all();
        return view('admin.users.create', compact('roles'));
    }

    /**
     * Store a newly created user in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'emp_code' => ['nullable', 'string', 'max:50', 'unique:users,emp_code'],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8'],
            'role' => ['required', 'string', 'exists:roles,name'],
            'is_active' => ['boolean'],
            'must_change_password' => ['boolean'],
        ], [
            'name.required' => 'กรุณากรอกชื่อ-นามสกุล',
            'email.required' => 'กรุณากรอกอีเมล',
            'email.unique' => 'อีเมลนี้ถูกใช้งานแล้วในระบบ',
            'password.required' => 'กรุณากรอกรหัสผ่าน',
            'password.min' => 'รหัสผ่านต้องมีความยาวอย่างน้อย 8 ตัวอักษร',
            'role.required' => 'กรุณาเลือกรหัสสิทธิ์ (Role)',
        ]);

        $user = User::create([
            'emp_code' => $validated['emp_code'] ?? null,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => Hash::make($validated['password']),
            'is_active' => $request->boolean('is_active', true),
            'must_change_password' => $request->boolean('must_change_password', true),
        ]);

        $user->assignRole($validated['role']);

        AuditLogService::log(
            action: 'Create User',
            module: 'User Management',
            recordId: (string) $user->id,
            newValues: [
                'emp_code' => $user->emp_code,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $validated['role'],
                'is_active' => $user->is_active,
            ]
        );

        return redirect()->route('admin.users.index')->with('success', 'เพิ่มผู้ใช้งานสำเร็จ');
    }

    /**
     * Show the form for editing the specified user.
     */
    public function edit(User $user)
    {
        $roles = Role::all();
        $userRole = $user->roles->first()?->name;

        return view('admin.users.edit', compact('user', 'roles', 'userRole'));
    }

    /**
     * Update the specified user in storage.
     */
    public function update(Request $request, User $user)
    {
        $validated = $request->validate([
            'emp_code' => ['nullable', 'string', 'max:50', Rule::unique('users', 'emp_code')->ignore($user->id)],
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'role' => ['required', 'string', 'exists:roles,name'],
            'is_active' => ['boolean'],
            'must_change_password' => ['boolean'],
        ], [
            'name.required' => 'กรุณากรอกชื่อ-นามสกุล',
            'email.required' => 'กรุณากรอกอีเมล',
            'email.unique' => 'อีเมลนี้ถูกใช้งานแล้วในระบบ',
            'role.required' => 'กรุณาเลือกบทบาทผู้ใช้',
        ]);

        $oldValues = [
            'name' => $user->name,
            'email' => $user->email,
            'role' => $user->roles->first()?->name,
            'is_active' => $user->is_active,
        ];

        $user->update([
            'emp_code' => $validated['emp_code'] ?? null,
            'name' => $validated['name'],
            'email' => $validated['email'],
            'is_active' => $request->boolean('is_active'),
            'must_change_password' => $request->boolean('must_change_password'),
        ]);

        $user->syncRoles([$validated['role']]);

        AuditLogService::log(
            action: 'Update User',
            module: 'User Management',
            recordId: (string) $user->id,
            oldValues: $oldValues,
            newValues: $validated
        );

        return redirect()->route('admin.users.index')->with('success', 'แก้ไขข้อมูลผู้ใช้งานสำเร็จ');
    }

    /**
     * Toggle active status.
     */
    public function toggleStatus(User $user)
    {
        $oldStatus = $user->is_active;
        $user->is_active = !$user->is_active;
        $user->save();

        AuditLogService::log(
            action: $user->is_active ? 'Enable User Account' : 'Disable User Account',
            module: 'User Management',
            recordId: (string) $user->id,
            oldValues: ['is_active' => $oldStatus],
            newValues: ['is_active' => $user->is_active]
        );

        return redirect()->back()->with('success', 'เปลี่ยนสถานะใช้งานเรียบร้อยแล้ว');
    }

    /**
     * Reset user password to default.
     */
    public function resetPassword(User $user)
    {
        $defaultPassword = 'Password123!';
        $user->update([
            'password' => Hash::make($defaultPassword),
            'must_change_password' => true,
        ]);

        AuditLogService::log(
            action: 'Reset User Password',
            module: 'User Management',
            recordId: (string) $user->id
        );

        return redirect()->back()->with('success', "รีเซ็ตรหัสผ่านของผู้ใช้ {$user->name} เป็น {$defaultPassword} เรียบร้อยแล้ว");
    }
}

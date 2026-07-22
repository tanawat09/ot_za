<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\SystemSetting;
use App\Services\AuditLogService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;

class SystemSettingController extends Controller
{
    public function index()
    {
        $settings = SystemSetting::all()->groupBy('group');
        $companyLogo = SystemSetting::get('company_logo');
        $companyName = SystemSetting::get('company_name', 'บริษัท เอ็นเตอร์ไพรส์ จำกัด');

        return view('admin.settings.index', compact('settings', 'companyLogo', 'companyName'));
    }

    public function update(Request $request)
    {
        $settings = $request->input('settings', []);

        // Handle Text Settings
        foreach ($settings as $key => $value) {
            SystemSetting::set($key, $value, 'general');
        }

        // Handle Logo Upload
        if ($request->hasFile('company_logo')) {
            $request->validate([
                'company_logo' => ['image', 'mimes:png,jpg,jpeg,svg', 'max:2048'],
            ], [
                'company_logo.image' => 'ไฟล์โลโก้ต้องเป็นรูปภาพเท่านั้น',
                'company_logo.mimes' => 'อนุญาตเฉพาะไฟล์ PNG, JPG, JPEG, SVG เท่านั้น',
                'company_logo.max' => 'ขนาดไฟล์โลโก้ต้องไม่เกิน 2MB',
            ]);

            $file = $request->file('company_logo');
            $filename = 'logo_' . time() . '.' . $file->getClientOriginalExtension();
            $destinationPath = public_path('uploads/settings');

            if (!File::exists($destinationPath)) {
                File::makeDirectory($destinationPath, 0755, true);
            }

            // Remove old logo if exists
            $oldLogo = SystemSetting::get('company_logo');
            if ($oldLogo && File::exists(public_path($oldLogo))) {
                File::delete(public_path($oldLogo));
            }

            $file->move($destinationPath, $filename);
            $logoUrl = 'uploads/settings/' . $filename;

            SystemSetting::set('company_logo', $logoUrl, 'general', 'โลโก้องค์กร / บริษัท');
        }

        AuditLogService::log(action: 'Update System Settings & Logo', module: 'System Configuration');

        return redirect()->back()->with('success', 'บันทึกการตั้งค่าระบบและโลโก้องค์กรเรียบร้อยแล้ว');
    }

    public function removeLogo()
    {
        $oldLogo = SystemSetting::get('company_logo');
        if ($oldLogo && File::exists(public_path($oldLogo))) {
            File::delete(public_path($oldLogo));
        }

        SystemSetting::where('key', 'company_logo')->delete();
        AuditLogService::log(action: 'Remove Company Logo', module: 'System Configuration');

        return redirect()->back()->with('success', 'ลบโลโก้องค์กรเรียบร้อยแล้ว');
    }
}

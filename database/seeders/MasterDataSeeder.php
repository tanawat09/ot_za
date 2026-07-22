<?php

namespace Database\Seeders;

use App\Models\Department;
use App\Models\Employee;
use App\Models\Holiday;
use App\Models\OvertimeType;
use App\Models\Position;
use App\Models\SystemSetting;
use App\Models\Team;
use App\Models\User;
use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Departments
        $it = Department::firstOrCreate(['code' => 'IT'], ['name_th' => 'เทคโนโลยีสารสนเทศ (IT)', 'name_en' => 'Information Technology', 'is_active' => true]);
        $hr = Department::firstOrCreate(['code' => 'HR'], ['name_th' => 'ทรัพยากรบุคคล (HR)', 'name_en' => 'Human Resources', 'is_active' => true]);
        $prod = Department::firstOrCreate(['code' => 'PROD'], ['name_th' => 'ฝ่ายผลิต (Production)', 'name_en' => 'Production Department', 'is_active' => true]);

        // 2. Teams
        $devTeam = Team::firstOrCreate(['code' => 'IT-DEV', 'department_id' => $it->id], ['name_th' => 'ทีมพัฒนาซอฟต์แวร์', 'name_en' => 'Software Dev Team', 'is_active' => true]);
        $infraTeam = Team::firstOrCreate(['code' => 'IT-INFRA', 'department_id' => $it->id], ['name_th' => 'ทีมโครงสร้างพื้นฐาน', 'name_en' => 'Infrastructure Team', 'is_active' => true]);

        // 3. Positions
        $devPos = Position::firstOrCreate(['code' => 'DEV'], ['title_th' => 'นักพัฒนาซอฟต์แวร์ (Developer)', 'title_en' => 'Software Developer', 'is_active' => true]);
        $leadPos = Position::firstOrCreate(['code' => 'LEAD'], ['title_th' => 'หัวหน้าทีม (Team Lead)', 'title_en' => 'Team Lead', 'is_active' => true]);
        $mgrPos = Position::firstOrCreate(['code' => 'MGR'], ['title_th' => 'ผู้จัดการแผนก (Manager)', 'title_en' => 'Department Manager', 'is_active' => true]);

        // 4. Overtime Types
        OvertimeType::firstOrCreate(['code' => 'OT-NORMAL'], [
            'name_th' => 'OT วันทำงานปกติ (1.5 เท่า)',
            'multiplier' => 1.50,
            'max_hours_per_day' => 4.00,
            'requires_document' => true,
            'is_active' => true,
        ]);

        OvertimeType::firstOrCreate(['code' => 'OT-HOLIDAY-NORM'], [
            'name_th' => 'OT วันหยุดประจำสัปดาห์ (1.0 - 2.0 เท่า)',
            'multiplier' => 2.00,
            'max_hours_per_day' => 8.00,
            'requires_document' => true,
            'is_active' => true,
        ]);

        OvertimeType::firstOrCreate(['code' => 'OT-HOLIDAY-FEST'], [
            'name_th' => 'OT วันหยุดนักขัตฤกษ์ (3.0 เท่า)',
            'multiplier' => 3.00,
            'max_hours_per_day' => 8.00,
            'requires_document' => true,
            'is_active' => true,
        ]);

        OvertimeType::firstOrCreate(['code' => 'OT-NIGHT'], [
            'name_th' => 'OT ช่วงกลางคืน (1.5 - 2.0 เท่า)',
            'multiplier' => 1.50,
            'max_hours_per_day' => 6.00,
            'requires_document' => true,
            'is_active' => true,
        ]);

        // 5. Holidays
        Holiday::firstOrCreate(['holiday_date' => '2026-01-01'], ['name_th' => 'วันขึ้นปีใหม่', 'name_en' => 'New Year Day', 'is_recurring' => true]);
        Holiday::firstOrCreate(['holiday_date' => '2026-04-13'], ['name_th' => 'วันสงกรานต์', 'name_en' => 'Songkran Festival', 'is_recurring' => true]);
        Holiday::firstOrCreate(['holiday_date' => '2026-12-05'], ['name_th' => 'วันคล้ายวันพระบรมราชสมภพ ร.9', 'name_en' => 'King Bhumibol Memorial Day', 'is_recurring' => true]);

        // 6. System Settings
        SystemSetting::set('document_number_format', 'OT-{YYYY}{MM}-{DEPT}-{NUMBER}', 'document', 'รูปแบบเลขที่เอกสารคำขอ OT');
        SystemSetting::set('max_ot_hours_per_week', '36', 'business_rules', 'จำนวนชั่วโมง OT สูงสุดต่อสัปดาห์ตามกฎหมายแรงงาน');
        SystemSetting::set('company_name_th', 'บริษัท เอ็นเตอร์ไพรส์ จำกัด', 'company', 'ชื่อบริษัทภาษาไทย');

        // 7. Assign Manager & Supervisor relationships
        $managerUser = User::where('email', 'manager@company.com')->first();
        if ($managerUser) {
            $it->managers()->syncWithoutDetaching([$managerUser->id]);
        }

        $supervisorUser = User::where('email', 'supervisor@company.com')->first();
        if ($supervisorUser) {
            $emp = Employee::firstOrCreate(['emp_code' => 'EMP-0004'], [
                'user_id' => $supervisorUser->id,
                'prefix' => 'นาย',
                'first_name' => 'หัวหน้างาน',
                'last_name' => 'ไอที',
                'position_id' => $leadPos->id,
                'department_id' => $it->id,
                'team_id' => $devTeam->id,
                'email' => 'supervisor@company.com',
                'status' => 'Active',
            ]);
            $emp->supervisors()->syncWithoutDetaching([$supervisorUser->id]);
        }
    }
}

<?php

namespace App\Services;

use App\Models\Department;
use App\Models\OvertimeRequest;
use Illuminate\Support\Facades\DB;

class DocumentNumberService
{
    /**
     * Generate auto-running document number: OT-YYYYMM-DEPT-XXXXX with DB Transaction lock.
     */
    public static function generate(int $departmentId, ?string $requestDate = null): string
    {
        return DB::transaction(function () use ($departmentId, $requestDate) {
            $date = $requestDate ? \Carbon\Carbon::parse($requestDate) : now();
            $yearMonth = $date->format('Ym');

            $dept = Department::find($departmentId);
            $deptCode = $dept ? strtoupper($dept->code) : 'GEN';

            $prefix = "OT-{$yearMonth}-{$deptCode}";

            // Select max existing document number with shared lock
            $latestDoc = OvertimeRequest::where('document_no', 'like', "{$prefix}-%")
                ->lockForUpdate()
                ->orderBy('document_no', 'desc')
                ->first();

            if ($latestDoc) {
                $lastNumber = (int) substr($latestDoc->document_no, -5);
                $nextNumber = $lastNumber + 1;
            } else {
                $nextNumber = 1;
            }

            $runningNo = str_pad((string)$nextNumber, 5, '0', STR_PAD_LEFT);

            return "{$prefix}-{$runningNo}";
        });
    }
}

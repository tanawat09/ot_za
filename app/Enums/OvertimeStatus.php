<?php

namespace App\Enums;

enum OvertimeStatus: string
{
    case DRAFT = 'DRAFT';
    case WAITING_CONSENT = 'WAITING_CONSENT';
    case READY_TO_SUBMIT = 'READY_TO_SUBMIT';
    case PENDING_APPROVAL = 'PENDING_APPROVAL';
    case RETURNED = 'RETURNED';
    case APPROVED = 'APPROVED';
    case REJECTED = 'REJECTED';
    case CANCELLED = 'CANCELLED';
    case COMPLETED = 'COMPLETED';
    case LOCKED = 'LOCKED';

    public function label(): string
    {
        return match($this) {
            self::DRAFT => 'ร่างคำขอ (Draft)',
            self::WAITING_CONSENT => 'รอพนักงานเซ็นยินยอม',
            self::READY_TO_SUBMIT => 'พร้อมส่งอนุมัติ',
            self::PENDING_APPROVAL => 'รอผู้จัดการอนุมัติ',
            self::RETURNED => 'ถูกส่งกลับแก้ไข',
            self::APPROVED => 'อนุมัติแล้ว',
            self::REJECTED => 'ไม่อนุมัติ (Rejected)',
            self::CANCELLED => 'ยกเลิกรายการ',
            self::COMPLETED => 'บันทึกเวลาจริงเรียบร้อย',
            self::LOCKED => 'ปิดรอบแล้ว (Locked)',
        };
    }

    public function badgeClass(): string
    {
        return match($this) {
            self::DRAFT => 'bg-secondary',
            self::WAITING_CONSENT => 'bg-warning text-dark',
            self::READY_TO_SUBMIT => 'bg-info text-dark',
            self::PENDING_APPROVAL => 'bg-primary',
            self::RETURNED => 'bg-warning text-dark',
            self::APPROVED => 'bg-success',
            self::REJECTED => 'bg-danger',
            self::CANCELLED => 'bg-dark',
            self::COMPLETED => 'bg-success-subtle text-success border border-success',
            self::LOCKED => 'bg-secondary',
        };
    }
}

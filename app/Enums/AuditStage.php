<?php

namespace App\Enums;

enum AuditStage: string
{
    case DOC_AUDIT = 'doc_audit';
    case FIELD_AUDIT = 'field_audit';
    case FINDING = 'finding';
    case REPORTING = 'reporting';
    case RTM_RTL = 'rtm_rtl';
    case FINISHED = 'finished';

    public function label(): string
    {
        return match ($this) {
            self::DOC_AUDIT => 'Audit Dokumen',
            self::FIELD_AUDIT => 'Audit Lapangan',
            self::FINDING => 'Temuan',
            self::REPORTING => 'Pelaporan',
            self::RTM_RTL => 'RTM / RTL',
            self::FINISHED => 'Selesai',
        };
    }

    public function next(): ?AuditStage
    {
        $cases = array_values(AuditStage::cases());
        $current = array_search($this, $cases, true);

        return $current < count($cases) - 1 ? $cases[$current + 1] : null;
    }

    public function previous(): ?AuditStage
    {
        $cases = array_values(AuditStage::cases());
        $current = array_search($this, $cases, true);

        return $current > 0 ? $cases[$current - 1] : null;
    }

    public function fieldReport(): bool
    {
        return $this === self::FIELD_AUDIT;
    }

    public function finalReport(): bool
    {
        return $this === self::REPORTING;
    }

    public function endReport(): bool
    {
        return $this === self::FINISHED;
    }

    public function isEnd(): bool
    {
        return $this === self::FINISHED;
    }
}

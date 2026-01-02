<?php

namespace App\Enums;

enum AssignmentDocType: string
{
    case FIELD_REPORT = 'field_report';
    case FINAL_REPORT = 'final_report';
    case END_REPORT = 'end_report';

    public function label(): string
    {
        return match ($this) {
            self::FIELD_REPORT => 'Berita Acara Lapangan',
            self::FINAL_REPORT => 'Berita Acara Final',
            self::END_REPORT => 'Laporan Akhir',
        };
    }
}

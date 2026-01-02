<?php

namespace App\Enums;

enum UserRole: string
{
    case ADMIN = 'admin';
    case AUDITOR = 'auditor';
    case AUDITEE = 'auditee';

    public function label(): string
    {
        return match ($this) {
            self::ADMIN => 'Administrator',
            self::AUDITOR => 'Auditor Internal',
            self::AUDITEE => 'Program Studi',
        };
    }
}

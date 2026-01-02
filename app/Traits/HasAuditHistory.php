<?php

namespace App\Traits;

use App\Models\AuditHistory;

trait HasAuditHistory
{
    /**
     * Relasi Polimorfik ke tabel audit_histories
     */
    public function histories()
    {
        return $this->morphMany(AuditHistory::class, 'historable');
    }
}

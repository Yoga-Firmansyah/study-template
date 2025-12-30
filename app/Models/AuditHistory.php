<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AuditHistory extends Model
{
    protected $fillable = [
        'user_id',
        'historable_type',
        'historable_id',
        'stage',
        'old_values',
        'new_values',
        'action',
        'reason'
    ];

    protected $casts = [
        'old_values' => 'json',
        'new_values' => 'json',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    // Mengambil parent (Bisa MasterIndicator atau AssignmentIndicator)
    public function historable()
    {
        return $this->morphTo();
    }
}

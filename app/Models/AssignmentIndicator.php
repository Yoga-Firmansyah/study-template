<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssignmentIndicator extends Model
{
    protected $fillable = [
        'assignment_id',

        'snapshot_code',
        'snapshot_requirement',
        'snapshot_template_path',
        'score',
        'auditor_note',
        'evidence_path',
        'evidence_url',
        'recommendation'
    ];

    public function assignment()
    {
        return $this->belongsTo(Assignment::class);
    }

    // Relasi Polimorfik ke History
    public function histories()
    {
        return $this->morphMany(IndicatorHistory::class, 'historable');
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssignmentIndicator extends Model
{
    protected $fillable = [
        'assignment_id',
        'snapshot_code',
        'snapshot_requirement',
        'is_evidence_required',
        'score',
        'finding',
        'recommendation'
    ];

    public function assignment()
    {
        return $this->belongsTo(Assignment::class);
    }

    public function evidences()
    {
        return $this->hasMany(AssessmentEvidence::class);
    }

    // Relasi Polimorfik ke History
    public function histories()
    {
        return $this->morphMany(IndicatorHistory::class, 'historable');
    }
}

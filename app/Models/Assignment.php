<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Assignment extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'period_id',
        'master_standard_id',
        'prodi_id',
        'auditor_id',
        'current_stage',
        'summary_note',
        'overall_rating',
        'completed_at'
    ];

    protected $casts = [
        'completed_at' => 'datetime',
    ];

    public function period()
    {
        return $this->belongsTo(Period::class);
    }

    public function standard()
    {
        return $this->belongsTo(MasterStandard::class, 'master_standard_id')->withTrashed();
    }

    public function prodi()
    {
        return $this->belongsTo(Prodi::class);
    }

    public function auditor()
    {
        return $this->belongsTo(User::class, 'auditor_id');
    }

    public function indicators()
    {
        return $this->hasMany(AssignmentIndicator::class);
    }

    public function documents()
    {
        return $this->hasMany(AssignmentDocument::class);
    }

    public function histories()
    {
        return $this->morphMany(AuditHistory::class, 'historable');
    }
}

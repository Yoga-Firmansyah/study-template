<?php

namespace App\Models;

use App\Traits\Filterable;
use App\Traits\HasAuditHistory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Assignment extends Model
{
    use SoftDeletes, Filterable, HasAuditHistory;
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

    /**
     * Override scopeSearch dari Filterable Trait khusus untuk relasi
     */
    public function scopeSearch($query, $search, $columns = [])
    {
        if (!$search)
            return $query;

        return $query->where(function ($q) use ($search) {
            $q->whereHas('prodi', fn($p) => $p->where('name', 'like', "%{$search}%"))
                ->orWhereHas('auditor', fn($a) => $a->where('name', 'like', "%{$search}%"))
                ->orWhereHas('period', fn($per) => $per->where('name', 'like', "%{$search}%"))
                ->orWhere('current_stage', 'like', "%{$search}%");
        });
    }

}

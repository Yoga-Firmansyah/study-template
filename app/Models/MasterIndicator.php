<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MasterIndicator extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'master_standard_id',
        'code',
        'requirement',
        'template_path',
        'is_evidence_required'
    ];

    public function standard()
    {
        return $this->belongsTo(MasterStandard::class, 'master_standard_id');
    }

    // Relasi Polimorfik ke History
    public function histories()
    {
        return $this->morphMany(AuditHistory::class, 'historable');
    }
}

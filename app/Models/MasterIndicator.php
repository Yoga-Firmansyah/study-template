<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Filterable;
use App\Traits\HasAuditHistory;

class MasterIndicator extends Model
{
    use SoftDeletes, Filterable, HasAuditHistory;
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

}

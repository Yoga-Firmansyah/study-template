<?php

namespace App\Models;

use App\Traits\Filterable;
use App\Traits\HasAuditHistory;
use Illuminate\Database\Eloquent\Model;

class AssignmentIndicator extends Model
{
    use Filterable, HasAuditHistory;
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

}

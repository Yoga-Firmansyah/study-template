<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Period extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'name',
        'doc_audit_start',
        'doc_audit_end',
        'field_audit_start',
        'field_audit_end',
        'finding_start',
        'finding_end',
        'reporting_start',
        'reporting_end',
        'rtm_rtl_start',
        'rtm_rtl_end',
        'is_active'
    ];

    protected $casts = [
        'doc_audit_start' => 'datetime',
        'doc_audit_end' => 'date',
        'field_audit_start' => 'date',
        'field_audit_end' => 'date',
        'finding_start' => 'date',
        'finding_end' => 'date',
        'reporting_start' => 'date',
        'reporting_end' => 'date',
        'rtm_rtl_start' => 'date',
        'rtm_rtl_end' => 'date',
    ];

    public function assignments()
    {
        return $this->hasMany(Assignment::class);
    }
}

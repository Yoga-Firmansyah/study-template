<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use Carbon\Carbon;
use App\Traits\Filterable;
use App\Traits\HasAuditHistory;

class Period extends Model
{
    use SoftDeletes, Filterable, HasAuditHistory;

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
        'doc_audit_end' => 'datetime',
        'field_audit_start' => 'datetime',
        'field_audit_end' => 'datetime',
        'finding_start' => 'datetime',
        'finding_end' => 'datetime',
        'reporting_start' => 'datetime',
        'reporting_end' => 'datetime',
        'rtm_rtl_start' => 'datetime',
        'rtm_rtl_end' => 'datetime',
        'is_active' => 'boolean',
    ];

    /**
     * Logic Otomatis: Start (00:00:00) & End (23:59:59)
     * Menggunakan Mutator agar Controller tetap bersih
     */

    // Doc Audit
    protected function setDocAuditStartAttribute($value)
    {
        $this->attributes['doc_audit_start'] = Carbon::parse($value)->startOfDay();
    }
    protected function setDocAuditEndAttribute($value)
    {
        $this->attributes['doc_audit_end'] = Carbon::parse($value)->endOfDay();
    }

    // Field Audit
    protected function setFieldAuditStartAttribute($value)
    {
        $this->attributes['field_audit_start'] = Carbon::parse($value)->startOfDay();
    }
    protected function setFieldAuditEndAttribute($value)
    {
        $this->attributes['field_audit_end'] = Carbon::parse($value)->endOfDay();
    }

    // Finding
    protected function setFindingStartAttribute($value)
    {
        $this->attributes['finding_start'] = Carbon::parse($value)->startOfDay();
    }
    protected function setFindingEndAttribute($value)
    {
        $this->attributes['finding_end'] = Carbon::parse($value)->endOfDay();
    }

    // Reporting
    protected function setReportingStartAttribute($value)
    {
        $this->attributes['reporting_start'] = Carbon::parse($value)->startOfDay();
    }
    protected function setReportingEndAttribute($value)
    {
        $this->attributes['reporting_end'] = Carbon::parse($value)->endOfDay();
    }

    // RTM & RTL
    protected function setRtmRtlStartAttribute($value)
    {
        $this->attributes['rtm_rtl_start'] = Carbon::parse($value)->startOfDay();
    }
    protected function setRtmRtlEndAttribute($value)
    {
        $this->attributes['rtm_rtl_end'] = Carbon::parse($value)->endOfDay();
    }

    public function assignments()
    {
        return $this->hasMany(Assignment::class);
    }
}

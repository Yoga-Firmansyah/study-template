<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Assignment extends Model
{
    use SoftDeletes;
    protected $fillable = ['name', 'description', 'period_id', 'master_standard_id'];

    public function period()
    {
        return $this->belongsTo(Period::class);
    }

    public function standard()
    {
        return $this->belongsTo(MasterStandard::class, 'master_standard_id')->withTrashed();
    }

    public function indicators()
    {
        return $this->hasMany(AssignmentIndicator::class);
    }

    public function auditors()
    {
        return $this->belongsToMany(User::class, 'assignment_auditors', 'assignment_id', 'auditor_id');
    }
}

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
        'overall_rating'
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

    public function indicators()
    {
        return $this->hasMany(AssignmentIndicator::class);
    }

    public function auditor()
    {
        return $this->belongsTo(User::class, 'auditor_id');
    }

}

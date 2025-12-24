<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Period extends Model
{
    use SoftDeletes;
    protected $fillable = ['name', 'prodi_id', 'start_date', 'end_date', 'is_active'];

    public function prodi()
    {
        return $this->belongsTo(Prodi::class);
    }

    public function assignments()
    {
        return $this->hasMany(Assignment::class);
    }
}

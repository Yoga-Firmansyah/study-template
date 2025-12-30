<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Prodi extends Model
{
    use SoftDeletes;
    protected $fillable = ['name', 'code'];

    public function periods()
    {
        return $this->hasMany(Period::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function histories()
    {
        return $this->morphMany(AuditHistory::class, 'historable');
    }
}

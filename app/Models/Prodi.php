<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Filterable;
use App\Traits\HasAuditHistory;

class Prodi extends Model
{
    use SoftDeletes, Filterable, HasAuditHistory;
    protected $fillable = ['name', 'code'];

    public function periods()
    {
        return $this->hasMany(Period::class);
    }

    public function users()
    {
        return $this->hasMany(User::class);
    }

    public function assignments()
    {
        return $this->hasMany(Assignment::class);
    }

}

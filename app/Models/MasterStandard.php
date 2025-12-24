<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MasterStandard extends Model
{
    use SoftDeletes;
    protected $fillable = ['code', 'name', 'description'];

    public function indicators()
    {
        return $this->hasMany(MasterIndicator::class);
    }
}

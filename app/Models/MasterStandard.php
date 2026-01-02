<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use App\Traits\Filterable;
use App\Traits\HasAuditHistory;

class MasterStandard extends Model
{
    use SoftDeletes, Filterable, HasAuditHistory;
    protected $fillable = ['code', 'name', 'description'];

    public function indicators()
    {
        return $this->hasMany(MasterIndicator::class);
    }
}

<?php

namespace App\Models;

use App\Traits\HasAuditHistory;
use Illuminate\Database\Eloquent\Model;

class AssignmentDocument extends Model
{
    use HasAuditHistory;
    protected $fillable = ['assignment_id', 'type', 'file_path', 'uploaded_by'];

    public function assignment()
    {
        return $this->belongsTo(Assignment::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }
}

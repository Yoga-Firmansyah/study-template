<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AssessmentEvidence extends Model
{
    protected $table = 'assessment_evidence'; // Sesuai nama tabel di migrasi Anda
    protected $fillable = ['assignment_indicator_id', 'type', 'title', 'content'];

    public function indicator()
    {
        return $this->belongsTo(AssignmentIndicator::class, 'assignment_indicator_id');
    }
}

<?php

namespace App\Models;

use App\Enums\AssignmentDocType;
use App\Traits\HasAuditHistory;
use Illuminate\Database\Eloquent\Model;

class AssignmentDocument extends Model
{
    use HasAuditHistory;
    protected $fillable = ['assignment_id', 'type', 'file_path', 'uploaded_by'];

    protected function casts(): array
    {
        return [
            'type' => AssignmentDocType::class,
        ];
    }

    public function assignment()
    {
        return $this->belongsTo(Assignment::class);
    }

    public function uploader()
    {
        return $this->belongsTo(User::class, 'uploaded_by');
    }

    public function isLockedForReportChange(): bool
    {
        $assignment = $this->assignment;
        if (!$assignment)
            return true;
        $stage = $assignment->current_stage;

        return match ($this->type) {
            AssignmentDocType::FIELD_REPORT =>
            !$stage->fieldReport(),

            AssignmentDocType::FINAL_REPORT =>
            !$stage->finalReport(),

            AssignmentDocType::END_REPORT =>
            !$stage->endReport() && $assignment->period->is_active,
        };
    }
}

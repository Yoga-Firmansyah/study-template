<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('periods', function (Blueprint $table) {
            $table->id();
            $table->string('name'); // Contoh: AMI 2026 Ganjil

            // 6 Tahapan dengan rentang waktu DateTime
            $table->date('doc_audit_start');
            $table->date('doc_audit_end');
            $table->date('field_audit_start');
            $table->date('field_audit_end');
            $table->date('finding_start');
            $table->date('finding_end');
            $table->date('reporting_start');
            $table->date('reporting_end');
            $table->date('rtm_rtl_start');
            $table->date('rtm_rtl_end');

            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('periods');
    }
};

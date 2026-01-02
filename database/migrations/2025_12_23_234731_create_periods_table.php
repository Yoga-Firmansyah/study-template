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
            $table->string('name')->index(); // Contoh: AMI 2026 Ganjil

            // 6 Tahapan dengan rentang waktu DateTime
            $table->dateTime('doc_audit_start');
            $table->dateTime('doc_audit_end');
            $table->dateTime('field_audit_start');
            $table->dateTime('field_audit_end');
            $table->dateTime('finding_start');
            $table->dateTime('finding_end');
            $table->dateTime('reporting_start');
            $table->dateTime('reporting_end');
            $table->dateTime('rtm_rtl_start');
            $table->dateTime('rtm_rtl_end');

            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
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

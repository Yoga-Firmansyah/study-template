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
        Schema::create('audit_histories', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->comment('Siapa yang melakukan perubahan');

            // Polimorfik: Bisa merujuk ke MasterIndicator atau AssignmentIndicator
            $table->string('historable_type');
            $table->unsignedBigInteger('historable_id');
            $table->string('stage')->comment('Tahap saat perubahan terjadi: doc_audit, field_audit, dll');

            $table->json('old_values')->nullable()->comment('Data sebelum diubah');
            $table->json('new_values')->nullable()->comment('Data sesudah diubah');

            $table->string('action')->comment('Jenis aktivitas: update, delete, etc');
            $table->text('reason')->nullable()->comment('Alasan perubahan jika diperlukan');
            $table->timestamps();

            // Index untuk mempercepat pencarian history
            $table->index(['historable_type', 'historable_id']);
            $table->index('created_at');
            $table->index('stage');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('audit_histories');
    }
};

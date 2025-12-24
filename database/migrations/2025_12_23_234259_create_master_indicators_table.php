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
        Schema::create('master_indicators', function (Blueprint $table) {
            $table->id();
            $table->foreignId('master_standard_id')->constrained()->onDelete('cascade');
            $table->char('code', 6)->unique()->comment('Kode indikator, contoh: 1.1.1');
            $table->text('requirement')->comment('Deskripsi persyaratan');
            $table->boolean('is_evidence_required')->default(true)->comment('Apakah bukti diperlukan?');
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('master_indicators');
    }
};

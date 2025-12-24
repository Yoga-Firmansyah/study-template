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
            $table->string('name')->comment('Nama periode');
            $table->foreignId('prodi_id')->constrained('prodis')->restrictOnDelete();
            $table->date('start_date')->comment('Tanggal mulai');
            $table->date('end_date')->comment('Tanggal selesai');
            $table->boolean('is_active')->default(false)->comment('Apakah periode aktif?');
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

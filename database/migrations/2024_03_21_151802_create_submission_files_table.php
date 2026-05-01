<?php

// Šis fails izveido pieteikumiem pievienoto failu tabulu.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Izveido failu tabulu un sasaista katru failu ar darba pieteikumu.
    public function up(): void
    {
        Schema::create('submission_files', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_submission_id')->constrained('job_submissions')->onDelete('cascade');
            $table->string('file_name');
            $table->string('file_path');
            $table->string('mime_type')->nullable();
            $table->bigInteger('file_size')->nullable();
            $table->timestamps();
        });
    }

    // Dzēš pieteikumu failu tabulu.
    public function down(): void
    {
        Schema::dropIfExists('submission_files');
    }
};

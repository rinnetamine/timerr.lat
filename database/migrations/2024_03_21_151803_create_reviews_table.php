<?php

// Šis fails izveido atsauksmju tabulu starp pieteikuma pusēm.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Izveido atsauksmju tabulu, ja tā vēl nepastāv.
    public function up(): void
    {
        if (!Schema::hasTable('reviews')) {
            Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('reviewer_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('reviewee_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('job_submission_id')->constrained('job_submissions')->onDelete('cascade');
            $table->tinyInteger('rating')->unsigned();
            $table->text('comment')->nullable();
            $table->timestamps();

            // Katram pieteikumam drīkst būt tikai viena atsauksme.
            $table->unique('job_submission_id');
            });
        }
    }

    // Dzēš atsauksmju tabulu.
    public function down(): void
    {
        Schema::dropIfExists('reviews');
    }
};

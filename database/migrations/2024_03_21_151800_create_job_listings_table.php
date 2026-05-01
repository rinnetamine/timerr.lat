<?php

// Šis fails izveido darba sludinājumu tabulu ar kategoriju, kredītiem un attēla ceļu.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Izveido darba sludinājumu tabulu un sasaista sludinājumu ar lietotāju.
    public function up(): void
    {
        Schema::create('job_listings', function (Blueprint $table) {
            $table->id();
            $table->foreignIdFor(App\Models\User::class)->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('description');
            $table->integer('time_credits');
            $table->string('category');
            $table->string('image_path')->nullable();
            $table->timestamps();
        });
    }

    // Dzēš darba sludinājumu tabulu.
    public function down(): void
    {
        Schema::dropIfExists('job_listings');
    }
};

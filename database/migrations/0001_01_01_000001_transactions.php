<?php

// Šis fails izveido laika kredītu darījumu vēstures tabulu.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Izveido darījumu tabulu ar lietotāju, summu un aprakstu.
    public function up()
    {
        Schema::create('transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->integer('amount');
            $table->string('description');
            $table->timestamps();
        });
    }

    // Dzēš darījumu vēstures tabulu.
    public function down()
    {
        Schema::dropIfExists('transactions');
    }
};

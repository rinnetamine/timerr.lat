<?php

// Šis fails izveido publiskās kontaktformas ziņojumu tabulu.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Izveido kontaktziņojumu tabulu un pēc iespējas sasaista ziņojumu ar lietotāju.
    public function up()
    {
        Schema::create('contact_messages', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('email');
            $table->string('subject');
            $table->string('message');
            $table->foreignId('user_id')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();
        });
    }

    // Dzēš kontaktziņojumu tabulu.
    public function down()
    {
        Schema::dropIfExists('contact_messages');
    }
};

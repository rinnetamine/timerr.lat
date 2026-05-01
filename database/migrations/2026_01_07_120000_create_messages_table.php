<?php

// Šis fails izveido privāto ziņojumu tabulu ar pielikumu metadatiem.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Izveido ziņojumu tabulu ar sūtītāju, saņēmēju, tekstu un pielikumu laukiem.
    public function up(): void
    {
        Schema::create('messages', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sender_id')->constrained('users')->onDelete('cascade');
            $table->foreignId('recipient_id')->constrained('users')->onDelete('cascade');
            $table->text('body');
            $table->string('attachment_name')->nullable();
            $table->string('attachment_path')->nullable();
            $table->string('attachment_mime_type')->nullable();
            $table->unsignedBigInteger('attachment_size')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->timestamps();
        });
    }

    // Dzēš privāto ziņojumu tabulu.
    public function down(): void
    {
        Schema::dropIfExists('messages');
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Database\Seeders\DatabaseSeeder;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Seed default users after all tables are created
        $seeder = new DatabaseSeeder();
        $seeder->runWithDefaults();
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // No need to delete seeded data in down migration
        // Users can be manually deleted if needed
    }
};

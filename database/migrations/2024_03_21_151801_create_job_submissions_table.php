<?php

// Šis fails izveido darba pieteikumu tabulu ar statusiem, strīdiem un iesaldēšanas laukiem.

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    // Izveido pieteikumu tabulu ar saitēm uz darbu, lietotāju un strīda metadatiem.
    public function up(): void
    {
        Schema::create('job_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_listing_id')->constrained('job_listings')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->text('message')->nullable();
            $table->string('status')->default('claimed'); // Statusi: saņemts, gaida, apstiprināts, noraidīts, administratora pārskatīšana.
            $table->text('admin_notes')->nullable();
            $table->boolean('admin_approved')->default(false);
            
            // Strīdu lauki glabā iemeslu, iniciatoru, rezolūciju un administratora informāciju.
            $table->string('dispute_status')->default('none'); // Strīda statusi: nav, pieprasīts, pārskatīšanā, atrisināts.
            $table->text('dispute_reason')->nullable();
            $table->foreignId('dispute_initiated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('dispute_resolution')->nullable();
            $table->timestamp('dispute_resolved_at')->nullable();
            $table->foreignId('dispute_resolved_by')->nullable()->constrained('users')->onDelete('set null');
            
            // Iesaldēšana aptur pieteikuma apstrādi, kamēr strīds nav atrisināts.
            $table->boolean('is_frozen')->default(false);
            $table->text('freeze_reason')->nullable();
            
            $table->timestamps();
        });
    }

    // Dzēš darba pieteikumu tabulu.
    public function down(): void
    {
        Schema::dropIfExists('job_submissions');
    }
};

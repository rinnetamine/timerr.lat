<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('job_submissions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('job_listing_id')->constrained('job_listings')->onDelete('cascade');
            $table->foreignId('user_id')->constrained('users')->onDelete('cascade');
            $table->text('message')->nullable();
            $table->string('status')->default('claimed'); // claimed, pending, approved, declined, admin_review
            $table->text('admin_notes')->nullable();
            $table->boolean('admin_approved')->default(false);
            
            // dispute related fields
            $table->string('dispute_status')->default('none'); // none, requested, under_review, resolved
            $table->text('dispute_reason')->nullable();
            $table->foreignId('dispute_initiated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->text('dispute_resolution')->nullable();
            $table->timestamp('dispute_resolved_at')->nullable();
            $table->foreignId('dispute_resolved_by')->nullable()->constrained('users')->onDelete('set null');
            
            // freeze functionality
            $table->boolean('is_frozen')->default(false);
            $table->text('freeze_reason')->nullable();
            
            $table->timestamps();
        });
    }


    public function down(): void
    {
        Schema::dropIfExists('job_submissions');
    }
};

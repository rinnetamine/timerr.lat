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
        Schema::table('job_submissions', function (Blueprint $table) {
            // add admin notes for admin review messages
            if (!Schema::hasColumn('job_submissions', 'admin_notes')) {
                $table->text('admin_notes')->nullable()->after('status');
            }

            // add flag to indicate admin approved state
            if (!Schema::hasColumn('job_submissions', 'admin_approved')) {
                $table->boolean('admin_approved')->default(false)->after('admin_notes');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('job_submissions', function (Blueprint $table) {
            if (Schema::hasColumn('job_submissions', 'admin_approved')) {
                $table->dropColumn('admin_approved');
            }

            if (Schema::hasColumn('job_submissions', 'admin_notes')) {
                $table->dropColumn('admin_notes');
            }
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('chimera_print_jobs')) {
            Schema::create('chimera_print_jobs', function (Blueprint $table) {
                $table->id();
                // users.id is the legacy INT UNSIGNED type, not BIGINT.
                $table->unsignedInteger('user_id')->nullable();
                $table->foreign('user_id')->references('id')->on('users')->nullOnDelete();
                $table->string('status')->default('pending');
                $table->string('delivery_method')->default('tcp');
                $table->string('target_host')->nullable();
                $table->unsignedInteger('target_port')->nullable();
                $table->string('target_path')->nullable();
                $table->string('template_path')->nullable();
                $table->text('payload')->nullable();
                $table->unsignedInteger('asset_count')->default(0);
                $table->text('result_message')->nullable();
                $table->timestamps();

                $table->index('status');
                $table->index('created_at');
            });
        }

        if (!Schema::hasTable('chimera_print_job_assets')) {
            Schema::create('chimera_print_job_assets', function (Blueprint $table) {
                $table->id();
                $table->foreignId('chimera_print_job_id')->constrained()->cascadeOnDelete();
                $table->unsignedInteger('asset_id');
                $table->foreign('asset_id')->references('id')->on('assets')->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['chimera_print_job_id', 'asset_id']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('chimera_print_job_assets');
        Schema::dropIfExists('chimera_print_jobs');
    }
};

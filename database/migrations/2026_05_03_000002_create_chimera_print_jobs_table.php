<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('chimera_print_jobs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
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

        Schema::create('chimera_print_job_assets', function (Blueprint $table) {
            $table->id();
            $table->foreignId('chimera_print_job_id')->constrained()->cascadeOnDelete();
            $table->foreignId('asset_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['chimera_print_job_id', 'asset_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('chimera_print_job_assets');
        Schema::dropIfExists('chimera_print_jobs');
    }
};

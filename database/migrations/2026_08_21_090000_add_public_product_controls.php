<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('custom_fields', function (Blueprint $table) {
            $table->boolean('display_public')->default(false)->after('display_in_user_view');
            $table->string('public_section', 32)->nullable()->after('display_public');
            $table->unsignedSmallInteger('public_order')->default(0)->after('public_section');
            $table->index(['display_public', 'public_section'], 'custom_fields_public_section_index');
        });

        Schema::table('assets', function (Blueprint $table) {
            $table->boolean('public_product_enabled')->default(false)->after('scan_count');
            $table->timestamp('public_product_published_at')->nullable()->after('public_product_enabled');
            $table->index(['public_product_enabled', 'asset_tag'], 'assets_public_product_tag_index');
        });
    }

    public function down(): void
    {
        Schema::table('assets', function (Blueprint $table) {
            $table->dropIndex('assets_public_product_tag_index');
            $table->dropColumn(['public_product_enabled', 'public_product_published_at']);
        });

        Schema::table('custom_fields', function (Blueprint $table) {
            $table->dropIndex('custom_fields_public_section_index');
            $table->dropColumn(['display_public', 'public_section', 'public_order']);
        });
    }
};

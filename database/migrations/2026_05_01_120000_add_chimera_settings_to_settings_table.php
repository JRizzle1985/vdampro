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
        Schema::table('settings', function (Blueprint $table) {
            $table->boolean('chimera_enabled')->default(false)->after('manager_view_enabled');
            $table->string('chimera_printer_ip')->nullable()->after('chimera_enabled');
            $table->unsignedInteger('chimera_printer_port')->default(1680)->after('chimera_printer_ip');
            $table->string('chimera_scripts_path')->nullable()->after('chimera_printer_port');
            $table->string('chimera_delivery_method', 10)->default('tcp')->after('chimera_scripts_path');
            $table->string('chimera_qr_prefix')->nullable()->after('chimera_delivery_method');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('settings', function (Blueprint $table) {
            $table->dropColumn([
                'chimera_enabled',
                'chimera_printer_ip',
                'chimera_printer_port',
                'chimera_scripts_path',
                'chimera_delivery_method',
                'chimera_qr_prefix',
            ]);
        });
    }
};

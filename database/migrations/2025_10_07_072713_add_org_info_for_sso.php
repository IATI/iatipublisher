<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            if (!Schema::hasColumn('organizations', 'uuid')) {
                $table->uuid()->unique()->nullable()->after('id');
            }
            if (!Schema::hasColumn('organizations', 'registry_approved')) {
                $table->boolean('registry_approved')->default(false)->after('uuid');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('organizations', function (Blueprint $table) {
            $table->dropColumn([
                'uuid',
                'registry_approved',
            ]);
        });
    }
};

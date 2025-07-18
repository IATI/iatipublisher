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
            if (!Schema::hasColumn('organizations', 'org_uuid')) {
                $table->string('org_uuid')->unique()->nullable()->after('id');
            }
            if (!Schema::hasColumn('organizations', 'org_handle')) {
                $table->string('org_handle')->unique()->nullable()->after('org_uuid');
            }
            if (!Schema::hasColumn('organizations', 'registry_approved')) {
                $table->boolean('registry_approved')->default(false)->after('org_handle');
            }
            if (!Schema::hasColumn('organizations', 'name')) {
                $table->string('name')->nullable()->after('org_handle'); // Or adjust position
            }
            if (!Schema::hasColumn('organizations', 'slug')) {
                $table->string('slug')->unique()->nullable()->after('name');
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
                'org_uuid',
                'org_handle',
                'registry_approved',
                'name',
                'slug',
            ]);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->index('activity_identifier');
            $table->unique(['org_id', 'activity_identifier'], 'unique_org_activity_identifier');
        });
        DB::statement('ALTER TABLE activities ALTER COLUMN activity_identifier SET NOT NULL');
    }

    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->dropUnique('unique_org_activity_identifier');
        });
    }
};

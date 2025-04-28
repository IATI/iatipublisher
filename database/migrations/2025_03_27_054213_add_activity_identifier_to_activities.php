<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->string('activity_identifier')->nullable()->after('iati_identifier');
        });

        Artisan::call('command:CopyActivityIdentifier');
        Artisan::call('command:DeleteDuplicateActivities');
    }

    public function down(): void
    {
        Schema::table('activities', function (Blueprint $table) {
            $table->dropColumn('activity_identifier');
        });
    }
};

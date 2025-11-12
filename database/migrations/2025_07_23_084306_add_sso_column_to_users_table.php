<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->uuid()->unique()->nullable()->after('id');
            $table->string('sign_on_method')->nullable()->default('traditional')->after('migrated_from_aidstream');

            DB::statement('ALTER TABLE users ALTER COLUMN password DROP NOT NULL;');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'uuid',
                'sign_on_method',
            ]);
        });
    }
};

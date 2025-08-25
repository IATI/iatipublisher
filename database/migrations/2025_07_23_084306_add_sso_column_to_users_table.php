<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('sub')->unique()->nullable()->after('id');
            $table->string('preferred_username')->nullable()->after('username');
            $table->string('given_name')->nullable()->after('full_name');
            $table->string('family_name')->nullable()->after('given_name');
            $table->string('locale')->nullable()->after('language_preference');
            $table->string('picture')->nullable()->after('locale');
            $table->string('sign_on_method')
                ->nullable()
                ->default('traditional')
                ->after('migrated_from_aidstream');

            DB::statement('ALTER TABLE users ALTER COLUMN password DROP NOT NULL;');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn([
                'sub',
                'preferred_username',
                'given_name',
                'family_name',
                'locale',
                'picture',
                'sign_on_method',
            ]);
        });

        DB::statement('ALTER TABLE users ALTER COLUMN password SET NOT NULL;');
    }
};

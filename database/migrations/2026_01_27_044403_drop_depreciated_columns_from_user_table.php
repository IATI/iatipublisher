<?php

use App\Constants\DBTables;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table(DBTables::USERS, function (Blueprint $table) {
            Schema::table(DBTables::USERS, function (Blueprint $table) {
                if (Schema::hasColumn(DBTables::USERS, 'username')) {
                    $table->dropColumn('username');
                }

                if (Schema::hasColumn(DBTables::USERS, 'password')) {
                    $table->dropColumn('password');
                }

                if (Schema::hasColumn(DBTables::USERS, 'registration_method')) {
                    $table->dropColumn('registration_method');
                }

                if (Schema::hasColumn(DBTables::USERS, 'sign_on_method')) {
                    $table->dropColumn('sign_on_method');
                }
            });
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table(DBTables::USERS, function (Blueprint $table) {
            $table->string('username')->nullable();
            $table->string('password')->nullable();
            $table->string('registration_method')->nullable();
            $table->string('sign_on_method')->nullable();
        });
    }
};

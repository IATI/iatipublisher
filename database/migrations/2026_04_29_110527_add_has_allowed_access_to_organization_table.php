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
        Schema::table(DBTables::ORGANIZATIONS, function (Blueprint $table) {
            $table->boolean('has_allowed_access')->default(false);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table(DBTables::ORGANIZATIONS, function (Blueprint $table) {
            $table->dropColumn('has_allowed_access');
        });
    }
};

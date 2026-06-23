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
        Schema::table(DBTables::BULK_PUBLISHING_STATUS, function (Blueprint $table) {
//            $table->text('activity_title')->change();
            //DB:statement to not install package -> composer require doctrine/dbal
            DB::statement('ALTER TABLE bulk_publishing_status ALTER COLUMN activity_title TYPE text');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        DB::statement('ALTER TABLE bulk_publishing_status ALTER COLUMN activity_title TYPE varchar(255)');
    }
};

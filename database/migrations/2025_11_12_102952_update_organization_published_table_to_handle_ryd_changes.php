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
    public function up(): void
    {
        Schema::table(DBTables::ORGANIZATION_PUBLISH, function (Blueprint $table) {
            $table->uuid('dataset_uuid')->index()->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down(): void
    {
        $tableName = DBTables::ORGANIZATION_PUBLISH;
        $indexName = $tableName . '_dataset_uuid_index';

        Schema::table($tableName, function (Blueprint $table) use ($indexName) {
            if (Schema::hasIndex($table->getTable(), $indexName)) {
                $table->dropIndex($indexName);
            }

            $table->dropColumn('dataset_uuid');
        });
    }
};

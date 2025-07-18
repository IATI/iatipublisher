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

        $indexExists = DB::table('pg_indexes')
            ->where('tablename', $tableName)
            ->where('indexname', $indexName)
            ->exists();

        if ($indexExists) {
            DB::statement("DROP INDEX IF EXISTS {$indexName};");
        }

        Schema::table($tableName, function (Blueprint $table) {
            if (Schema::hasColumn($table->getTable(), 'dataset_uuid')) {
                $table->dropColumn('dataset_uuid');
            }
        });
    }
};

d<?php

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
        Schema::table('result_indicator_periods', function (Blueprint $table) {
            $defaultMap = [
                'period_start' => [],
                'period_end'   => [],
                'target'       => [],
                'actual'       => [],
            ];

            $table->json('deprecation_status_map')->nullable()->default(json_encode($defaultMap));
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('result_indicator_periods', function (Blueprint $table) {
            $table->dropColumn('deprecation_status_map');
        });
    }
};

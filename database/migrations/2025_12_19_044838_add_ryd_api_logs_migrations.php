<?php

use App\Constants\Enums;
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
        Schema::dropIfExists('api_logs');
        Schema::create(
            'ryd_api_logs',
            function (Blueprint $table) {
                $table->bigIncrements('id');
                $table->string('method')->nullable();
                $table->text('url')->nullable();
                $table->text('request')->nullable();
                $table->text('response')->nullable();
                $table->enum('type', Enums::API_TYPE)->nullable()->default('external');
                $table->timestamps();
            }
        );
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('ryd_api_logs');
    }
};

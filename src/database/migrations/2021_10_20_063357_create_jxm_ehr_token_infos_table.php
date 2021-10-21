<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJxmEhrTokenInfosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('jxm_ehr_token_infos', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->morphs('user', 'users');
            $table->string('token_type');
            $table->string('access_token');
            $table->string('refresh_token');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('jxm_ehr_token_infos');
    }
}

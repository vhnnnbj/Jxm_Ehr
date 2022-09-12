<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up()
    {
        Schema::create('esb_message_records', function (Blueprint $table) {
            $table->id();
            formatRecordTableColumn($table);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('esb_message_records');
    }
};

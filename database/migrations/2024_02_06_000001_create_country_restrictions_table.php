<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('country_restrictions', function (Blueprint $table) {
            $table->id();
            $table->string('country_code', 2);
            $table->string('country_name');
            $table->enum('action', ['allow', 'block'])->default('block');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('country_restrictions');
    }
};

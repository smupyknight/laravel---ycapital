<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DeleteUnusedFilterTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::drop('cases_type');
        Schema::drop('hearings_type');
        Schema::drop('party_representatives');
        Schema::drop('court_suburbs');

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::create('cases_type', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name',255);
            $table->timestamps();
        });

        Schema::create('hearings_type', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name',255);
            $table->timestamps();
        });

        Schema::create('party_representatives', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name',255);
            $table->timestamps();
        });

        Schema::create('court_suburbs', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name',255);
            $table->timestamps();
        });

    }
}

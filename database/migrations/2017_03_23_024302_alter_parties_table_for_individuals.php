<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AlterPartiesTableForIndividuals extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('parties', function($table) {
            $table->enum('type', ['Unknown', 'Company', 'Individual'])->after('name');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('parties', function($table) {
            $table->dropColumn('type');
        });
    }
}

<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddAcnToParties extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('parties', function (Blueprint $table) {
            $table->string('acn')->nullable()->after('abn');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('parties', function (Blueprint $table) {
            $table->dropColumn('acn');
        });
    }
}

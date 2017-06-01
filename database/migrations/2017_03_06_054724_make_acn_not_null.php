<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MakeAcnNotNull extends Migration
{

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('parties', function (Blueprint $table) {
			$table->string('acn', 255)->nullable(false)->change();
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
			$table->string('acn', 255)->nullable()->change();
		});
	}

}

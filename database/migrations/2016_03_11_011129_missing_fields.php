<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MissingFields extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('hearings', function (Blueprint $table) {
			$table->datetime('datetime')->after('application_id');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('hearings', function (Blueprint $table) {
			$table->dropColumn('datetime');
		});
	}
}

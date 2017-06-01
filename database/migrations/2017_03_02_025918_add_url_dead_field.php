<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUrlDeadField extends Migration
{

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('cases', function (Blueprint $table) {
			$table->tinyInteger('url_dead')->after('url');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('cases', function (Blueprint $table) {
			$table->dropColumn('url_dead');
		});
	}

}

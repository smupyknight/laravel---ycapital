<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class WatchlistStopwords extends Migration
{

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('parties', function (Blueprint $table) {
			$table->string('searchable_name')->after('acn');
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
			$table->dropColumn('searchable_name');
		});
	}

}

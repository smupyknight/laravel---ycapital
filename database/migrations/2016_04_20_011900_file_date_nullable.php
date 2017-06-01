<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class FileDateNullable extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('applications', function(Blueprint $table) {
			$table->datetime('date_filed')->nullable()->change();
		});

		DB::statement("update applications set date_filed = null where date_filed = '0000-00-00 00:00:00'");
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('applications', function(Blueprint $table) {
			$table->datetime('date_filed')->change();
		});
	}
}

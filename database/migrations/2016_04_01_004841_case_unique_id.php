<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CaseUniqueId extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('cases', function (Blueprint $table) {
			$table->string('unique_id', 250)->unique()->after('id');
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
			$table->dropColumn('unique_id');
		});
	}
}

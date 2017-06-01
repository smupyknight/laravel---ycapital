<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CaseType extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('scrape_results', function (Blueprint $table) {
			$table->string('case_type', 255)->after('case_name');
		});

		Schema::table('cases', function (Blueprint $table) {
			$table->string('case_type', 255)->after('case_name');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('scrape_results', function (Blueprint $table) {
			$table->dropColumn('case_type');
		});

		Schema::table('cases', function (Blueprint $table) {
			$table->dropColumn('case_type');
		});
	}
}

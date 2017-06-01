<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class NormaliseJurisdiction extends Migration
{

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		DB::statement("UPDATE cases SET jurisdiction = IF(case_type LIKE '%Criminal%', 'Criminal', 'Civil') WHERE jurisdiction = ''");
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		// Not needed.
	}

}

<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class NormaliseCaseTypes extends Migration
{

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		DB::update("update cases c inner join applications a on a.case_id = c.id set c.case_type = a.type where a.type != '' and c.court_type = 'Federal'");
		DB::update("update cases c inner join applications a on a.case_id = c.id set c.case_type = a.type where a.type != '' and c.court_type != 'Federal' and c.state = 'ACT'");
		DB::update("update cases c inner join applications a on a.case_id = c.id set c.case_type = a.type where a.type != '' and c.court_type != 'Federal' and c.state = 'NSW'");
		DB::update("update cases c inner join applications a on a.case_id = c.id set c.case_type = a.title where a.title != '' and c.court_type != 'Federal' and c.court_type != 'Magistrates' and c.state = 'QLD'");
		DB::update("update cases c inner join applications a on a.case_id = c.id set c.case_type = a.title where a.title != '' and c.court_type = 'Magistrates' and c.state = 'VIC'");
		DB::update("update cases c inner join applications a on a.case_id = c.id set c.case_type = a.type where a.type != '' and c.court_type = 'County' and c.state = 'VIC'");
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

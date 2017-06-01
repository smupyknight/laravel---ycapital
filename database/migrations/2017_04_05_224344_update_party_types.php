<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdatePartyTypes extends Migration
{

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		DB::statement("ALTER TABLE parties MODIFY type ENUM('Other', 'Company', 'Individual', 'ASIC', 'ACCC', 'ATO')");
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		DB::statement("ALTER TABLE parties MODIFY type ENUM('Unknown','Company', 'Individual')");
	}

}

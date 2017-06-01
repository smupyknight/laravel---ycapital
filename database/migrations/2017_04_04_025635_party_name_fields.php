<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class PartyNameFields extends Migration
{

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::table('parties', function (Blueprint $table) {
			$table->string('given_names', 255)->after('name');
			$table->string('last_name', 255)->after('given_names');
		});

		DB::statement("UPDATE parties SET given_names = LEFT(name, LENGTH(name) - LOCATE(' ', REVERSE(name))), last_name = IF(LOCATE(' ', name), SUBSTRING_INDEX(name, ' ', -1), '') WHERE type = 'Individual'");
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::table('parties', function (Blueprint $table) {
			$table->dropColumn('given_names');
			$table->dropColumn('last_name');
		});
	}

}

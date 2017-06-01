<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropMaterials extends Migration
{

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::drop('materials');
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::create('materials', function (Blueprint $table) {
			$table->increments('id');
			$table->integer('application_id')->unsigned()->index();
			$table->string('location');
			$table->string('party');
			$table->string('description');
			$table->string('type');
			$table->datetime('date_returned')->nullable();
			$table->string('evidence');
			$table->timestamps();
		});
	}

}

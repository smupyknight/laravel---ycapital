<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Filters extends Migration
{

	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::drop('filters');

		Schema::create('filters', function (Blueprint $table) {
			$table->increments('id');
			$table->integer('user_id')->unsigned()->index();
			$table->string('name');
			$table->string('state');
			$table->string('court_type');
			$table->string('notification_date');
			$table->string('case_types');
			$table->string('hearing_types');
			$table->string('hearing_date');
			$table->string('document_date');
			$table->string('court_suburbs');
			$table->string('party_representatives');
			$table->tinyInteger('per_page')->unsigned();
			$table->datetime('created_at');
			$table->datetime('updated_at');
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('filters');

		Schema::create('filters', function (Blueprint $table) {
			$table->increments('id');
			$table->integer('user_id')->unsigned()->index();
			$table->text('setting');
			$table->string('name');
			$table->tinyInteger('default');
			$table->timestamps();
		});
	}

}

<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MostTables extends Migration
{
	/**
	 * Run the migrations.
	 *
	 * @return void
	 */
	public function up()
	{
		Schema::create('applications', function(Blueprint $table) {
			$table->increments('id');
			$table->integer('case_id')->unsigned()->index();
			$table->string('title', 255);
			$table->string('type', 255);
			$table->string('status', 255);
			$table->datetime('date_filed');
			$table->datetime('date_finalised')->nullable();
			$table->timestamps();

			$table->engine = 'MyISAM';
		});

		Schema::create('cases', function(Blueprint $table) {
			$table->increments('id');
			$table->string('state', 255);
			$table->string('court_type', 255);
			$table->string('case_no', 255);
			$table->string('case_name', 255);
			$table->string('suburb', 255);
			$table->string('jurisdiction', 255);
			$table->string('url', 255);
			$table->datetime('date_added');
			$table->timestamps();

			$table->engine = 'MyISAM';
		});

		Schema::create('documents', function(Blueprint $table) {
			$table->increments('id');
			$table->integer('application_id');
			$table->string('title', 255);
			$table->string('description', 255);
			$table->string('filed_by', 255);
			$table->timestamps();

			$table->engine = 'MyISAM';
		});

		Schema::create('hearings', function(Blueprint $table) {
			$table->increments('id');
			$table->integer('application_id');
			$table->string('reason', 255);
			$table->string('officer', 255);
			$table->string('court_room', 255);
			$table->string('court_name', 255);
			$table->string('court_phone', 255);
			$table->string('court_address', 255);
			$table->string('court_suburb', 255);
			$table->string('type', 255);
			$table->string('list_no', 255);
			$table->string('outcome', 255);
			$table->string('orders_filename', 255);
			$table->timestamps();

			$table->engine = 'MyISAM';
		});

		Schema::create('materials', function(Blueprint $table) {
			$table->increments('id');
			$table->integer('application_id');
			$table->string('location', 255);
			$table->string('party', 255);
			$table->string('description', 255);
			$table->string('type', 255);
			$table->datetime('date_returned')->nullable();
			$table->string('evidence', 255);
			$table->timestamps();

			$table->engine = 'MyISAM';
		});

		Schema::create('parties', function(Blueprint $table) {
			$table->increments('id');
			$table->integer('application_id');
			$table->string('party_id', 255);
			$table->string('name', 255);
			$table->string('role', 255);
			$table->string('address', 255);
			$table->string('phone', 255);
			$table->string('fax', 255);
			$table->string('email', 255);
			$table->string('abn', 255);
			$table->timestamps();

			$table->engine = 'MyISAM';
		});

		Schema::create('scrape_results', function(Blueprint $table) {
			$table->increments('id');
			$table->string('scraper', 16);
			$table->string('unique_id', 32);
			$table->string('state', 255);
			$table->string('court_type', 255);
			$table->string('case_no', 255);
			$table->string('case_name', 255);
			$table->string('suburb', 255);
			$table->string('jurisdiction', 255);
			$table->string('url', 255);
			$table->mediumText('data', 255);
			$table->text('notes', 255);
			$table->timestamps();

			$table->unique(['scraper','unique_id']);
			$table->engine = 'MyISAM';
		});
	}

	/**
	 * Reverse the migrations.
	 *
	 * @return void
	 */
	public function down()
	{
		Schema::drop('applications');
		Schema::drop('cases');
		Schema::drop('documents');
		Schema::drop('hearings');
		Schema::drop('materials');
		Schema::drop('parties');
		Schema::drop('scrape_results');
	}
}

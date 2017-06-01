<?php

use App\Application;
use App\CourtCase;
use App\Party;
use App\Setting;
use App\Watchlist;
use App\WatchlistEntity;

class WatchlistTest extends TestCase
{

	public function testEverything()
	{
		// Exact name match
		$this->withParty(['name' => 'Foo'])
			->andEntity(['party_name' => 'Foo'])
			->expectMatch('exact');

		// Exact ABN match
		$this->withParty(['name' => 'Foo', 'abn' => '123'])
			->andEntity(['party_name' => 'Bar', 'abn' => '123'])
			->expectMatch('exact');

		// Exact ACN match
		$this->withParty(['name' => 'Foo', 'acn' => '123'])
			->andEntity(['party_name' => 'Bar', 'acn' => '123'])
			->expectMatch('exact');

		// Test name matches ignoring stopwords
		$this->withParty(['name' => 'Foo Bar Pty Ltd'])
			->andEntity(['party_name' => 'Foo Bar Inc'])
			->expectMatch('contains');

		// Test name matches with punctuation
		$this->withParty(['name' => 'Foo Bar Pty. Ltd.'])
			->andEntity(['party_name' => 'Foo Bar Inc.'])
			->expectMatch('contains');

		// Test entity with ABN, party without
		$this->withParty(['name' => 'Foo Bar Pty Ltd'])
			->andEntity(['party_name' => 'Foo Bar Inc', 'abn' => 123])
			->expectMatch('contains');

		// Test party with ABN, entity without
		$this->withParty(['name' => 'Foo Bar Pty Ltd', 'abn' => 123])
			->andEntity(['party_name' => 'Foo Bar Inc'])
			->expectMatch('contains');

		// Test both with different ABNs
		$this->withParty(['name' => 'Foo Bar Pty Ltd', 'abn' => 123])
			->andEntity(['party_name' => 'Foo Bar Inc', 'abn' => 321])
			->expectMatch('contains');

		// Test contains match doesn't apply if entity name is one word and party name is two or more words
		$this->withParty(['name' => 'Jeffrey Hills'])
			->andEntity(['party_name' => 'Hills Limited'])
			->expectNoMatch();

		// Test contains match doesn't apply on partial words
		$this->withParty(['name' => 'Environmental Services'])
			->andEntity(['party_name' => 'TAL Services'])
			->expectNoMatch();

		// Test to assert the no match is found if the entity is an individual
		$this->withParty(['name' => 'Foo'])
			->andEntity(['party_name' => 'Foo', 'type' => 'Individual'])
			->expectNoMatch();

		// Test to assert the no match is found if the party is an individual
		$this->withParty(['name' => 'Foo', 'type' => 'Individual'])
			->andEntity(['party_name' => 'Foo'])
			->expectNoMatch();
	}

	private function withParty(array $party_fields)
	{
		$this->party_fields = $party_fields;
		return $this;
	}

	private function andEntity(array $entity_fields)
	{
		$this->entity_fields = $entity_fields;
		return $this;
	}

	private function expectMatch($type)
	{
		$this->doMatch();

		$this->seeInDatabase('watchlist_notifications', [
			'case_id'    => '1',
			'match_type' => $type,
		]);
	}

	private function expectNoMatch()
	{
		$this->doMatch();

		$this->dontSeeInDatabase('watchlist_notifications', [
			'case_id' => '1',
		]);
	}

	private function doMatch()
	{
		self::resetDatabase();

		DB::table('settings')->insert([
			'field' => 'criminal_jurisdiction',
			'value' => 1,
		]);

		// Create case
		$case = factory(CourtCase::class)->create();

		// Create application
		$application = factory(Application::class)->create(['case_id' => $case->id]);

		// Create party
		$fields = $this->party_fields;
		$fields['application_id'] = $application->id;
		if (!isset($fields['type'])) {
			$fields['type'] = 'Company';
		}

		$party = factory(Party::class)->create($fields);
		$party->searchable_name = $party->getCalculatedSearchableName();
		$party->save();

		// Create watchlist
		$watchlist = factory(Watchlist::class)->create();

		// Create entity
		$fields = $this->entity_fields;
		$fields['watchlist_id'] = $watchlist->id;
		if (!isset($fields['type'])) {
			$fields['type'] = 'Company';
		}

		factory(WatchlistEntity::class)->create($fields);

		// Do the match
		DB::table('watchlist_queue')->insert(['id' => $case->id]);
		Artisan::call('process-watchlist-queue');
	}

}

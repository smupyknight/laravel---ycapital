<?php

use App\Application;
use App\CourtCase;
use App\Party;
use App\Setting;
use App\Watchlist;
use App\WatchlistEntity;

class IndividualWatchlistTest extends TestCase
{

	public function testIndividualWatchlist()
	{
		// Test no match if party is company type
		$this->withParty(['given_names' => 'Foo', 'last_name' => 'Bar', 'type' => 'Company'])
			->andEntity(['party_given_names' => 'Foo', 'party_last_name' => 'Bar'])
			->expectNoMatch();

		// Test no match if entity is company type
		$this->withParty(['given_names' => 'Foo', 'last_name' => 'Bar'])
			->andEntity(['party_given_names' => 'Foo', 'party_last_name' => 'Bar', 'type' => 'Company'])
			->expectNoMatch();

		// Exact ABN Match
		$this->withParty(['abn' => 123])
			->andEntity(['abn' => 123])
			->expectMatch('exact');

		// Test contains match doesn't apply on partial words
		$this->withParty(['given_names' => 'land', 'last_name' => 'Bar'])
			->andEntity(['party_given_names' => 'Greenland', 'party_last_name' => 'Bar'])
			->expectNoMatch();

		// Contains name match
		$this->withParty(['given_names' => 'Foo', 'last_name' => 'Bar'])
			->andEntity(['party_given_names' => 'Foo', 'party_last_name' => 'Bar'])
			->expectMatch('contains');

		// Test does not match ignoring stopwords
		$this->withParty(['given_names' => 'Miss'])
			->andEntity(['party_given_names' => 'Miss'])
			->expectNoMatch();

		// Test matches, ignoring stopwords
		$this->withParty(['given_names' => 'Miss Jane', 'last_name' => 'Doe'])
			->andEntity(['party_given_names' => 'Jane', 'party_last_name' => 'Doe'])
			->expectMatch('contains');

		// Test no match where party last name is different
		$this->withParty(['given_names' => 'Foo', 'last_name' => 'Smith'])
			->andEntity(['party_given_names' => 'Foo', 'party_last_name' => 'Bar'])
			->expectNoMatch();

		// Test no match where entity last name is different
		$this->withParty(['given_names' => 'Foo', 'last_name' => 'Foo'])
			->andEntity(['party_given_names' => 'Foo', 'party_last_name' => 'Smith'])
			->expectNoMatch();

		// Test name matches with punctuation
		$this->withParty(['given_names' => 'Foo', 'last_name' => 'Bar'])
			->andEntity(['party_given_names' => 'Foo.', 'party_last_name' => 'Bar'])
			->expectMatch('contains');

		// Test name match even if ABN succeeds
		$this->withParty(['given_names' => 'Foo', 'last_name' => 'Bar', 'abn' => 123])
			->andEntity(['party_given_names' => 'Foo', 'party_last_name' => 'Bar', 'abn' => 123])
			->expectMatch('contains');

		// Test entity with ABN, party without
		$this->withParty(['given_names' => 'Foo', 'last_name' => 'Bar'])
			->andEntity(['party_given_names' => 'Foo', 'party_last_name' => 'Bar', 'abn' => 123])
			->expectMatch('contains');

		// Test party with ABN, entity without
		$this->withParty(['given_names' => 'Foo', 'last_name' => 'Bar', 'abn' => 123])
			->andEntity(['party_given_names' => 'Foo', 'party_last_name' => 'Bar'])
			->expectMatch('contains');

		// Test both with different ABNs
		$this->withParty(['given_names' => 'Foo', 'last_name' => 'Bar', 'abn' => 123])
			->andEntity(['party_given_names' => 'John', 'party_last_name' => 'Smith', 'abn' => 321])
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
		$fields = [];
		$fields = $this->party_fields;
		$fields['application_id'] = $application->id;
		if (!isset($fields['type'])) {
			$fields['type'] = 'Individual';
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
			$fields['type'] = 'Individual';
		}

		factory(WatchlistEntity::class)->create($fields);

		// Do the match
		$job = new \App\Jobs\ProcessIndividualWatchlist($case);
		$job->handle();
	}

}

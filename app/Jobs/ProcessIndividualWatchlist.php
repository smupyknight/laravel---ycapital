<?php

namespace App\Jobs;

use App\Jobs\Job;
use Illuminate\Queue\SerializesModels;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Contracts\Bus\SelfHandling;
use Illuminate\Contracts\Queue\ShouldQueue;
use App\CourtCase;
use App\Party;
use App\WatchlistEntity;
use App\WatchlistNotification;
use Log;
use DB;

class ProcessIndividualWatchlist extends Job implements SelfHandling, ShouldQueue
{
	use InteractsWithQueue, SerializesModels;

	private $court_case;

	/**
	 * Create a new job instance.
	 *
	 * @return void
	 */
	public function __construct(CourtCase $court_case)
	{
		$this->court_case = $court_case;
	}

	/**
	 * Execute the job.
	 *
	 * @return void
	 */
	public function handle()
	{
		$court_case = $this->court_case;

		$watchlist_entities = WatchlistEntity::whereType('Individual')->get();

		foreach ($watchlist_entities as $entity) {

			if ($entity->abn) {
				if ($this->processAbnMatch($court_case, $entity) >= 1) {
					WatchlistNotification::create([
						'watchlist_entity_id' => $entity->id,
						'case_id'             => $court_case->id,
						'match_type'          => 'Exact',
					]);
				}
			}

			if ($this->processNameMatch($court_case, $entity) >= 1) {
				WatchlistNotification::create([
					'watchlist_entity_id' => $entity->id,
					'case_id'             => $court_case->id,
					'match_type'          => 'Contains',
				]);
			}
		}
	}

	private function processNameMatch(CourtCase $court_case, WatchlistEntity $entity)
	{
		$given_names = $entity->party_given_names;
		$given_names = strtolower($given_names);
		$given_names = str_replace(",", '', $given_names);
		$given_names = str_replace("'", '', $given_names);
		$given_names = $this->removeStopWords($given_names);
		$given_names = explode(' ', $given_names);

		if (!$given_names || !$entity->party_last_name) {
			return;
		}

		$query = DB::table('applications AS a')
			->join('parties AS p', 'p.application_id', '=', 'a.id')
			->where('a.case_id', $court_case->id)
			->where('p.type', 'Individual')
			->where('p.last_name', $entity->party_last_name);

		$query->where( function ($query) use ($given_names, $entity) {
			foreach ($given_names as $given_name) {
				$query->orWhere('p.searchable_name', 'LIKE', '% ' . $given_name . ' %');
			}
		});

		return $query->count();
	}

	private function processAbnMatch(CourtCase $court_case, WatchlistEntity $entity)
	{
		$query = DB::table('applications AS a')
			->join('parties AS p', 'p.application_id', '=', 'a.id')
			->where('a.case_id', $court_case->id)
			->where('p.type', 'Individual');

		$query->where('p.abn', $entity->abn);

		return $query->count();
	}

	private function removeStopWords($text)
	{
		$name = strtolower($text);
		$name = str_replace("'", '', $name);

		$stopwords = ['mr', 'mrs', 'ms', 'miss'];

		preg_match_all('/[a-z0-9]+/', $name, $matches);
		$words = $matches[0];

		$words = array_filter($words, function($word) use ($stopwords) {
			return !in_array($word, $stopwords);
		});

		return implode(' ', $words);
	}

}

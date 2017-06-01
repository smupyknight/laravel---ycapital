<?php
namespace App\Console\Commands;

use App\Setting;
use App\WatchlistEntity;
use App\WatchlistNotification;
use App\WatchlistQueueItem;
use DB;
use Illuminate\Console\Command;
use Illuminate\Support\Collection;

class ProcessWatchlistQueue extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'process-watchlist-queue';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Processes the watchlist queue.';

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
		$entities = WatchlistEntity::whereType('Company')->get();

		$bar = $this->output->createProgressBar(WatchlistQueueItem::count());

		$bar->setFormat(' %current%/%max% [%bar%] %percent:3s%% %elapsed:6s%/%estimated:-6s% %memory:6s%');

		while ($item = WatchlistQueueItem::first()) {
			$item->delete();
			$this->processItem($item, $entities);
			$bar->advance();
		}

		$bar->finish();
	}

	/**
	 * Processes this queue item.
	 */
	private function processItem(WatchlistQueueItem $item, Collection $entities)
	{
		foreach ($entities as $entity) {
			$entity->party_name = $this->removeStopWords($entity->party_name);
			$type = $this->getMatchType($item, $entity);

			if ($type) {
				WatchlistNotification::create([
					'watchlist_entity_id' => $entity->id,
					'case_id'             => $item->id,
					'match_type'          => $type,
				]);
			}
		}
	}

	private function removeStopWords($text)
	{
		$name = strtolower($text);
		$name = str_replace("'", '', $name);

		$stopwords = ['and','co','for','inc','limited','ltd','mr','mrs','of','pty'];

		preg_match_all('/[a-z0-9]+/', $name, $matches);
		$words = $matches[0];

		$words = array_filter($words, function($word) use ($stopwords) {
			return !in_array($word, $stopwords);
		});

		return implode(' ', $words);
	}

	/**
	 * Determines if the item's party name is an exact match or not.
	 */
	private function getMatchType(WatchlistQueueItem $item, WatchlistEntity $entity)
	{
		// Get info for exact match types
		$query = DB::table('applications AS a')
			->join('parties AS p', 'p.application_id', '=', 'a.id')
			->where('a.case_id', $item->id)
			->where('p.type', '!=', 'Individual')
			->selectRaw('SUM(p.abn = ?) AS num_abn_exact', [$entity->abn])
			->selectRaw('SUM(p.acn = ?) AS num_acn_exact', [$entity->acn])
			->selectRaw('SUM(p.name = ?) AS num_name_exact', [$entity->party_name])
			->selectRaw('SUM(p.searchable_name LIKE ?) AS num_name_contains', ['% ' . $entity->party_name . ' %']);

		if (Setting::where('field', 'criminal_jurisdiction')->first()->value == 0) {
			$query->join('cases AS c', 'a.case_id', '=', 'c.id');
			$query->where('c.jurisdiction', '!=', 'Criminal');
		}

		$row = $query->first();

		if ($entity->acn && $row->num_acn_exact) {
			return 'exact';
		}

		if ($entity->abn && $row->num_abn_exact) {
			return 'exact';
		}

		if ($entity->party_name) {
			if ($row->num_name_exact) {
				return 'exact';
			}

			if ($row->num_name_contains && strpos($entity->party_name, ' ') !== false) {
				return 'contains';
			}
		}

		return false;
	}

}

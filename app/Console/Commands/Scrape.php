<?php
namespace App\Console\Commands;

use App;
use App\ScrapeResult;
use App\ScrapeResults\Result;
use DB;
use DateTime;
use DateTimeZone;
use Illuminate\Console\Command;
use InvalidArgumentException;
use Storage;

class Scrape extends Command
{
	/**
	 * The name and signature of the console command.
	 *
	 * @var string
	 */
	protected $signature = 'scrape {name}';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Scrape one of the court listing websites';

	/**
	 * A HTTP transport.
	 *
	 * @var null
	 */
	protected $transport = null;

	/**
	 * The name of the scraper which is running.
	 *
	 * @var string
	 */
	protected $scraper_name = null;

	/**
	 * Constructor.
	 *
	 * @param [type] $transport
	 */
	public function __construct($transport)
	{
		parent::__construct();

		$this->transport = $transport;
	}

	/**
	 * Execute the console command.
	 *
	 * @return mixed
	 */
	public function handle()
	{
		$this->scraper_name = $this->argument('name');

		switch ($this->scraper_name) {
			case 'qld':
				$scraper = new App\Scrapers\QueenslandCourtsScraper($this->transport);
				break;
			case 'qld-magistrates':
				$scraper = new App\Scrapers\QueenslandMagistratesCourtsScraper($this->transport);
				break;
			case 'nsw':
				$scraper = new App\Scrapers\OnlineRegistryScraper($this->transport);
				break;
			case 'vic-supreme':
				$scraper = new App\Scrapers\SupremeCourtVictoriaScraper($this->transport);
				break;
			case 'vic-county':
				$scraper = new App\Scrapers\CountyCourtVictoriaScraper($this->transport);
				break;
			case 'vic-magistrates':
				$scraper = new App\Scrapers\MagistratesVictoriaScraper($this->transport);
				break;
			case 'wa':
				$scraper = new App\Scrapers\WaDotagScraper($this->transport);
				break;
			case 'act-supreme':
				$scraper = new App\Scrapers\ActCourtsScraper($this->transport, 'http://www.courts.act.gov.au/supreme/lists', 'Supreme');
				break;
			case 'act-magistrates':
				$scraper = new App\Scrapers\ActCourtsScraper($this->transport, 'http://www.courts.act.gov.au/magistrates/lists', 'Magistrates');
				break;
			case 'act-acat':
				$scraper = new App\Scrapers\ActCourtsScraper($this->transport, 'http://www.acat.act.gov.au/lists', 'ACAT');
				break;
			case 'nt':
				$scraper = new App\Scrapers\NtCatScraper($this->transport);
				break;
			case 'ecourts-wa':
				$scraper = new App\Scrapers\EcourtsWaScraper($this->transport);
				break;
			case 'sa-cat':
				$scraper = new App\Scrapers\SaCatScraper($this->transport);
				break;
			case 'federal-1':
				$scraper = new App\Scrapers\ComCourtsSearcher($this->transport);
				break;
			case 'fix-vic-supreme':
				$scraper = new App\Scrapers\SupremeCourtVictoriaScraper($this->transport);
				$scraper->overridePdf([
					Storage::get('scrapers/vic-supreme-2017-04-05.pdf'),
					Storage::get('scrapers/vic-supreme-2017-04-06.pdf'),
				]);
				$this->scraper_name = 'vic-supreme';
				break;
			case 'fix-nt':
				$scraper = new App\Scrapers\NtCatScraper($this->transport);
				$scraper->overridePdf([
					Storage::get('scrapers/nt-2017-04-05.pdf'),
					Storage::get('scrapers/nt-2017-04-06.pdf'),
				]);
				$this->scraper_name = 'nt';
				break;
			default:
				throw new InvalidArgumentException('Unknown scraper: ' . $this->scraper_name);
		}

		$scraper->setStartDate(new DateTime('Yesterday', new DateTimeZone('Australia/Sydney')));
		$scraper->setCallback([$this, 'handleResult']);
		$scraper->run();
	}

	public function handleResult(Result $result)
	{

		$result_id = ScrapeResult::where('scraper', $this->scraper_name)->where('unique_id', $result->getUniqueId())->value('id');

		$result_array = $result->asArray();

		$data = [
			'applications'  => $result_array['applications'],
			'related_cases' => $result_array['related_cases'],
		];

		$fields = [
			'scraper'      => $this->scraper_name,
			'unique_id'    => $result->getUniqueId(),
			'state'        => $result->getState(),
			'court_type'   => $result->getCourtType(),
			'case_no'      => $result->getCaseNumber(),
			'case_name'    => $result->getCaseName(),
			'case_type'    => $result->getCaseType(),
			'suburb'       => $result->getSuburb(),
			'jurisdiction' => $result->getJurisdiction(),
			'url'          => $result->getUrl(),
			'data'         => json_encode($data),
			'created_at'   => (new DateTime)->setTimezone(new DateTimeZone('UTC')),
			'updated_at'   => (new DateTime)->setTimezone(new DateTimeZone('UTC')),
		];

		if ($result_id) {
			// Updating
			$sets = array_map(function($fieldname) {
				return "`$fieldname` = ?";
			}, array_keys($fields));

			DB::update("UPDATE scrape_results SET " . implode(', ', $sets) . " WHERE id = ?",
				array_merge(array_values($fields), [$result_id])
			);
		} else {
			// Inserting
			$updateable = array_diff(array_keys($fields), ['scraper','unique_id','created_at','updated_at']);
			$updateable = array_map(function($fieldname) {
				return "`$fieldname` = VALUES(`$fieldname`)";
			}, $updateable);

			DB::insert(
				"INSERT INTO scrape_results (`" . implode("`,`", array_keys($fields)) . "`)
				VALUES (" . implode(', ', array_fill(0, count($fields), '?')) . ")
				ON DUPLICATE KEY UPDATE " . implode(', ', $updateable),
				array_values($fields)
			);

			$result_id = DB::select("SELECT LAST_INSERT_ID()")[0]->{'LAST_INSERT_ID()'};
		}

		$result = ScrapeResult::find($result_id);

		if ($result && $result->validate()) {
			$result->approve();
		}
	}

}

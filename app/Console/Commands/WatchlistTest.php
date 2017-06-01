<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\CourtCase;
use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Jobs\ProcessIndividualWatchlist;

class WatchlistTest extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'watchlist-test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Adds 1000 cases to the individual watchlist queue for testing';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $cases = CourtCase::limit('10000')->orderBy('id', 'DESC')->get();

        foreach ($cases as $case) {
            dispatch(new \App\Jobs\ProcessIndividualWatchlist($case));
        }
    }
}

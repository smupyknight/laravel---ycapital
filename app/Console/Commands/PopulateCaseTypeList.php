<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\CaseType;
use App\CourtCase;

class PopulateCaseTypeList extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'populate:case_types';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
        set_time_limit(0);
        $array = [];
        $data['ACT'] = ['ACAT','Magistrates','Supreme'];
        $data['NSW'] = ['Coroner\'s','District','Industrial & Industrial Relations Commission','Land and Environment','Local','Supreme'];
        $data['QLD'] = ['District','Magistrates','Supreme',];
        $data['VIC'] = ['County','Magistrates','Supreme'];
        $data['WA'] = ['District','Supreme'];


        foreach ($data as $state => $court_types) {
            foreach ($court_types as $court_type) {
                if ($state == 'ACT' || $state == 'NSW') {
                    $case_types = CourtCase::select('applications.type')
                                            ->leftJoin('applications','applications.case_id','=','cases.id')
                                            ->distinct()
                                            ->where('cases.state',$state)
                                            ->where('cases.court_type',$court_type)
                                            ->where('applications.type','!=','')
                                            ->orderBy('applications.type')
                                            ->lists('applications.type');
                } else {
                    $case_types = CourtCase::select('case_type')
                                            ->distinct()
                                            ->where('state',$state)
                                            ->where('court_type',$court_type)
                                            ->where('case_type','!=','')
                                            ->orderBy('case_type')
                                            ->lists('case_type');
                }
                $array[$state.' '.$court_type]= $case_types;
            }
        }

        $case_types = CourtCase::select('applications.type')
                                    ->leftJoin('applications','applications.case_id','=','cases.id')
                                    ->distinct()
                                    ->where('cases.court_type','Federal')
                                    ->where('applications.type','!=','')
                                    ->orderBy('applications.type')
                                    ->lists('applications.type');

        $array['Federal'] = $case_types;

        foreach ($array as $scraper => $case_types) {
            foreach ($case_types as $data) {
                $case_type = CaseType::firstOrNew([
                        'scraper'   => $scraper,
                        'case_type' => $data
                    ]);

                $case_type->save();
            }
        }
    }
}

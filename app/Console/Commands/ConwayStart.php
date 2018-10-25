<?php

namespace App\Console\Commands;
use App\Http\Controllers\FieldController;
use Illuminate\Console\Command;
use App\Field;
use App\Classes\FieldProcess;
class ConwayStart extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'conway:start {start} {finish?}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Game Of Life start in [start..finish] or One';

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
    public function handle(){

    }


}

<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 4/9/21
 * Time: 12:33 PM
 */

namespace App\Chat\Dispatcher\Commands\Test;


use App\Chat\Dispatcher\Factory\DispatcherFactory;
use Illuminate\Console\Command;

class TestDispatcher extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:chat:dispatcher';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Dispatcher';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }


    public function handle()
    {
        $dispatcher = DispatcherFactory::make();
        $dispatcher->dispatchBatch();
    }
}

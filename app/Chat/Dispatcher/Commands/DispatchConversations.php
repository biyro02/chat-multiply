<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 4/9/21
 * Time: 1:18 PM
 */

namespace App\Chat\Dispatcher\Commands;


use App\Chat\Dispatcher\Factory\DispatcherFactory;
use Illuminate\Console\Command;

class DispatchConversations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'conversation:dispatcher';

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
        try{
            $this->line('Dispatcher started working ' . now()->format('Y-m-d H:i:s'));
            $dispatcher = DispatcherFactory::make();
            $dispatcher->dispatchBatch();
        }catch (\Throwable $t){
            $this->line($t->getMessage());
        }
    }
}

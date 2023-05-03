<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 4/9/21
 * Time: 11:58 AM
 */

namespace App\Chat\Dispatcher\Commands\Test;

use App\Chat\Dispatcher\Repository\User\CapabilityBasedUserRepository;
use App\Chat\Models\Conversation;
use Illuminate\Console\Command;

class TestConversationDispatchAbleUsers extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:conversation:chatDispatchAbleUsers';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Chat DispatchAble Users';

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
        $repository = new CapabilityBasedUserRepository();
        $conversation = Conversation::find(3);
        echo json_encode($repository->availableUsers($conversation));
        die();
    }
}

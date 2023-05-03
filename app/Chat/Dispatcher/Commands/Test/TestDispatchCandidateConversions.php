<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 4/9/21
 * Time: 12:20 PM
 */

namespace App\Chat\Dispatcher\Commands\Test;


use App\Chat\Dispatcher\Repository\Conversation\OverlayConversationRepository;
use Illuminate\Console\Command;

class TestDispatchCandidateConversions extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:chat:dispatchCandidateConversations';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Chat Dispatch Candidate Conversations';

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
        $repository = new OverlayConversationRepository();
        echo json_encode($repository->allWaitingDispatch());
        die();
    }
}

<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 5/5/21
 * Time: 7:16 PM
 */

namespace App\Chat\Converters\Commands\Test;


use App\Chat\Models\Conversation;
use Illuminate\Console\Command;

class TestCreateTicketForChat extends  Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:conversation:ticketCreate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Create Ticket For Conversation';

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
        /**
         * @var $conversation Conversation
         */
        $conversation = Conversation::find(133);
        $conversation->createTicket();

    }
}

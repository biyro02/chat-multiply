<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 4/13/21
 * Time: 10:21 AM
 */

namespace App\Chat\Converters\Commands\Test;


use App\Chat\Converters\HtmlConverter;
use App\Chat\Events\ChatConversationEnded;
use App\Chat\Models\Conversation;
use App\Http\Models\TicketAssignAbleUser;
use App\Http\Services\ConnectWise\DocumentService;
use App\Onboarding\Services\PolymorphicTicketService;
use App\ServiceDeskDataIntegration\Services\TicketService;
use App\TicketManagement\Models\Builder\Note\BaseNoteBuilder;
use Illuminate\Console\Command;

class TestConverter extends Command
{

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'test:conversation:converter';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Chat Converter';

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
     * @throws \Spinen\ConnectWise\Clients\System\ApiException
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function handle()
    {
        /**
         * @var $conversation Conversation
         */
        $conversation = Conversation::find(174);
        $converter = new HtmlConverter(new DocumentService());
        $text = $converter->convert($conversation);
        echo $text;


        $ticket = (new TicketService())->detail($conversation->getTicketId());
        $provider = $ticket->getTicketProvider();


        $note = $provider
            ->noteBuilder($ticket->getProviderId())
            ->text($text, BaseNoteBuilder::BODY_TYPE_HTML)
            ->owner($provider->createProviderUser($ticket->getOwner()))
            ->internal()
            ->build()
            ->getData();

        $provider->addNote($ticket->getProviderId(), $note);

    }
}

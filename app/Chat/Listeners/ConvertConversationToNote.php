<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 4/13/21
 * Time: 9:26 AM
 */

namespace App\Chat\Listeners;


use App\Chat\Converters\IConverter;
use App\Chat\Events\ChatConversationEnded;
use App\ConnectWiseDataIntegration\Services\OverlayBackedConnectWiseServices\BoardService;
use App\Onboarding\Services\PolymorphicTicketService;
use App\TicketManagement\Models\Builder\NoteBuilder;

class ConvertConversationToNote
{

    /**
     * @var IConverter|null
     */
    private $converter = null;

    /**
     * @var null | PolymorphicTicketService
     */
    private $ticketService =   null;

    /**
     * @var null | BoardService
     */
    private $boardService = null;

    /**
     * ConvertConversationToNote constructor.
     * @param IConverter $converter
     * @param BoardService $boardService
     * @param PolymorphicTicketService $ticketService
     */
    public function __construct(IConverter $converter, BoardService $boardService, PolymorphicTicketService $ticketService)
    {
        $this->converter = $converter;
        $this->ticketService = $ticketService;
        $this->boardService = $boardService;
    }

    /**
     * @param ChatConversationEnded $event
     * Now Only Text Support
     */
    public function handle(ChatConversationEnded $event){
        try{

            $ticketId = null;
            $conversation = $event->getConversation();

            if($conversation->haveRelatedTicket()){
                $ticketId = $event->getConversation()->getTicketId();
            } else if($conversation->hasUser()){
                try {
                    $ticket = $conversation->createTicket();
                    $ticketId = $ticket->getId();
                } catch (\Throwable $t){
                    $ticketId = null;
                }
            }

            if($ticketId){

                $text = $this->converter->convert($event->getConversation());

                $note = NoteBuilder::forTicket($ticketId)
                    ->text($text, NoteBuilder::BODY_TYPE_HTML)
                    ->member($event->getConversation()->getUser()->getConnectWiseUserId())
                    ->external()
                    ->internal()
                    ->build();

                $this->ticketService->addNote($note->getTicketId(), $note);
            }

        }catch (\Throwable $t){

        }

    }
}

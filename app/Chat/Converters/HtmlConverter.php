<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 4/13/21
 * Time: 9:27 AM
 */

namespace App\Chat\Converters;


use App\Chat\ChatBots\BaseChatBot;
use App\Chat\Contracts\IConversation;
use App\Chat\Contracts\IMessage;
use App\Chat\Models\Attachment;
use App\Http\Services\ConnectWise\DocumentService;

class HtmlConverter implements IConverter
{

    /**
     * @var DocumentService|null
     */
    private $documentService = null;

    const NEW_LINE = '<br>';
    const CUSTOMER_TEXT = 'Customer';
    const BOT_TEXT = 'Virtual Assistant';

    /**
     * HtmlConverter constructor.
     * @param DocumentService $service
     */
    public function __construct(DocumentService $service)
    {
        $this->documentService = $service;
    }

    /**
     * @param IConversation $conversation
     * @return string
     */
    public function convert(IConversation $conversation)
    {
        $messages  = $conversation->messages()->orderBy('timestamp', 'ASC')->get();
        $html = $this->getHeader($conversation);
        /**
         * @var $message IMessage
         */
        $html .= static::NEW_LINE;
        foreach($messages as $message){
            /**
             * Attachments Allow Now
             */
            if($message->getAttachments()->isNotEmpty()){
                $parts = $this->preparePartsForAttachmentMessages($message, $conversation);
            }else{
                $parts = $this->prepareParts($message, $conversation);
            }

            foreach($parts as $part){
                $html .= $part . static::NEW_LINE;
            }
        }
        return $html;
    }

    /**
     * @param IMessage $message
     * @param IConversation $conversation
     * @return array
     */
    private function preparePartsForAttachmentMessages(IMessage $message, IConversation $conversation){

        /**
         * @var $attachment Attachment
         */
        $isBotSend = $message->isOutgoing() && $message->getSender() === BaseChatBot::BOT_NAME;

        $parts = [
            'datetime' =>  $message->getTimestamp()->format('H:i, F d'),
            'user' => $message->isIncoming() ? static::CUSTOMER_TEXT : ($isBotSend ? static::BOT_TEXT : $message->getSender()),
            'content' => ''
        ];
        /**
         * @var $attachment Attachment
         */
        foreach($message->getAttachments() as $attachment){
            $parts['content'] .= $attachment->getFileName() . static::NEW_LINE;
            try{
                $createdAttachment = $this->documentService->addDocumentToTicket($conversation->getTicketId(), $attachment->getFileName(), $attachment->getStoragePath());
                $createdAttachment = $this->documentService->detail($createdAttachment['id']);
                $documentGuid = $createdAttachment['guid'];
                $src = static::INLINE_IMAGE_PATH . $documentGuid;
                $parts['content'] .= '<img src="' . $src  . '">' . static::NEW_LINE;
            }catch (\Throwable $t){

            }
        }
        $preparedParts = [
            'datetime' => $parts['datetime'],
            'user_content' => $parts['user'] . ': ' . static::NEW_LINE . $parts['content']
        ];
        return $preparedParts;
    }

    /**
     * @param IMessage $message
     * @param IConversation $conversation
     * @return array
     */
    private function prepareParts(IMessage $message, IConversation $conversation){
        $isBotSend = $message->isOutgoing() && $message->getSender() === BaseChatBot::BOT_NAME;
        $parts =  [
            'datetime' => $message->getTimestamp()->format('H:i, F d'),
            'user' => $message->isIncoming() ? static::CUSTOMER_TEXT : ($isBotSend ? static::BOT_TEXT : $message->getSender()),
            'content' => $message->getMessage()
        ];
        $preparedParts = [
            'datetime' => $parts['datetime'],
            'user_content' => $parts['user'] . ': ' . $parts['content']
        ];
        return $preparedParts;
    }

    private function getHeader(IConversation $conversation){
        $contact = $conversation->getContactData();
        $firstName = isset($contact['firstName']) ? $contact['firstName'] : '';
        $lastName = isset($contact['lastName']) ? $contact['lastName'] : '';
        $fullName = $firstName . ' ' . $lastName;
        $text = 'Chat with agent "' . $conversation->getUser()->getFullName() . '"'
            . ' and customer "' . $fullName . '"'
            . ' on ' . $conversation->getStartDate()->format('F d, Y') . static::NEW_LINE;
        return $text;
    }
}

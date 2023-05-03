<?php

namespace App\Chat\Channels;

use App\Chat\ChatBots\BaseChatBot;
use App\Chat\Contracts\IChannel;
use App\Chat\Contracts\IConversation;
use App\Chat\Contracts\IMessage;
use App\Chat\Events\ChatConversationCreated;
use App\Chat\Events\ChatConversationEnded;
use App\Chat\Events\ChatMessageReceived;
use App\Chat\Events\ChatMessageSent;
use App\Chat\Exceptions\InvalidFileExtensionException;
use App\Chat\Exceptions\InvalidFileSizeException;
use App\Chat\Models\AppCredentials;
use App\Chat\Models\Attachment;
use App\Chat\Models\Conversation;
use App\Chat\Models\Message;
use App\Chat\Services\BaseService;
use App\Onboarding\Services\ContactService;
use App\Http\Services\ConnectWise\Conditions\Child\ChildEqual;
use App\UserCapabilities\Models\PairProduct;
use App\Chat\Exceptions\InvalidPayloadException;
use App\Chat\Exceptions\InvalidChannelException;

abstract class BaseChannel implements IChannel
{
    const SLUG_SLACK = 'slack';
    const SLUG_MSTEAMS = 'msteams';
    const SLUG_SMS = 'sms';

    /**
     * @var BaseService
     */
    protected $service;

    /**
     * @var mixed
     */
    protected $product;

    /**
     * @var mixed
     */
    protected $payload;

    /**
     * @var string
     */
    protected $storage = 'public';

    /**
     * BaseChannel constructor.
     * @param PairProduct $product
     * @param BaseService $service
     */
    public final function __construct(PairProduct $product, BaseService $service)
    {
        $this->product = $product;
        $this->service = $service;
    }

    /**
     * @param $serviceName
     * @param PairProduct $product
     * @return MicrosoftTeams|Slack|Sms
     * @throws InvalidChannelException
     */
    public static function make($serviceName, PairProduct $product)
    {
        switch ($serviceName) {
            case static::SLUG_MSTEAMS:
                return new MicrosoftTeams($product, new \App\Chat\Services\MicrosoftTeams());
            case static::SLUG_SLACK:
                return new Slack($product, new \App\Chat\Services\Slack());
            case static::SLUG_SMS:
                return new Sms($product, new \App\Chat\Services\Sms());
        }

        throw new InvalidChannelException();
    }

    abstract protected function isValidPayload() : bool;
    abstract protected function parseIncomingAttachments() : array;
    abstract protected function sendMessageToProvider(IConversation $conversation, IMessage $message);
    abstract protected function parseIncomingMessage(IMessage $message);
    abstract protected function getConversationContactIdentifier() : string;
    abstract protected function getConversationIdentifier() : string;

    /**
     * @return string
     */
    protected function getStorage()
    {
        return $this->storage;
    }

    /**
     * @return BaseService
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * @return PairProduct
     */
    public function getProduct()
    {
        return $this->product;
    }

    /**
     * @return mixed
     */
    public function getPayload()
    {
        return $this->payload;
    }

    /**
     * @param $payload
     * @return $this
     */
    protected function setPayload($payload)
    {
        $this->payload = $payload;
        return $this;
    }

    /**
     * @return IConversation
     */
    protected function transformIncomingConversation(): IConversation
    {
        return (new Conversation())
            ->setChannel($this->getChannelSlug())
            ->setProductId($this->getProduct()->getKey())
            ->setConversationIdentifier($this->getConversationIdentifier());
    }

    /**
     * @return IMessage
     */
    protected function transformIncomingMessage(): IMessage
    {
        $message = new Message();

        $message->setIncoming();
        $message->setRaw($this->getPayload());

        $this->parseIncomingMessage($message);

        $attachments = [];
        foreach ($this->parseIncomingAttachments() as $attachment) {
            /**
             * @var $attachment Attachment
             */
            if ($attachment instanceof Attachment) {
                $attachment->setStorage($this->getStorage());
                $attachments[] = $attachment;
            }
        }

        $message->setAttachments($attachments);

        return $message;
    }

    /**
     * @param Message $message
     * @param $files
     * @return Message
     */
    public function transformOutgoingAttachments(Message $message, $files)
    {
        $attachments = [];
        /**
         * @var $file \Illuminate\Http\UploadedFile
         */
        foreach ($files as $file)
        {
            $attachments[] = (new Attachment())
                ->setStorage($this->getStorage())
                ->setFileName($file->getClientOriginalName())
                ->setContentType($file->getMimeType())
                ->setContent($file->openFile()->fread($file->getSize()));
        }

        $message->setAttachments($attachments);
        return $message;
    }

    /**
     * @return AppCredentials
     */
    protected function getAppCredentials()
    {
        return $this
            ->getProduct()
            ->appCredentials()
            ->channel($this->getChannelSlug())
            ->first();
    }

    /**
     * @param IConversation $conversation
     */
    public function createConversation(IConversation $conversation)
    {
        $conversation
            ->setStatus(IConversation::STATUS_NEW)
            ->save();

        event(new ChatConversationCreated($conversation));
    }

    /**
     * @param IConversation $conversation
     * @param string $cause
     */
    public function endConversation(IConversation $conversation, $cause = IConversation::END_CAUSE_AGENT_MANUAL_END)
    {
        $conversation->setStatus(IConversation::STATUS_ENDED)->save();

        event(new ChatConversationEnded($conversation, $cause));

        $conversation->delete();
    }

    /**
     * @throws InvalidPayloadException
     */
    public function validatePayload()
    {
        if ($this->isValidPayload() === false) {
            throw new InvalidPayloadException();
        }
    }

    /**
     * @param IConversation $conversation
     * @param IMessage $message
     * @return Message|false|\Illuminate\Database\Eloquent\Model
     */
    public function sendMessage(IConversation $conversation, IMessage $message)
    {
        $message->setOutgoing();
        $message->setTimestamp(now());

        $this->sendMessageToProvider($conversation, $message);

        return $this->addMessageToConversation($conversation, $message);
    }

    /**
     * @param IConversation $conversation
     * @param IMessage $message
     * @return IMessage|Message
     */
    public function addMessageToConversation(IConversation $conversation, IMessage $message)
    {
        $channelData = $conversation->getChannelData();
        $additionalData = $conversation->getAdditionalData();

        if ($message->isIncoming()) {
            if (isset($additionalData['client_name'])) {
                $message->setSender($additionalData['client_name']);
            } else if (isset($channelData['user_name'])) {
                $message->setSender($channelData['user_name']);
            }
            $conversation->touchCustomerLastMessagedAt();
        } else if ($message->isOutgoing()) {
            if (isset($additionalData['client_name'])) {
                $message->setRecipient($additionalData['client_name']);
            } else if (isset($channelData['user_name'])) {
                $message->setRecipient($channelData['user_name']);
            }
            /**
             * Bot Must NOT update last agent message
             */
            if($message->getSender() !== BaseChatBot::BOT_NAME){
                $conversation->touchAgentLastMessagedAt();
            }
        }
        $conversation->save();

        /**
         * @var Message $message
         */
        $message = $conversation->messages()->save($message);

        if ($message->isIncoming()) {
            event(new ChatMessageReceived($message));
        } else if($message->isOutgoing()) {
            event(new ChatMessageSent($message));
        }

        return $message;
    }

    /**
     * @param IConversation $conversation
     * @param null $contactIdentifier
     * @return false|mixed|null
     */
    public function fetchConnectWiseContact(IConversation $conversation, $contactIdentifier = null)
    {
        $contactIdentifier = $contactIdentifier ?: $this->getConversationContactIdentifier();
        $cwContactData = $this->getContactFromConnectWise($contactIdentifier);

        if ($cwContactData) {
            $conversation->setContactIdentifier($contactIdentifier);
            $conversation->setContactData($cwContactData->toArray());
            return $cwContactData;
        }

        return false;
    }

    /**
     * @param $email
     * @return mixed|null
     */
    protected function getContactFromConnectWise($email)
    {
        $contact = null;
        try {

            $service = new ContactService();
            $conditions = [
                new ChildEqual('communicationItems/value', $email)
            ];
            $contact = $service->all($conditions, 1, 1)->first();
        } catch (\Throwable $t) {

        }
        return $contact;
    }

    /**
     * @param IConversation $conversation
     */
    protected function setChannelData(IConversation $conversation)
    {

    }

    /**
     * @param IConversation $conversation
     */
    protected function setAdditionalData(IConversation $conversation)
    {

    }

    /**
     * @param $payload
     * @throws InvalidPayloadException
     */
    public function handleIncomingMessage($payload)
    {
        $this->setPayload($payload);

        $this->validatePayload();

        /**
         * @var Conversation $conversation
         */
        $conversation = $this->transformIncomingConversation();

        // Check the database has the record.
        $conversation->fetch();

        if (!$conversation->isExists()) {
            // Create new Conversation
            $this->setChannelData($conversation);
            $this->setAdditionalData($conversation);
            $this->fetchConnectWiseContact($conversation);
            $this->createConversation($conversation);
        }

        try {

            /**
             * @var Message $message
             */
            $message = $this->transformIncomingMessage();

            // Add message to Conversation
            $this->addMessageToConversation($conversation, $message);

            // ChatBot
            if ($chatBot = $this->getProduct()->getChatBot()) {
                $statusCheck = in_array($conversation->getStatus(), [
                    IConversation::STATUS_NEW,
                    IConversation::STATUS_BOT
                ]);

                if ($statusCheck && $bot = $chatBot->buildChatBot($conversation, $this)) {
                    $bot->handle($message);
                }
            }

            // If bot does not exists, dispatch the chat
            if ($conversation->getStatus() === IConversation::STATUS_NEW) {
                $conversation->setStatus(IConversation::STATUS_WAITING_DISPATCH)->save();
            }

        } catch (InvalidFileSizeException | InvalidFileExtensionException $e) {

            $text = 'You can send only PNG, GIF and JPG file types.';

            if ($e instanceof InvalidFileSizeException) {
                $text = 'You cannot send files larger than 15mb.';
            }

            $message = (new Message())
                ->setSender(BaseChatBot::BOT_NAME)
                ->setMessage($text);

            $this->sendMessage($conversation, $message);
        }
    }
}

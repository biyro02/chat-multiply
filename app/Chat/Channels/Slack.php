<?php

namespace App\Chat\Channels;

use App\Chat\Contracts\IConversation;
use App\Chat\Contracts\IMessage;
use App\Chat\Exceptions\InvalidFileExtensionException;
use App\Chat\Exceptions\InvalidFileSizeException;
use App\Chat\Models\AppCredentials;
use App\Chat\Models\Attachment;
use App\Chat\Models\Conversation;
use App\Chat\Services\Slack as SlackService;
use App\Chat\WebhookEvents\Slack\WebhookEventsFactory;
use App\Http\Services\Slack\AuthorizationService;
use App\Http\Services\Slack\Models\AccessResponse;
use App\Http\Services\Slack\Models\BaseModel;
use App\Http\Services\Slack\Models\Events\Message as MessageEvent;
use App\Http\Services\Slack\Models\Events\Message;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Class Slack
 * @package App\Chat\Channels
 * @method SlackService getService()
 * @method Message getPayload()
 */
class Slack extends BaseChannel
{
    /**
     * @var null|AppCredentials
     */
    protected $appCredentials = null;

    /**
     * @return string
     */
    public function getChannelSlug() : string
    {
        return static::SLUG_SLACK;
    }

    /**
     * @param $payload
     * @return $this|Slack
     */
    protected function setPayload($payload)
    {

        if($payload instanceof BaseModel){
            $this->payload = $payload;
        } else {
            $this->payload = WebhookEventsFactory::makeModel($payload);
        }

        $this->setAppCredentials($this->payload->getTeamId());

        return $this;
    }

    /**
     * @param $teamId
     * @return $this
     */
    protected function setAppCredentials($teamId = null)
    {
        $this->appCredentials = $this
            ->getProduct()
            ->appCredentials()
            ->channel($this->getChannelSlug())
            ->teamId($teamId)
            ->first();

        return $this;
    }

    /**
     * @return AppCredentials|null
     */
    public function getAppCredentials()
    {
        return $this->appCredentials;
    }

    /**
     * @return bool
     */
    protected function isValidPayload(): bool
    {
        return $this->getPayload()->getEventType() === MessageEvent::TYPE &&
            !$this->getPayload()->getBotId();
    }

    /**
     * @param IMessage $message
     */
    protected function parseIncomingMessage(IMessage $message)
    {
        $payload = $this->getPayload();

        $text = $payload->getText();

        if (preg_match('/mailto:(.+)\|/', $text, $email)) {
            $text = $email[1];
        }

        $message
            ->setMessage($text)
            ->setTimestamp(Carbon::parse($payload->getTs()));
    }

    /**
     * @return array
     * @throws InvalidFileExtensionException
     * @throws InvalidFileSizeException
     */
    protected function parseIncomingAttachments() : array
    {
        $attachments = [];

        if (isset($this->getPayload()['event']['files']) && $files = $this->getPayload()->getFiles()) {
            $credentials = $this->getAppCredentials();

            foreach ($files as $file) {
                $fileExtension = pathinfo($file['name'], PATHINFO_EXTENSION);

                if (!preg_match('/'. preg_quote($fileExtension) .'/i', config('chat.validation.extensions'))) {
                    throw new InvalidFileExtensionException();
                }

                $fileContent = $this->getService()->downloadFile($credentials->getAccessToken(), $file['url_private_download']);

                $attachments[] = (new Attachment())
                    ->setFileName($file['name'])
                    ->setContentType($file['mimetype'])
                    ->setContent($fileContent);
            }
        }

        return $attachments;
    }

    /**
     * @param IConversation $conversation
     * @param IMessage $message
     * @return array|mixed|null
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function sendMessageToProvider(IConversation $conversation, IMessage $message)
    {
        /** @var $message \App\Chat\Models\Message */
        $this->setAppCredentials($conversation->getChannelData()['team_id']);
        if($message->getAttachments()){
            foreach ($message->getAttachments() as $attachment) {
                $this->getService()->uploadFile(
                    $this->getAppCredentials()->getAccessToken(),
                    [$conversation->getConversationIdentifier()],
                    $attachment
                );
            }
        }
        return $this->getService()
            ->sendMessage(
                $this->getAppCredentials()->getAccessToken(),
                $conversation->getConversationIdentifier(),
                $message->getMessage()
            );
    }

    /**
     * @param IConversation $conversation
     */
    protected function setChannelData(IConversation $conversation)
    {
        $client = $this
            ->getService()
            ->getConversationMember(
                $this->getAppCredentials()->getAccessToken(),
                $this->getPayload()->getUserId()
            );

        $conversation->pushChannelData([
            'team_id' => $this->getPayload()->getTeamId(),
            'user_name' => $client->getRealName()
        ]);
        $conversation->setContactIdentifier($client->getEmail());
    }

    /**
     * @return string
     */
    protected function getConversationContactIdentifier(): string
    {
        return $this
            ->getService()
            ->getConversationMember(
                $this->getAppCredentials()->getAccessToken(),
                $this->getPayload()->getUserId()
            )
            ->getEmail();
    }

    /**
     * @return string
     */
    protected function getConversationIdentifier() : string
    {
        return $this->getPayload()->getUserId();
    }

    /**
     * @param $code
     * @return AccessResponse
     */
    public function authorize($code)
    {
        return AuthorizationService::access(
            $this->setAppCredentials()->getAppCredentials(),
            $code
        );
    }
}

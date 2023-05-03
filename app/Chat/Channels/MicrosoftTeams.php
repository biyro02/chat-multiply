<?php

namespace App\Chat\Channels;

use App\Chat\Exceptions\InvalidFileExtensionException;
use App\Chat\Exceptions\InvalidFileSizeException;
use App\Chat\Models\AppCredentials;
use App\Chat\Models\Attachment;
use Carbon\Carbon;
use App\Chat\Models\Message;
use App\Chat\Contracts\IMessage;
use App\Chat\Contracts\IConversation;
use App\Chat\Services\MicrosoftTeams as MicrosoftTeamsService;

class MicrosoftTeams extends BaseChannel
{
    /**
     * @return string
     */
    public function getChannelSlug() : string
    {
        return static::SLUG_MSTEAMS;
    }

    /**
     * @return bool
     */
    protected function isValidPayload() : bool
    {
        $payload = $this->getPayload();

        return isset($payload['type']) && $payload['type'] === 'message';
    }

    /**
     * @param IConversation $conversation
     * @param IMessage $message
     * @return mixed
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    protected function sendMessageToProvider(IConversation $conversation, IMessage $message)
    {
        $endPoint = $conversation->getChannelData()['serviceUrl'];

        /**
         * @var $service MicrosoftTeamsService
         */
        $service = $this->getService();

        return $service
            ->setEndpoint($endPoint)
            ->setClientId($this->getAppCredentials()->getClientId())
            ->setClientSecret($this->getAppCredentials()->getClientSecret())
            ->sendMessage($conversation->getConversationIdentifier(), $message->getMessage(), $message->getAttachments());
    }

    /**
     * @param IMessage $message
     */
    protected function parseIncomingMessage(IMessage $message)
    {
        $payload = $this->getPayload();

        $message
            ->setMessage(isset($payload['text']) ? $payload['text'] : null)
            ->setTimestamp(Carbon::parse($payload['timestamp']));
    }

    /**
     * @return array
     * @throws InvalidFileExtensionException
     * @throws InvalidFileSizeException
     */
    protected function parseIncomingAttachments() : array
    {
        $attachments = [];

        $payload = $this->getPayload();

        if (isset($payload['attachments'])) {
            /**
             * @var $service MicrosoftTeamsService
             */
            $service = $this->getService()
                ->setClientId($this->getAppCredentials()->getClientId())
                ->setClientSecret($this->getAppCredentials()->getClientSecret());

            $files = collect($payload['attachments'])
                ->where('contentType', '!=', 'text/html');

            foreach ($files as $file) {

                $fileName = null;
                $fileUrl = isset($file['content']) ? $file['content']['downloadUrl'] : $file['contentUrl'];

                if (isset($file['name'])) {
                    $fileName = $file['name'];
                } else {
                    $path = parse_url($fileUrl, PHP_URL_PATH);
                    $fileName = basename($path);
                }

                $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);

                if (!preg_match('/'. preg_quote($fileExtension) .'/i', config('chat.validation.extensions'))) {
                    throw new InvalidFileExtensionException();
                }

                $fileContent = $service->downloadAttachment($fileUrl);

                $attachment = (new Attachment())
                    ->setFileName($fileName)
                    ->setContentType($file['contentType'])
                    ->setContent($fileContent);

                $attachments[] = $attachment;
            }
        }

        return $attachments;
    }

    /**
     * @return mixed
     */
    protected function getConversationContact()
    {
        $payload = $this->getPayload();

        $endPoint = $payload['serviceUrl'];

        /**
         * @var $service MicrosoftTeamsService
         */
        $service = $this->getService();

        return $service
            ->setEndpoint($endPoint)
            ->setClientId($this->getAppCredentials()->getClientId())
            ->setClientSecret($this->getAppCredentials()->getClientSecret())
            ->getConversationMember($payload['conversation']['id'], $payload['from']['id']);
    }

    /**
     * @return string
     */
    protected function getConversationContactIdentifier() : string
    {
        $member = $this->getConversationContact();
        return $member->userPrincipalName;
    }

    /**
     * @return string
     */
    protected function getConversationIdentifier() : string
    {
        $payload = $this->getPayload();

        return $payload['conversation']['id'];
    }

    /**
     * @param IConversation $conversation
     * @return $this|void
     */
    protected function setChannelData(IConversation $conversation)
    {
        $payload = $this->getPayload();

        $conversation->setChannelData([
            'serviceUrl' => $payload['serviceUrl'],
            'team_id' => $payload['channelData']['tenant']['id'],
            'user_name' => $payload['from']['name']
        ]);
    }

}

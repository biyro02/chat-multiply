<?php

namespace App\Chat\Channels;

use App\CallManagement\Models\NuAnswerCompany;
use App\Chat\Contracts\IConversation;
use App\Chat\Contracts\IMessage;
use App\Chat\Exceptions\InvalidFileExtensionException;
use App\Chat\Exceptions\InvalidFileSizeException;
use App\Chat\Models\Attachment;
use Carbon\Carbon;

class Sms extends BaseChannel
{

    /**
     * @return string
     */
    public function getChannelSlug() : string
    {
        return static::SLUG_SMS;
    }

    /**
     * @return \App\Chat\Services\Sms
     */
    public function getService()
    {
        return parent::getService();
    }

    /**
     * @return bool
     */
    protected function isValidPayload() : bool
    {
        $payload = $this->getPayload();
        return isset($payload['body']) && $payload['body']['type'] === 'SMS';
    }

    /**
     * @return NuAnswerCompany
     */
    private function getProductCompany()
    {
        return $this->getProduct()->getNuAnswerCompanies()->first();
    }

    /**
     * @param IConversation $conversation
     * @param IMessage $message
     * @return \RingCentral\SDK\Http\ApiResponse
     * @throws \App\Http\Exceptions\AuthenticationException
     * @throws \Exception
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     * @throws \RingCentral\SDK\Http\ApiException
     */
    protected function sendMessageToProvider(IConversation $conversation, IMessage $message)
    {
        $service = $this->getService();
        $service->loginForCompany($this->getProductCompany());

        return $service->sendMessage(
            $conversation->getChannelData()['servicePhoneNumber'],
            $conversation->getChannelData()['clientPhoneNumber'],
            $message->getMessage(),
            $message->getAttachments()
        );
    }

    /**
     * @param IMessage $message
     */
    protected function parseIncomingMessage(IMessage $message)
    {
        $payload = $this->getPayload();

        $message
            ->setMessage(isset($payload['body']['subject']) ? $payload['body']['subject'] : null)
            ->setTimestamp(Carbon::parse($payload['timestamp']));
    }

    /**
     * @return array
     * @throws InvalidFileExtensionException
     * @throws InvalidFileSizeException
     * @throws \RingCentral\SDK\Http\ApiException
     */
    protected function parseIncomingAttachments() : array
    {
        $attachments = [];
        $payload = $this->getPayload();

        if (isset($payload['body']['attachments'])) {
            $service = $this->getService();
            $service->loginForCompany($this->getProductCompany());

            $files = $payload['body']['attachments'];

            foreach ($files as $file) {

                if ($payload['body']['id'] === $file['id']) {
                    continue;
                }

                if ($file['size'] > (config('chat.validation.size') * 1024)) {
                    throw new InvalidFileSizeException();
                }

                $fileUrl = $file['uri'];
                $fileDownload = $service->downloadAttachment($fileUrl);
                $fileNameHeader = $fileDownload->response()->getHeaderLine('Content-Disposition');

                preg_match('/(inline;)?filename=(.+)/', $fileNameHeader, $fileNameParts);

                $fileName = end($fileNameParts);
                $fileContent = $fileDownload->body();
                $fileExtension = pathinfo($fileName, PATHINFO_EXTENSION);

                if (!preg_match('/'. preg_quote($fileExtension) .'/i', config('chat.validation.extensions'))) {
                    throw new InvalidFileExtensionException();
                }

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
     * @return string
     */
    protected function getConversationContactIdentifier() : string
    {
        $payload = $this->getPayload();
        return mb_substr($payload['body']['from']['phoneNumber'], 1);
    }

    /**
     * @return string
     */
    protected function getConversationIdentifier() : string
    {
        $payload = $this->getPayload();
        return $payload['body']['conversation']['id'];
    }

    /**
     * @param IConversation $conversation
     * @return $this|void
     */
    protected function setChannelData(IConversation $conversation)
    {
        $payload = $this->getPayload();

        $conversation->setChannelData([
            'servicePhoneNumber' => $payload['body']['to'][0]['phoneNumber'],
            'clientPhoneNumber' => $payload['body']['from']['phoneNumber']
        ]);
    }
}

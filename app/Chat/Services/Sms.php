<?php

namespace App\Chat\Services;

use App\CallManagement\Models\NuAnswerCompany;
use App\CallManagement\Services\RingCentral\SmsService;
use App\Chat\Exceptions\InvalidFileSizeException;
use App\Chat\Models\Attachment;
use Psr\Http\Message\ResponseInterface;
use RingCentral\SDK\Http\ApiResponse;

class Sms extends BaseService
{
    protected $service;

    /**
     * Sms constructor.
     */
    public function __construct()
    {
        $this->service = new SmsService();
    }

    /**
     * @param NuAnswerCompany $nuAnswerCompany
     * @return $this
     * @throws \Exception
     * @throws \RingCentral\SDK\Http\ApiException
     */
    public function loginForCompany(NuAnswerCompany $nuAnswerCompany)
    {
        $this->service->loginForCompany($nuAnswerCompany);
        return $this;
    }

    /**
     * @param $fromNumber
     * @param $toNumber
     * @param $message
     * @param null $attachments
     * @return ApiResponse
     * @throws \App\Http\Exceptions\AuthenticationException
     * @throws \Exception
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     * @throws \RingCentral\SDK\Http\ApiException
     */
    public function sendMessage($fromNumber, $toNumber, $message, $attachments = null)
    {
        $contents = [];
        if ($attachments) {
            foreach ($attachments as $attachment) {
                /**
                 * @var $attachment Attachment
                 */
                $contents[] = [
                    'fileName' => $attachment->getFileName(),
                    'fileContent' => $attachment->getContent()
                ];
            }
        }

        if ($contents) {
            return $this->service->sendMms($fromNumber, $toNumber, $message, $contents);
        }

        return $this->service->sendSms($fromNumber, $toNumber, $message);
    }

    /**
     * @param $url
     * @return ApiResponse
     * @throws \Exception
     * @throws \RingCentral\SDK\Http\ApiException
     */
    public function downloadAttachment($url)
    {
        $client = $this->service->getPlatform();

        $request = $client->get($url);
        return $request;
    }
}

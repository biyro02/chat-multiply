<?php

namespace App\Chat\Services;

use App\Chat\Models\Attachment;
use App\Chat\Models\Message;
use App\Http\Services\Slack\FileService;
use App\Http\Services\Slack\MessagesService;
use App\Http\Services\Slack\Models\User;
use App\Http\Services\Slack\UsersService;

class Slack extends BaseService
{
    /**
     * @param $accessToken
     * @param $userId
     * @param $message
     * @return array|mixed|null
     */
    public function sendMessage($accessToken, $userId, $message)
    {
        return MessagesService::postMessage(
            $accessToken,
            $userId,
            $message,
            true);
    }

    /**
     * @param $accessToken
     * @param $channels
     * @param Attachment $attachment
     * @return array|mixed|null
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function uploadFile($accessToken, $channels, Attachment $attachment)
    {
        return FileService::upload(
            $accessToken,
            $channels,
            $attachment->getContent(),
            $attachment->getFileName()
        );
    }

    /**
     * @param $accessToken
     * @param $fileUrl
     * @return string|null
     */
    public function downloadFile($accessToken, $fileUrl)
    {
        return FileService::download($accessToken, $fileUrl);
    }

    /**
     * @param $accessToken
     * @param $userId
     * @return User
     */
    public function getConversationMember($accessToken, $userId)
    {
        return UsersService::info($userId, $accessToken);
    }
}

<?php
/**
 * Created by PhpStorm.
 * User: bayramu
 * Date: 3/24/21
 * Time: 3:55 PM
 */

namespace App\Http\Services\Slack;

class MessagesService extends BaseSlackService
{
    protected $basePath = 'chat';

    /**
     * @param $slackToken
     * @param $channelId
     * @param $text
     * @param bool $asUser
     * @return array|mixed|null
     */
    public static function postMessage($slackToken, $channelId, $text, $asUser = true)
    {
        $postResponse = null;
        $service = new MessagesService($slackToken);

        try {
            $service->headers['Access-Type'] = 'Application/json';

            $formParams = ['channel' => $channelId,'text' => $text];
            if($asUser){
                $formParams['as_user'] = true;
            }

            $postResponse = $service->post('postMessage',
                [
                    'headers' => $service->headers,
                    'form_params' => $formParams
                ]);

        } catch (\Throwable $throwable) {
            //
        }

        return $postResponse;
    }
}

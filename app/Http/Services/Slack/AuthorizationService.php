<?php
/**
 * Created by PhpStorm.
 * User: bayramu
 * Date: 3/24/21
 * Time: 3:55 PM
 */

namespace App\Http\Services\Slack;

use App\Chat\Models\AppCredentials;
use App\Http\Services\Slack\Models\AccessResponse;

class AuthorizationService extends BaseSlackService
{
    protected $basePath = 'oauth.v2';
    protected static $baseSlack = 'https://slack.com';
    protected static $slackApp = 'https://slack.com/app_redirect?app=';

    /**
     * @param AppCredentials $app
     * @param $code
     * @return AccessResponse
     */
    public static function access(AppCredentials $app, $code)
    {
        $service = new static();
        $options = [
            'headers' => ['Accept' => 'application/json'],
            'form_params' => [
                'client_id' => $app->getClientId(),
                'client_secret' => $app->getClientSecret(),
                'code' => $code,
                'redirect_uri' => $app->getRedirectUrl(),
            ]
        ];
        $accessResponse = $service->post('access', $options);
        return new AccessResponse($accessResponse);
    }

    /**
     * @param $appId
     */
    public static function returnToApp($appId)
    {
        header('Location: ' . static::$slackApp . $appId);
        exit;
    }

    /**
     * @param $text
     */
    public static function returnToSlack($text = null)
    {
        header('Location: ' . static::$baseSlack);
        exit;
    }
}
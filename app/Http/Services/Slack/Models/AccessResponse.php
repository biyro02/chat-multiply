<?php
/**
 * Created by PhpStorm.
 * User: bayramu
 * Date: 3/24/21
 * Time: 3:59 PM
 */

namespace App\Http\Services\Slack\Models;

/**
 * Class User
 * @package App\Http\Services\Slack\Models
 * @method string getAppId()
 * @method string getScope()
 * @method string getTokenType()
 * @method string getAccessToken()
 * @method string getBotUserId()
 * @method string getIsEnterpriseInstall()
 * @method array getIncomingWebhook()
 */
class AccessResponse extends BaseModel
{
    /**
     * @var array
     */
    public $fields =
    [
        'ok' => "1",
        'app_id' => null,
        'authed_user' => [
            'id' => null,
            'scope' => "",
            'access_token' => "xoxp-blabla",
            'token_type' => "user"
        ],
        'scope' => "",
        'token_type' => "bot",
        'access_token' => "xoxb-blabla",
        'bot_user_id' => null,
        'team' => [
                'id' => null,
                'name' => null
        ],
        'enterprise' => "",
        'is_enterprise_install' => false,
        'incoming_webhook' => [
                'channel' => "#general",
                'channel_id' => null,
                'configuration_url' => null,
                'url' => null
        ]
    ];

    /**
     * @return string
     */
    public function getUserId()
    {
        return $this['authed_user']['id'];
    }

    /**
     * @return bool
     */
    public function isOk()
    {
        return $this['ok']
            || $this['ok'] === 1
            || $this['ok'] === '1'
            || $this['ok'] === 'true'
            || $this['ok'] === true;
    }

    /**
     * @return string
     */
    public function getUserScopes()
    {
        return $this['authed_user']['scopes'];
    }

    /**
     * @return string
     */
    public function getUserAccessToken()
    {
        return $this['authed_user']['access_token'];
    }

    /**
     * @return string
     */
    public function getTeamName()
    {
        return $this['team']['name'];
    }

    /**
     * @return mixed
     */
    public function getTeamId()
    {
        return $this['team']['id'];
    }
}
<?php
/**
 * Created by PhpStorm.
 * User: bayramu
 * Date: 3/24/21
 * Time: 4:59 PM
 */

namespace App\Http\Services\Slack\Models;

/**
 * Class Event
 * @package App\Http\Services\Slack\Models
 * @method string getToken()
 * @method string getTeamId()
 * @method string getApiAppId()
 * @method array getEvent()
 * @method string getEventId()
 * @method string getEventTime()
 * @method string getIsBot()
 * @method string getIsIm()
 * @method string getBotId()
 * @method bool isExtSharedChannel() is_ext_shared_channel
 */
class Event extends BaseModel
{
    protected $responseKey = false;

    public $fields = [
        "token" => "",
        "team_id" => "",
        "api_app_id" => "",
        "event" => [],
        "type" => "event_callback",
        "event_id" => "",
        "event_time" => 0,
        "authorizations" => [
            [
             "enterprise_id" => null,
             "team_id" => "",
             "user_id" => "",
             "is_bot" => false,
             "is_enterprise_install" => false
            ]
        ],
        "is_ext_shared_channel" => false,
        "authed_users" => []
    ];

    /**
     * @return array
     */
    public function getFields()
    {
        $this->fields['user'] = $this->getRawData()['event']['user'];
        if(is_array($this->getRawData()['event']['user'])){
            $this->fields['user'] = (new User())->getFields();
        }
        return $this->fields;
    }


    /**
     * @return mixed|null
     */
    public function getAuthedUserIds()
    {
        return @$this->authed_users;
    }

    /**
     * @return mixed
     */
    public function getEventType()
    {
        return $this->getEventData('type');
    }

    /**
     * @return mixed
     */
    public function getAuthedUserId()
    {
        return $this['authorizations']['user_id'];
    }

    /**
     * @param $offset
     * @return mixed
     */
    protected function getEventData($offset)
    {
        return $this['event'][$offset];
    }
}

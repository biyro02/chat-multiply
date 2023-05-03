<?php
/**
 * Created by PhpStorm.
 * User: bayramu
 * Date: 3/24/21
 * Time: 4:59 PM
 */

namespace App\Http\Services\Slack\Models\Events;

use App\Http\Services\Slack\Models\Event;

/**
 * Class Event
 * @package App\Http\Services\Slack\Models
 */
class Message extends Event
{
    const TYPE = 'message';

    public $eventTypeFields = [
        "type" => self::TYPE,
        "channel" => null,
        "user" => null,
        "text" => "",
        "files" => [],
        "ts" => "",
        "event_ts" => "",
        "channel_type" => "im",
    ];

    /**
     * @return array
     */
    public function getFields()
    {
        $this->fields['event'] = $this->eventTypeFields;
        return $this->fields;
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
    public function getChannelId()
    {
        return $this->getEventData('channel');
    }

    /**
     * @return mixed
     */
    public function getText()
    {
        return $this->getEventData('text');
    }

    /**
     * @return mixed
     */
    public function getFiles()
    {
        return $this->getEventData('files');
    }

    /**
     * @return mixed
     */
    public function getTs()
    {
        return $this->getEventData('ts');
    }

    /**
     * @return mixed
     */
    public function getEventTs()
    {
        return $this->getEventData('event_ts');
    }

    /**
     * @return mixed
     */
    public function getChannelType()
    {
        return $this->getEventData('channel_type');
    }

    /**
     * @return mixed
     */
    public function getUserId()
    {
        return $this->getEventData('user');
    }
}

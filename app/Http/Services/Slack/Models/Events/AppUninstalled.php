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
 * @method
 */
class AppUninstalled extends Event
{
    const TYPE = 'app_uninstalled';
    public $eventTypeFields = [
        'type' => self::TYPE
    ];

    /**
     * @return array
     */
    public function getFields()
    {
        $this->fields['event'] = $this->eventTypeFields;
        return $this->fields;
    }
}
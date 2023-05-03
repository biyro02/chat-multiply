<?php
/**
 * Created by PhpStorm.
 * User: bayramu
 * Date: 4/3/21
 * Time: 5:03 AM
 */

namespace App\Chat\WebhookEvents\Slack;

use App\Http\Services\Slack\Models\Event;
use App\Http\Services\Slack\Models\Events\AppMention;
use App\Http\Services\Slack\Models\Events\AppUninstalled;
use App\Http\Services\Slack\Models\Events\Message;
use App\Http\Services\Slack\Models\Events\UserChanged;
use App\Http\Services\Slack\Models\RedirectCode;

class WebhookEventsFactory
{
    /**
     * @param array $eventArr
     * @return Event|AppMention|AppUninstalled|Message|UserChanged
     */
    public static function makeModel(array $eventArr)
    {
        if(isset($eventArr['event']['type'])){
            switch ($eventArr['event']['type']){
                case Message::TYPE :
                    $model = new Message($eventArr);
                    break;
                case AppMention::TYPE :
                    $model = new AppMention($eventArr);
                    break;
                case AppUninstalled::TYPE:
                    $model = new AppUninstalled($eventArr);
                    break;
                case UserChanged::TYPE:
                    $model = new UserChanged($eventArr);
                    break;
                default:
                    $model = new Event($eventArr);
                    break;
            }
        } else {
            if(isset($eventArr['code'])){
                $model = new RedirectCode($eventArr);
            } else {
                $model = new Event();
            }
        }

        return $model;
    }
}
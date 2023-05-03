<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 4/11/20
 * Time: 7:36 PM
 */

namespace App\Http\Services\Outlook;


use App\Http\Models\NusalUser;
use App\Http\Models\Outlook\OutlookEventModel;
use App\Http\Services\OutlookService;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use Microsoft\Graph\Exception\GraphException;
use Microsoft\Graph\Graph;

class OutlookServiceForUser
{
    /**
     * @var NusalUser|null
     */
    private $user = null;

    /**
     * OutlookServiceForUser constructor.
     * @param NusalUser $user
     */
    public function __construct(NusalUser $user)
    {
        $this->user = $user;
    }

    /**
     * @param array $params
     * @return array|mixed
     * @throws IdentityProviderException
     * @throws GraphException
     */
    public function events($params = []){

        OutlookService::checkAndUpdateUserToken($this->user);

        $graph = new Graph();
        $graph->setAccessToken($this->user->oauth_token);

        $getEventsUrl = '/me/events?'.http_build_query($params);

        $events = $graph
            ->createRequest('GET', $getEventsUrl)
            ->setReturnType(OutlookEventModel::class)
            ->execute();

        /* there is an array object ambiguity, fix */
        if(!is_array($events)){
            $events = [];
        }

        return collect($events);
    }
}

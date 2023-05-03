<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 1/26/21
 * Time: 10:15 AM
 */

namespace App\Http\Services\Outlook;


use App\Http\Models\Outlook\OutlookGroupServiceModel;
use App\Http\Models\Outlook\OutlookUserServiceModel;
use App\Http\Services\OutlookService;
use App\MultiTenancy\UserMigrator\OutlookToTenantMigration\UserProviderContract;
use Illuminate\Support\Collection;
use League\OAuth2\Client\Provider\Exception\IdentityProviderException;
use League\OAuth2\Client\Token\AccessTokenInterface;
use Microsoft\Graph\Exception\GraphException;
use Microsoft\Graph\Graph;
use Microsoft\Graph\Http\GraphResponse;

class OutlookServiceForAdmin implements UserProviderContract
{

    const MAX_PAGE_SIZE = 500;
    const DEFAULT_PAGE_SIZE = 25;

    /**
     * @var null
     */
    protected $tenant = null;

    /**
     * @var array
     */
    protected $defaultPostParams = [];

    /**
     * @var null | AccessTokenInterface
     */
    private $token = null;


    /**
     * OutlookServiceForAdmin constructor.
     * @param null $tenant
     * @throws IdentityProviderException
     */
    public function __construct($tenant = null)
    {
        $this->tenant = $tenant;
        $this->getAccessToken();
    }

    /**
     * @return mixed
     */
    public function getTenantId(){
        return OutlookService::getTenantId($this->tenant);
    }

    /**
     * @param $groupId
     * @param int $pageSize
     * @return mixed | GraphResponse
     * @throws IdentityProviderException
     * @throws GraphException
     */
    public function groupUsers($groupId, int $pageSize = 500){

        $graph = new Graph();
        $graph->setAccessToken($this->getAccessToken());

        $params = [
            '$top' => $pageSize,
            '$select' => OutlookUserServiceModel::OUTLOOK_REQUEST_SELECT_FIELDS
        ];

        $endpoint = '/groups/' . $groupId . '/members?' . $this->prepareQueryParams($params);
        return $graph
            ->createRequest('GET', $endpoint)
            ->execute();
    }

    /**
     * @param $userId
     * @return mixed | OutlookUserServiceModel
     * @throws IdentityProviderException
     * @throws GraphException
     */
    public function userManager($userId){
        $user = $this->user($userId);
        if($user && $user->getManager()){
            return $user->getManager();
        }
        return null;
    }


    /**
     * @param $userId
     * @return mixed
     * @throws IdentityProviderException
     * @throws GraphException
     */
    public function userGroups($userId){

        $graph = new Graph();
        $graph->setAccessToken($this->getAccessToken());

        $params = [
            '$select' =>  OutlookUserServiceModel::OUTLOOK_REQUEST_SELECT_FIELDS,
            '$expand' => 'memberOf($select=id,displayName,description)'
        ];

        $endpoint = '/users/' . $userId . '?' . $this->prepareQueryParams($params);

        return $graph
            ->createRequest('GET', $endpoint)
            ->setReturnType(OutlookUserServiceModel::class)
            ->execute();
    }


    /**
     * @param $groupId
     * @param null $url
     * @return GraphResponse|mixed
     * @throws IdentityProviderException
     * @throws GraphException
     */
    public function groupUsersWithPreparedUrl($groupId, $url = null){

        if(!$url){
            return $this->groupUsers($groupId, static::MAX_PAGE_SIZE);
        }

        $graph = new Graph();
        $graph->setAccessToken($this->getAccessToken());

        return $graph
            ->createRequest('GET', $url)
            ->execute();
    }

    /**
     * @param int $pageSize
     * @return mixed
     * @throws IdentityProviderException
     * @throws GraphException
     */
    public function users(int $pageSize = 500){

        $graph = new Graph();
        $graph->setAccessToken($this->getAccessToken());

        $params = [
            '$top' => $pageSize,
            '$select' => OutlookUserServiceModel::OUTLOOK_REQUEST_SELECT_FIELDS,
            '$expand' => 'manager'
        ];

        $endpoint = '/users?'. $this->prepareQueryParams($params);

        return $graph
            ->createRequest('GET', $endpoint)
            ->execute();
    }

    /**
     * @param int $pageSize
     * @return mixed
     * @throws IdentityProviderException
     * @throws GraphException
     */
    public function usersWithGroups(int $pageSize = 500){

        $graph = new Graph();
        $graph->setAccessToken($this->getAccessToken());

        $params = [
            '$top' => $pageSize,
            '$select' => OutlookUserServiceModel::OUTLOOK_REQUEST_SELECT_FIELDS,
            '$expand' => 'memberOf($select=id,displayName,description)'
        ];

        $endpoint = '/users?'. $this->prepareQueryParams($params);

        return $graph
            ->createRequest('GET', $endpoint)
            ->execute();
    }

    /**
     * @param int $pageSize
     * @return Collection|mixed | GraphResponse
     * @throws IdentityProviderException
     * @throws GraphException
     */
    public function groups(int $pageSize = 500){

        $graph = new Graph();
        $graph->setAccessToken($this->getAccessToken());

        $params = [
            '$top' => $pageSize
        ];

        $endpoint = '/groups?'. $this->prepareQueryParams($params);
        return $graph
            ->createRequest('GET', $endpoint)
            ->execute();
    }

    /**
     * @param $userId
     * @return mixed | OutlookUserServiceModel
     * @throws IdentityProviderException
     * @throws GraphException
     */
    public function user($userId){

        $graph = new Graph();
        $graph->setAccessToken($this->getAccessToken());

        $params = [
            '$expand' => 'manager'
        ];

        $endpoint = '/users/' . $userId . '?'. $this->prepareQueryParams($params);

        return $graph
            ->createRequest('GET', $endpoint)
            ->setReturnType(OutlookUserServiceModel::class)
            ->execute();
    }
    /**
     * @param $url
     * @return mixed | GraphResponse
     * @throws IdentityProviderException
     * @throws GraphException
     */
    public function groupsWithPreparedUrl($url){

        $graph = new Graph();
        $graph->setAccessToken($this->getAccessToken());

        return $graph
            ->createRequest('GET', $url)
            ->execute();
    }

    /**
     * @return Collection|static
     */
    public function allGroups(){
        $url = null;
        $allGroups = collect([]);
        do{
            if($url){
                $groups = $this->groupsWithPreparedUrl($url);
            }else{
                $groups = $this->groups(500);
            }
            $url = $groups->getNextLink();
            $allGroups = $allGroups->merge(collect($groups->getResponseAsObject(OutlookGroupServiceModel::class)));
        }while($url);

        return $allGroups;
    }

    /**
     * @param $url
     * @return GraphResponse|mixed
     * @throws GraphException
     * @throws IdentityProviderException
     */
    public function usersWithPreparedUrl($url = null){

        if(!$url){
            return $this->users(static::MAX_PAGE_SIZE);
        }

        $graph = new Graph();
        $graph->setAccessToken($this->getAccessToken());

        return $graph
            ->createRequest('GET', $url)
            ->execute();
    }

    /**
     * @param null | GraphResponse $url
     * @return GraphResponse|mixed
     * @throws IdentityProviderException
     * @throws GraphException
     */
    public function usersWithGroupsPreparedUrl($url = null){

        if(!$url){
            return $this->usersWithGroups(static::MAX_PAGE_SIZE);
        }

        $graph = new Graph();
        $graph->setAccessToken($this->getAccessToken());
        return $graph
            ->createRequest('GET', $url)
            ->execute();
    }

    /**
     * @param $params
     * @return string
     */
    private function prepareQueryParams($params){
        if(isset($params['$select']) && is_array($params['$select'])){
            $params['$select'] = implode(',', $params['$select']);
        }
        return http_build_query($params);
    }

    /**
     * @throws IdentityProviderException
     */
    private function login(){

        $oauthConnectorClient = OutlookService::getConnectorClientForAdmin($this->tenant);
        /**
         * @var  $token AccessTokenInterface
         */
        $token = $oauthConnectorClient->getAccessToken('client_credentials', [
            'scope' => 'https://graph.microsoft.com/.default'
        ]);

        $this->token = $token;
    }

    /**
     * @return null
     * @throws IdentityProviderException
     */
    private function getAccessToken(){

        $renew = false;

        if(!$this->token){
            $renew = true;
        }

        if($this->token && $this->token->getExpires() <= now()->getTimestamp() + 300){
            $renew = true;
        }

        if($renew){
            $this->login();
        }

        return $this->token->getToken();
    }

}

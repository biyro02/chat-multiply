<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 1/17/20
 * Time: 3:17 PM
 */

namespace App\Http\Services;

use App\Http\Models\Group;
use App\Http\Models\NusalUser;
use GuzzleHttp\Client as HttpClient;
use GuzzleHttp\RequestOptions;
use App\Http\Models\Node\Room;

class NodeService
{
    /**
     * @var \Illuminate\Config\Repository|mixed|string
     */
    protected $baseUrl = '';

    /**
     * @var array
     */
    protected $headers =  [
        'Content-Type' => 'application/json',
        'laravel-token' => '',
        'Accept' => 'application/json'
    ];

    protected $httpClient = null;

    public function __construct($baseUrl = null)
    {
        if($baseUrl){
            $this->baseUrl = $baseUrl;
        }else{
            $this->baseUrl = config('nusd.node.server_hostname');
        }
        $this->headers['laravel-token'] = config('nusd.node.laravel_token');
        $this->httpClient = new HttpClient(['headers' => $this->headers]);
    }

    public function post($path, $body){
        try{

            $response = $this->httpClient->post($this->getFullUrl($path), [
                RequestOptions::JSON => $body
            ]);

        }catch(\Exception $e){
            $response = null;
        }
        return $response;
    }

    public function publishGlobalMessage($roomId, $messageType, $data){
        $body = [
            'roomId' => $roomId,
            'type' => $messageType,
            'data' => $data
        ];
        return $this->post('/publishGlobalMessage', ['body' => $body]);
    }

    /**
     * @param NusalUser $user
     * @param $messageType
     * @param $data
     * @return null|\Psr\Http\Message\ResponseInterface
     */
    public function publishUserMessage(NusalUser $user, $messageType, $data){
        $body = [
            'roomId' => Room::userRoom($user),
            'type' => $messageType,
            'data' => $data
        ];
        return $this->post('/publishUserMessage', ['body' => $body]);
    }

    public function publishGroupMessage(Group $group, $messageType, $data){
        $body = [
            'roomId' => Room::groupRoom($group),
            'type' => $messageType,
            'data' => $data
        ];
        return $this->post('/publishGroupMessage', ['body' => $body]);
    }

    private function getFullUrl($path){
        return $this->baseUrl . '/' . ltrim($path, '/');
    }
}

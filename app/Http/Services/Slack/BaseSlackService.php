<?php
/**
 * Created by PhpStorm.
 * User: bayramu
 * Date: 3/24/21
 * Time: 2:53 PM
 */

namespace App\Http\Services\Slack;

use GuzzleHttp\Client as HttpClient;

/**
 * Class BaseSlackService
 * @package App\Http\Services\Slack
 */
class BaseSlackService
{
    const BASE_URL = 'https://slack.com/api';

    protected $httpClient = null;
    protected $headers = [];

    protected $basePath = null;

    /**
     * BaseSlackService constructor.
     * @param null $slackToken
     */
    public function __construct($slackToken = null)
    {
        if($slackToken){
            $this->headers['Content-Type'] = 'application/x-www-form-urlencoded';
            $this->headers['Authorization'] = "Bearer " . $slackToken;
        }
        $this->httpClient = new HttpClient([
            "headers"  => $this->headers,
        ]);
    }

    /**
     * @return HttpClient|null
     */
    public function getHttpClient()
    {
        return $this->httpClient;
    }

    /**
     * @param $path
     * @param array $params
     * @return array|mixed
     */
    public function get($path, $params = [])
    {
        try{
            $route = $this->getFullUrl($path, $params);
            $response = $this->httpClient->request("GET", $route, $this->headers);
            $retArr = json_decode($response->getBody()->getContents(),true);

        } catch(\Throwable $e) {
            $retArr = [];
        }

        return $retArr;
    }

    /**
     * @param $path
     * @param array $params
     * @return array|mixed
     */
    public function post($path, $params = ['headers' => [], 'form_params' => []])
    {
        try{
            $route = $this->getFullUrl($path);
            $response = $this->httpClient->request("POST", $route, $params);
            $retArr = json_decode($response->getBody()->getContents(),true);

        } catch(\Throwable $e) {
            $retArr = [];
        }

        return $retArr;
    }

    /**
     * @param $path
     * @param array $params
     * @return string
     */
    private function getFullUrl($path, $params = [])
    {
        $conditions = "?";
        foreach ($params as $key => $value) {
            $conditions .= $key."=".$value."&";
        }

        if($this->basePath){
            $path = $this->basePath . "." . $path;
        }

        $route = ltrim($path, '/') . ltrim($conditions, '&');

        return static::BASE_URL . '/' . $route;
    }
}

<?php
/**
 * Created by PhpStorm.
 * User: bayramu
 * Date: 8/13/20
 * Time: 2:36 PM
 */

namespace App\Http\Services\Outlook;

use App\Http\Models\DepartmentJobTitleMap;
use App\Http\Exceptions\OutlookAdminException;
use App\Http\Models\Group;
use App\Http\Services\OutlookService;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;

/**
 * Burası zaman darlığından ötürü istediğim gibi olmadı
 * @TODO: ilk boşlukta adam et
 *
 * Class OutlookAdminConsent
 * @package App\Http\Services\Outlook
 */
class OutlookAdminConsent
{
    protected $token = null;
    protected $client = null;
    protected $decodedResponse = null;
    protected $nextLink = null;

    protected $tokenUrlTemplate = "https://login.microsoftonline.com/%s/oauth2/v2.0/token";
    protected $linkTemplate = 'https://graph.microsoft.com/v1.0/%s?$select=%s&$top=%d&$filter=%s';

    protected $tenant = OutlookService::TENANT_NUMSP;

    const NEXT_LINK_KEY = "@odata.nextLink";
    const MAX_TOP_COUNT = 500;

    /**
     * OutlookAdminConsent constructor.
     */
    public function __construct()
    {
        $this->client = new Client();
    }

    /**
     * @return $this
     */
    protected function setToken()
    {
        if($this->token === null){
            $url = sprintf($this->tokenUrlTemplate, OutlookService::getTenantId($this->tenant));
            $response = $this->post($url);
            $this->token = $response->getDecodedResponse()['access_token'];
        }
        return $this;
    }

    /**
     * @return $this
     */
    protected function clearToken()
    {
        $this->token = null;
        return $this;
    }

    /**
     * @return string
     */
    protected function getToken()
    {
        if(is_null($this->token)){
            $this->setToken();
        }
        return "Bearer " . $this->token;
    }

    /**
     * Options form_params default değeri token istemek için gerekli bilgiler
     *
     * @param $url
     * @param null $options
     * @return $this
     */
    public function post($url, $options = null)
    {
        if(!isset($options["form_params"]) || is_null($options["form_params"])){
            $options = [
                "form_params" => OutlookService::getAdminConsentFormParams($this->tenant)
            ];
        }
        return $this->request('POST', $url, $options);
    }

    /**
     * Defaultta headers boş olduğu sürece token gidecek
     *
     * @param $url
     * @param null $options
     * Option should include 'headers', 'body', 'Authorization' etc...
     * @return OutlookAdminConsent|mixed
     */
    public function get($url, $options = null)
    {
        if(!isset($options['headers']) || is_null($options['headers'])){
            $options['headers'] = ['Authorization' => $this->getToken()];
        }
        return $this->request('GET', $url, $options)->setNextLink();
    }

    /**
     * @param string $method
     * @param string $url
     * @param array $options
     * @return OutlookAdminConsent
     */
    public function request(string $method, string $url, array $options)
    {
        try{
            $responseRaw = $this->client->request($method, $url, $options);
        } catch (\Exception $e) {
            throw new OutlookAdminException($e->getMessage());
        } catch (\Throwable $e) {
            throw new OutlookAdminException($e->getMessage());
        }
        return $this->setDecodedResponse($responseRaw);
    }

    /**
     * @param $response
     * @return OutlookAdminConsent
     */
    public function setDecodedResponse(ResponseInterface $response)
    {
        $this->decodedResponse = json_decode($response->getBody()->getContents());
        return $this;
    }

    /**
     * @param bool $decoding
     * @return array|\stdClass
     */
    public function getDecodedResponse(bool $decoding = true)
    {
        return json_decode(json_encode($this->decodedResponse), $decoding);
    }

    /**
     * Response içinde nextLink varsa bul ve sınıfa kaydet
     * yoksa false dön
     * @return $this
     */
    protected function setNextLink()
    {
        $responseArr = $this->getDecodedResponse(true);
        $this->nextLink = $responseArr[self::NEXT_LINK_KEY] ?? false;
        return $this;
    }

    /**
     * @return string
     */
    public function hasNextLink()
    {
        return ($this->nextLink);
    }

    /**
     * @return null
     */
    public function getNextLink()
    {
        return $this->nextLink;
    }

    /**
     * @param $route
     * @param array $params
     * $params must include:
     * select, top, filter
     *
     * @return string
     */
    public function getLink($route, $params = ["select" => "", "top" => self::MAX_TOP_COUNT, "filter" => ""])
    {
        $link = sprintf($this->linkTemplate,
            $route,
            $params["select"],
            $params["top"],
            $params["filter"]);
        return $link;
    }

    /**
     * @param null $arrayFieldsMap
     * @return array
     */
    public function valuesToArray($arrayFieldsMap = null)
    {
        $valArr = [];
        $values = $this->getDecodedResponse(true)["value"];
        foreach ($values as $val) {

            $added = [];
            /** @var DepartmentJobTitleMap $departmentAndJobTitleMap */
            foreach ($val as $k => $v) {
                $dbKey = array_search($k, $arrayFieldsMap);
                $added[$dbKey] = $v;
            }
            $valArr[] = $added;
        }
        return $valArr;
    }
}

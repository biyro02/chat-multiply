<?php

namespace App\Chat\Services;

use App\Chat\Exceptions\InvalidFileSizeException;
use App\Chat\Models\Attachment;
use GuzzleHttp\Client;
use Psr\Http\Message\ResponseInterface;

class MicrosoftTeams extends BaseService
{
    /**
     * @var Client
     */
    protected $client;

    /**
     * @var null
     */
    protected $token = null;

    /**
     * @var string
     */
    protected $endpoint = 'https://smba.trafficmanager.net/apis';

    /**
     * MicrosoftTeams constructor.
     */
    public function __construct()
    {
        $this->client = new Client();
    }

    /**
     * @param $url
     * @return $this
     */
    public function setEndpoint($url)
    {
        $this->endpoint = $url;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getAccessToken()
    {
        if($this->token === null){
            $request = $this->client->post('https://login.microsoftonline.com/botframework.com/oauth2/v2.0/token', [
                'form_params' => [
                    'client_id' => $this->getClientId(),
                    'client_secret' => $this->getClientSecret(),
                    'grant_type' => 'client_credentials',
                    'scope' => 'https://api.botframework.com/.default'
                ],
            ]);

            $response = $request->getBody()->getContents();
            $token = json_decode($response);

            $this->token = $token->access_token;
        }

        return $this->token;
    }

    /**
     * @param $conversationId
     * @param $message
     * @param null $attachments
     * @return mixed
     * @throws \Illuminate\Contracts\Filesystem\FileNotFoundException
     */
    public function sendMessage($conversationId, $message, $attachments = null)
    {
        $postParams = [
            'type' => 'message',
            'text' => nl2br($message)
        ];

        if ($attachments) {
            $uploaded = [];

            foreach ($attachments as $attachment) {
                /**
                 * @var $attachment Attachment
                 */
                $uploaded[] = [
                    'name' => $attachment->getFileName(),
                    'contentType' => $attachment->getContentType(),
                    'contentUrl' => 'data:'. $attachment->getContentType() .';base64,'. base64_encode($attachment->getContent()),
                ];
            }

            $postParams['attachments'] = $uploaded;
        }

        $request = $this->client->post(rtrim($this->endpoint, '/') . '/v3/conversations/' . urlencode($conversationId) . '/activities', [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer ' . $this->getAccessToken()
            ],
            'json' => $postParams
        ]);

        $response = $request->getBody()->getContents();

        return json_decode($response);
    }

    /**
     * @param $conversationId
     * @param $clientId
     * @return mixed
     */
    public function getConversationMember($conversationId, $clientId)
    {
        $request = $this->client->get(rtrim($this->endpoint, '/') .'/v3/conversations/'. urlencode($conversationId) .'/members/'. urlencode($clientId), [
            'headers' => [
                'Content-Type' => 'application/json',
                'Authorization' => 'Bearer '. $this->getAccessToken()
            ]
        ]);

        $response = $request->getBody()->getContents();

        return json_decode($response);
    }

    /**
     * @param $url
     * @return string
     * @throws \GuzzleHttp\Exception\GuzzleException
     */
    public function downloadAttachment($url)
    {
        $request = $this->client->get($url, [
            'headers' => [
                'Authorization' => 'Bearer '. $this->getAccessToken()
            ],
            'on_headers' => function(ResponseInterface $response){
                if ($response->getHeaderLine('Content-Length') > (config('chat.validation.size') * 1024)) {
                    throw new InvalidFileSizeException();
                }
            }
        ]);

        return $request->getBody()->getContents();
    }
}

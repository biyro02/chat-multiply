<?php
/**
 * Created by PhpStorm.
 * User: bayramu
 * Date: 3/24/21
 * Time: 3:55 PM
 */

namespace App\Http\Services\Slack;

use App\Chat\Exceptions\InvalidFileSizeException;
use Psr\Http\Message\ResponseInterface;

class FileService extends BaseSlackService
{
    protected $basePath = 'files';

    /**
     * @param $slackToken
     * @param array $channels
     * @param null $content
     * @param null $fileName
     * @param null $initialComment
     * @param null $title
     * @return array|mixed|null
     */
    public static function upload($slackToken, $channels = [], $content = null, $fileName = null, $initialComment = null, $title = null)
    {
        $postResponse = null;
        $service = new static($slackToken);

        try {
            unset($service->headers['Content-Type']);
            $postResponse = $service->post('upload',
                [
                    'headers' => $service->headers,
                    'multipart' => [
                        [
                            'name' => 'channels',
                            'contents' => implode(',', $channels)
                        ],
                        [
                            'name' => 'file',
                            'contents' => $content,
                            'filename' => $fileName
                        ],
                        [
                            'name' => 'filename',
                            'contents' => $fileName
                        ],
                        [
                            'name' => 'initial_comment',
                            'contents' => $initialComment
                        ],
                        [
                            'name' => 'title',
                            'contents' => $title
                        ]
                    ]
                ]);

        } catch (\Throwable $throwable) {
            //
        }

        return $postResponse;
    }

    /**
     * @param $slackToken
     * @param $url
     * @return string|null
     */
    public static function download($slackToken, $url)
    {
        $fileContent = null;
        $service = new static($slackToken);

        $request = $service->getHttpClient()->get($url, [
            'on_headers' => function(ResponseInterface $response){
                if ($response->getHeaderLine('Content-Length') > (config('chat.validation.size') * 1024)) {
                    throw new InvalidFileSizeException();
                }
            }
        ]);

        $fileContent = $request->getBody()->getContents();

        return $fileContent;
    }
}

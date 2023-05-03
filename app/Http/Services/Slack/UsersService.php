<?php
/**
 * Created by PhpStorm.
 * User: bayramu
 * Date: 3/24/21
 * Time: 3:55 PM
 */

namespace App\Http\Services\Slack;

use App\Http\Services\Slack\Models\User;

class UsersService extends BaseSlackService
{
    protected $basePath = 'users';

    /**
     * @return \Illuminate\Support\Collection
     */
    public static function list()
    {
        $service = new static();
        $allUsers = $service->get('list');
        $retColl = collect();

        foreach ($allUsers as $user) {
            $retColl->push(new User($user));
        }

        return $retColl;
    }

    /**
     * @param $userId
     * @param $accessToken
     * @return User|null
     */
    public static function info($userId, $accessToken)
    {
        $user = [];
        $service = new static($accessToken);
        try {
            $user = $service->get('info', ['user' => $userId]);
        } catch (\Throwable $throwable) {
            //
        }
        return new User($user);
    }
}
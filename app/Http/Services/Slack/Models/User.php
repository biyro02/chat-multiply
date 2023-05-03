<?php
/**
 * Created by PhpStorm.
 * User: bayramu
 * Date: 3/24/21
 * Time: 3:59 PM
 */

namespace App\Http\Services\Slack\Models;

/**
 * Class User
 * @package App\Http\Services\Slack\Models
 * @method string getId()
 * @method string getTeamId()
 * @method string getName()
 * @method string getRealName()
 * @method string getTz()
 * @method string getTzLabel()
 * @method int getTzOffset()
 * @method bool getIsAdmin()
 * @method bool getIsBot()
 * @method bool getIsAppUser()
 * @method bool getIsEmailConfirmed()
 */
class User extends BaseModel
{
    protected $responseKey = 'user';

    /**
     * @var array
     */
    public $fields = [
        "id" => "",
        "team_id" => "",
        "name" => "",
        "deleted" => false,
        "real_name" => "",
        "tz" => "",
        "tz_label" => "",
        "tz_offset" => 0,
        "profile" => [
            "phone" => "",
            "email" => ""
        ],
        "is_admin" => false,
        "is_bot" => false,
        "is_app_user" => false,
        "is_email_confirmed" => false
    ];

    /**
     * @return mixed
     */
    public function getIsDeleted()
    {
        return $this->deleted;
    }

    /**
     * @return string|null
     */
    public function getPhone()
    {
        return $this['profile']['phone'] ?: null;
    }

    /**
     * @return string|null
     */
    public function getEmail()
    {
        return $this['profile']['email'] ?: null;
    }
}
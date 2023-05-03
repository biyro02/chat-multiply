<?php
/**
 * Created by PhpStorm.
 * User: bayramu
 * Date: 8/14/20
 * Time: 6:44 PM
 */

namespace App\Http\Services\Outlook;

use App\Http\Models\Outlook\OutlookUser;

class OutlookAdminUsers extends OutlookAdminConsent
{
    /**
     * Her istekte token ile uğraşmak zorunda kalınmaması adına constructa taşındı
     *
     * OutlookAdminUsers constructor.
     * @param string $tenant
     */
    public function __construct($tenant = 'nusmp')
    {
        $this->tenant = $tenant;
        parent::__construct();
        $this->setToken();
    }

    /**
     * @param null $link
     * @param array $params => select, top, filter etc ...
     * @param array $options => headers, body, form_params etc...
     * @return OutlookAdminUsers|mixed
     */
    public function users($link = null, $params = [], $options = [])
    {
        if(is_null($link))
        {
            $selectable = implode(",", OutlookUser::$selectable);
            if(empty($params)){
                $params = [
                    'select' => $selectable,
                    'top' => self::MAX_TOP_COUNT,
//                    'filter' => 'accountEnabled eq true'
                    'filter' => ''
                ];
            }
            $link = $this->getLink('users', $params);
        }
        return $this->get($link, $options);
    }

    /**
     * @param null $link
     * @param $groupId
     * @return OutlookAdminConsent|mixed
     */
    public function groupUsers($link = null, $groupId){

        if(is_null($link))
        {
            $params = [
                'select' => '',
                'top' => self::MAX_TOP_COUNT,
                'filter' => ''
            ];
            $link = $this->getLink('groups/' . $groupId . '/members', $params);
        }
        return $this->get($link, []);
    }

    /**
     * @return array
     */
    public function toArray()
    {
        $keys = OutlookUser::$selectable;
        return parent::valuesToArray($keys);
    }
}

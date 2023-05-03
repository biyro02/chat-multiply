<?php
/**
 * Created by PhpStorm.
 * User: root
 * Date: 4/13/21
 * Time: 3:06 PM
 */

namespace App\Chat\Contracts;


use Carbon\Carbon;

interface IAlertAble
{

    /**
     * @return mixed
     */
    public function getId();

    /**
     * @return mixed | Carbon
     */
    public function getAgentLastMessagedAt();


    /**
     * @param $alertTypeId
     * @return mixed
     */
    public function deleteAlertsWithTypeId($alertTypeId);


    /**
     * @return mixed
     */
    public function deleteAlerts();
}

<?php
/**
 * Created by PhpStorm.
 * User: steffen
 * Date: 29/06/14
 * Time: 16:31
 */

include_once "Device.class.php";

class DeviceManager {
    public static function registerDeviceForNotifications(User $user, $type, $notificationKey){
        $devices = $user->getDevices()->getIterator();
        foreach($devices as $device){
            if($device->getNotificationKey() == $notificationKey && $device->getType() == $type){
                return array("status" => 1, "message" => "Device is already registered", "payload" => "");
            }
        }
        Device::buildNew($user, $type, $notificationKey);
        return array("status" => 1, "message" => "Device registered", "payload" => "");
    }
} 
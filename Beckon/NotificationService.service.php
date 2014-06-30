<?php

$service = new NotificationService();

while(true){
    try{
        $i = $service->dispatchNotifications();
        echo "Dispatched {$i} notifications\n";
    }
    catch(Exception $e){
        echo "{$e}\n";
    }
    sleep(2);
}

class NotificationService{

    private static $SERVER_IP = "95.85.59.211";
    private static $USERNAME = "app";
    private static $PASSWORD = "AA2D43901A3D9F8C5730518DF92F6F0D";
    private static $DATABASE = "Beckon";
    private static $connection = 0;
    private static $query;
    private static $delievered;
    private static $passphrase = 'Tw0k1252';

    function __construct() {
        self::$query = self::getConnection()->prepare("select Notification.id, type, notificationKey, message, objectClass, object, Notification.owner _owner, (select count(*) from Notification where owner = _owner) badge from Notification inner join Device on Notification.owner = Device.owner where status = 'PENDING'");
        self::$delievered = self::getConnection()->prepare("update Notification set status = 'Delivered' where id = :id");
    }

    function dispatchNotifications(){
        self::$query->execute();
        $notifications =self::$query->fetchAll();

        $ctx = stream_context_create();
        stream_context_set_option($ctx, 'ssl', 'local_cert', 'ck.pem');
        stream_context_set_option($ctx, 'ssl', 'passphrase', self::$passphrase);

        $fp = stream_socket_client('ssl://gateway.sandbox.push.apple.com:2195', $err, $errstr, 60, STREAM_CLIENT_CONNECT|STREAM_CLIENT_PERSISTENT, $ctx);

        if (!$fp){
            throw new Exception($errstr);
        }
        self::getConnection()->beginTransaction();
        $i = 0;
        foreach($notifications as $notification){
            if($notification['type'] == "APPLEIOS"){
                $body['aps'] = array('alert' => $notification['message'], 'sound' => 'default', 'badge' => $notification['badge']);
                $body['prm'] = array("c" => $notification['objectClass'], "i" => $notification['object']);
                $payload = json_encode($body);
                $msg = chr(0) . pack('n', 32) . pack('H*', $notification['notificationKey']) . pack('n', strlen($payload)) . $payload;
                $result = fwrite($fp, $msg, strlen($msg));
                error_log($result . "\n", 3, "/var/www/html/errors.log");
                self::$delievered->execute(array("id" => $notification['id']));
            }
            $i++;
        }
        self::getConnection()->commit();
        fclose($fp);
        return $i;
    }

    protected static function getConnection(){
        if(self::$connection){
            return self::$connection;
        }
        else{
            self::$connection = new PDO("mysql:host=".self::$SERVER_IP.";dbname=".self::$DATABASE.";charset=utf8", self::$USERNAME, self::$PASSWORD);
            self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return self::$connection;
        }
    }
}
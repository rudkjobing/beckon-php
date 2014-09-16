<?php
/**
 * Created by PhpStorm.
 * User: steffen
 * Date: 05/06/14
 * Time: 13:24
 */

abstract class Persistence{

    private static $SERVER_IP = "178.62.173.25";
//    private static $SERVER_IP = "10.129.164.44";
    private static $USERNAME = "app";
    private static $PASSWORD = "AA2D43901A3D9F8C5730518DF92F6F0D";
    private static $DATABASE = "Beckon";
    protected static $connection = 0;
    private static $useCount = 0;
    private static $reuseCount = 0;
    private static $queries = array();
    private static $cache = array();
    private static $hits = 0;
    private static $misses = 0;
    private static $queryCount = 0;
    protected $dirty = false;

    public static function beginTransaction(){
        self::getConnection()->beginTransaction();
    }

    public static function commitTransaction(){
        self::getConnection()->commit();
    }

    protected static function getConnection(){
        if(self::$connection){
            self::$reuseCount++;
            return self::$connection;
        }
        else{
            self::$useCount++;
            self::$connection = new PDO("mysql:host=".self::$SERVER_IP.";dbname=".self::$DATABASE.";charset=utf8", self::$USERNAME, self::$PASSWORD);
            self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
            return self::$connection;
        }
    }

    public static function getUsage(){
        $result = "\n\nDatabase utilization:\nConnections opened: " . self::$useCount."\nConnections used: ".self::$reuseCount . " Queries: " . self::$queryCount . "\n\nQueries:\n";
        foreach(self::$queries as $query){
            $result = $result . "Time: [" . $query['Time'] . "] Query: [" . $query['Query'] . "]\n";
        }
        $result = $result . "\n\nCache hits: " . self::$hits . "\nMisses: " . self::$misses;
        return $result;
    }

    protected static function q($query){
        if(!self::$connection){
            self::$useCount++;
            self::$connection = new PDO("mysql:host=".self::$SERVER_IP.";dbname=".self::$DATABASE.";charset=utf8", self::$USERNAME, self::$PASSWORD);
            self::$connection->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        }
        self::$reuseCount++;
        self::$queryCount++;

        try{
            //array_push(self::$queries, array("Time" => 0 , "Query" => $query));
            $begin = microtime();
            $r = self::$connection->query($query);
            $end = microtime();
            array_push(self::$queries, array("Time" => round(($end - $begin), 5) , "Query" => $query));

        }
        catch(PDOException $e){
            if($e->getCode() == 23000){
                throw new Exception("Object already exists and is unique", 0, $e);
            }
            else{
                throw new Exception($e->getCode(), 0, $e);
            }
        }


        return $r;
    }

    static protected function cachePut($class, $id, &$object){
        self::$cache[$class][$id] = $object;
    }
    static protected function cacheGet($class, $id){
        //if(false){
        if(array_key_exists($class, self::$cache) && array_key_exists($id, self::$cache[$class])){
            self::$hits++;
            return self::$cache[$class][$id];
        }
        else{
            self::$misses++;
            throw new Exception("Object not cached");
        }
    }

    public abstract function delete();//Handle delete of an object or collection of objects
    public abstract function flush();//Handle changes made to an object, or the storrage of an object if it has not been persisted
    public abstract function sync();//Enrich the object with data stored somewhere

}
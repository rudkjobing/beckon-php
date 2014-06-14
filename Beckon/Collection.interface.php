<?php
/**
 * Created by PhpStorm.
 * User: steffen
 * Date: 05/06/14
 * Time: 13:49
 */

interface Collection {

    public function addItem(&$obj, $key);
    public function deleteItem($key);
    public function getItem($key);

} 
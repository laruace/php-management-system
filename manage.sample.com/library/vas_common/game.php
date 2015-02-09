<?php
defined('VAS_ROOT') || define('VAS_ROOT', dirname(__FILE__));

class Vas_Game
{
    const API_USER = 'http://game.g.pptv.com/api/rpc/user.php';

    public static function getUserRpc()
    {
        return self::getRpc(self::API_USER);
    }

    protected static function getRpc($url)
    {
        require_once 'phprpc/phprpc_client.php';
        return new PHPRPC_Client($url);
    }
}
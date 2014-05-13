<?php

//http://msdn.microsoft.com/en-us/library/ms537341%28v=vs.85%29.aspx
header('P3P: CP="IDC DSP COR CURa ADMa OUR IND PHY ONL COM STA"');

class FBSessionUtil
{
    private static $facebook;

    public static function init($facebook){
        self::$facebook = $facebook;
    }

    public static function getSessionName(){
        return 'fb_utils_'.self::$facebook->getAppID();
    }
    public static function has($name){
        return isset($_SESSION[self::getSessionName()][$name]);
    }
    public static function load($name){
        if(isset($_SESSION[self::getSessionName()][$name]))
            return $_SESSION[self::getSessionName()][$name];
        return null;
    }
    public static function save($name, $value){
        $_SESSION[self::getSessionName()][$name] = $value;
    }
    public static function clear($name){
        unset($_SESSION[self::getSessionName()][$name]);
    }
}

<?php

/**
* TransSID
*
* @author Jonas
* @version 0.1.1
* @date 2013-02-11
* 
*/

//http://php.net/manual/fr/session.idpassing.php
//http://php.net/manual/en/session.constants.php
//http://php.net/manual/fr/session.security.php
//http://php.net/manual/fr/function.ob-start.php
//http://php.net/manual/fr/ref.session.php#53809
class TransSID 
{
    public static $DEBUG = false;
    public static $TRANS_SID_NAME = 'PHPSESSID';
    public static $TRANS_SID_USED = false;
    public static $SAFARI_ONLY = true;

    public static function init()
    {
        if(self::isStarted()){
            throw new Exception('Session already started');
        }
        
        if(!self::$SAFARI_ONLY || self::isSafari())
        {
            if(!isset($_COOKIE['PHPSESSID']))
            {
                if(isset($_GET[self::$TRANS_SID_NAME]) || isset($_POST[self::$TRANS_SID_NAME])){
                    session_id($_REQUEST[self::$TRANS_SID_NAME]);
                    self::$TRANS_SID_USED = true;
                }
            }
        }

        session_start();
        
        self::TRACE('TransSID :: isSafari = '.Utils::formatBoolean(self::isSafari()));
        self::TRACE('TransSID :: isActive = '.Utils::formatBoolean(self::isActive()));
        
        if(!self::isActive()){
            self::TRACE('TransSID :: session.use_trans_sid = '.ini_get('session.use_trans_sid'));
            self::TRACE('TransSID :: session.use_only_cookies = '.ini_get('session.use_only_cookies'));
        }
        
        self::TRACE('TransSID :: isUsed = '.Utils::formatBoolean(self::isUsed()));
    }
    
    public static function getSID(){
        return session_id();
    }
    
    public static function getURL($url){
        if(strrpos($url, '?')){
            $url = $url.'&'.self::$TRANS_SID_NAME.'='.self::getSID();
        }else{
            $url = $url.'?'.self::$TRANS_SID_NAME.'='.self::getSID();
        }
        self::TRACE('TransSID :: getURL = '.$url);
        return $url;
    }

    //Todo PHP 5.4 : session_status() == PHP_SESSION_ACTIVE
    public static function isStarted(){
        return session_id() != '';
    }
    
    // To check
    public static function setActive($b){
        ini_set('session.use_trans_sid', $b);
        ini_set('session.use_only_cookies', !$b);
    }
    
    // To check
    public static function isActive(){
        return ini_get('session.use_trans_sid') == 1 && ini_get('session.use_only_cookies') == 0;
    }
    
    public static function isUsed(){
        return self::$TRANS_SID_USED;
    }
    
    public static function isSafari(){
        $u = $_SERVER['HTTP_USER_AGENT'];
        return preg_match('/Safari/', $u) && !preg_match('/Chrome/', $u);
    }
    
    public static function TRACE($s){
        if(self::$DEBUG)
            echo '<pre>DEBUG :: '.$s.'</pre>';
    }
}
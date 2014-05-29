<?php

namespace jonas;

function print_a($a){
    echo '<pre>'.print_r($a, true).'</pre>';
}

class Utils {

    static function printArray($a){
        echo '<pre>'.print_r($a, true).'</pre>';
    }

    static function formatBoolean($b){
        return $b ? 'true' : 'false';
    }
}

class Debug {

    public static $ACTIVE = false;
    public static $PATERN = 'DEBUG :: ';

    public static $message = '';

    public static function TRACE($s){
        if(self::$ACTIVE)
            self::$message .= '<pre>'.self::$PATERN.$s.'</pre>';
    }
}

class Browser {

    public static function getUserAgent(){
        if(isset($_SERVER['HTTP_USER_AGENT']))
            return $_SERVER['HTTP_USER_AGENT'];
        return '';
    }

    public static function isSafari(){
        $u = self::getUserAgent();
        return preg_match('/Safari/', $u) && !preg_match('/Chrome/', $u);
    }
}

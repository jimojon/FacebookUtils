<?php

class TabConfig 
{
    const APP_ID = '###############';
    const APP_SECRET = '#################################';
    const APP_DEFAULT_PAGE_ID = '231724386927312';

    static $PAGES_URL = array(
        '231724386927312' => 'https://www.facebook.com/positronic.fr/?sk=app_314298428681424'
    );

    static $PAGES_NAMES = array(
        '231724386927312' => 'Positronic'
    );
    
    
    static function getPageURL($id){
        if(isset(self::$PAGES_URL[$id])){
            return self::$PAGES_URL[$id];
        }
        return self::$PAGES_URL[self::APP_DEFAULT_PAGE_ID];
    }
    
    static function getPageName($id){
        if(isset(self::$PAGES_NAMES[$id])){
            return self::$PAGES_NAMES[$id];
        }
        
        return 'unknown page';
    }
}

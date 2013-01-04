<?php

/**
* FacebookUtils test
* Some utilities for use with the Facebook PHP SDK.
* https://github.com/jonasmonnier/FacebookUtils
*
* @author Jonas
* @version 0.1.6
* @date 2013-01-03
* 
*/

error_reporting(E_ALL);
ini_set("display_errors", 1);

require 'src/facebook.php'; // PHP SDK

require 'src/facebook_utils.php';
require 'tab.conf.php';

// Debug
Debug::$ACTIVE = false;

// Init Facebook PHP SDK
$facebook = new Facebook(array(
  'appId'  => APP_ID,
  'secret' => SECRET 
));

$use_session = false;

// Init FacebookUtils
$utils = new FacebookUtils($facebook); 
$utils->initSignedData($use_session);
$utils->initUser($use_session);

// Ask for publish_stream permission
$utils->setScope(array(
    FacebookPerms::publish_stream,
    FacebookPerms::email
));

// Define redirect URI for each app type we need
$utils->setAppURI(array(
    FacebookAppType::PAGE_TAB => APP_TAB_URL,
));
?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Tab demo</title>
    </head>
    <body>
        <h1>Tab demo</h1>
<?php
if($utils->isPageLiked()){
    if($utils->isAuth()){
        $user = $utils->getUserData();
        echo 'Hello '.$user['name'].', your user ID is '.$user['id'].'</br></br>';
        if(!$utils->hasPermission(FacebookPerms::publish_stream)){
            echo 'You must <a href="'.$utils->getLoginURL().'" target="_parent">allow publish</a> to play';
        }else{
            echo 'You are ready to play !';
        }
    }else{
        echo '<a href="'.$utils->getLoginURL().'" target="_parent">Play</a>';
    }
}else{
    echo 'Like to play';
}

?>
</br></br>
<h6>
<a href="https://developers.facebook.com/apps" target="_blank">Apps dev</a>
<a href="https://www.facebook.com/settings?tab=applications" target="_blank">Apps auth</a>
</h6>
    </body>
</html>

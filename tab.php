<?php

/**
* FacebookUtils test
* Some utilities for use with the Facebook PHP SDK.
* https://github.com/jonasmonnier/FacebookUtils
*
* @author Jonas
* @version 0.2.0
* @date 2013-02-11
* 
*/

error_reporting(E_ALL);
ini_set("display_errors", 1);

require 'src/CommonUtils.php';
require 'src/TransSID.php';
require 'src/FacebookUtils.php';

require 'src/facebook.php'; // PHP SDK
require 'tab.conf.php';


// Debug
Debug::$ACTIVE = FALSE;

// Session
TransSID::init(); 

// Init Facebook PHP SDK
$facebook = new Facebook(array(
  'appId'  => APP_ID,
  'secret' => APP_SECRET 
));

// Init SignedRequest
$request = new FacebookSignedRequest($facebook);
$request->clear(); // Clear session
$request->load();

// Init FacebookSession
$session = new FacebookSession($facebook); 
$session->setAppURI(APP_TAB_URL);
$session->setScope(array(
    FacebookPerms::publish_stream,
    FacebookPerms::email
));

$session->clear();  // Clear session
$session->load();

function getPageName($id){
    
    // Known pages
    $pages['231724386927312'] = 'Positronic';
    $pages['445987285443286'] = 'Havas360.dev';
    
    if(isset($pages[$id])){
        return $pages[$id];
    }
    
    return 'unknown page';
}



?>

<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.01 Transitional//EN"
"http://www.w3.org/TR/html4/loose.dtd">
<html xmlns="http://www.w3.org/1999/xhtml">
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
        <title>Tab demo</title>
    </head>
    <body>
        <h1>Tab demo on <?php echo getPageName($request->getPageID()); ?></h1>
        <p><a href="https://github.com/jonasmonnier/FacebookUtils" target="_blank">Source</a></p>
<?php
if($request->isPageLiked()){
    if($session->isAuth()){
        $user = $session->getUserData();
        echo 'Hello '.$user['name'].', your user ID is '.$user['id'].'</br>';
        
        // app_request
        try {
            $requests = $facebook->api('/me/apprequests');
            
            if(count($requests['data']) > 0){
                echo 'You have '.count($requests['data']).' pending request(s)</br>';
                foreach ($requests['data'] as $value) {
                    echo '  from '.$value['from']['name'].' : '.$value['id'].'<br/>';
                    //print_a($value);
                }
           }
        }catch (FacebookApiException $e) {
            echo $e->getMessage();
        }
        
        //deleteRequests($facebook);
        
        $score = 0;
        
        if(!$session->hasPermission(FacebookPerms::publish_stream)){
            echo 'You must <a href="'.$session->getLoginURL().'" target="_parent">allow publish</a> to play</br>';
        }else{
            
            // get score
            try {
                
                $scores = $facebook->api('/me/scores');
                $score = $scores['data'][0]['score'];
                //print_a($scores);
                echo 'Your score is '.$score.'</br>';
                
                // set new scores
                try {
                    $scores = $facebook->api('me/scores', 'POST', array(
                        'score' => ($score+1)
                    ));
                }
                catch(FacebookApiException $e){
                    echo 'Unable to save new score !<br/>'.$e->getMessage().'<br/><br/>';
                }
               
            }catch (FacebookApiException $e) {
                echo 'Unable to load score !<br/>'.$e->getMessage().'<br/><br/>';
            }
        }
    }else{
        echo '<a href="'.$session->getLoginURL().'" target="_parent">Play</a></br>';
    }
}else{
    echo 'Like to play</br>';
}

function deleteRequests($facebook){
    try {
        $requests = $facebook->api('/me/apprequests');
        foreach ($requests['data'] as $value) {
            $delete = $facebook->api($value['id'], 'DELETE');
        }
    }catch (FacebookApiException $e) {
        echo $e->getMessage();
    }
}

?>
</br></br>
<h6>
<a href="https://developers.facebook.com/apps" target="_blank">Apps dev</a>
<a href="https://www.facebook.com/settings?tab=applications" target="_blank">Apps auth</a>
</h6>
    </body>
</html>

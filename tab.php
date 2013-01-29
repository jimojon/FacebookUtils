<?php

/**
* FacebookUtils test
* Some utilities for use with the Facebook PHP SDK.
* https://github.com/jonasmonnier/FacebookUtils
*
* @author Jonas
* @version 0.1.9
* @date 2013-01-29
* 
*/

error_reporting(E_ALL);
ini_set("display_errors", 1);

require 'src/facebook.php'; // PHP SDK
require 'src/facebook_utils.php';
require 'src/utils.php';
require 'tab.conf.php';

// Debug
FacebookDebug::$ACTIVE = FALSE;

// Session
TransSID::init(); 

// Init Facebook PHP SDK
$facebook = new Facebook(array(
  'appId'  => APP_ID,
  'secret' => SECRET 
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
if($request->isPageLiked()){
    if($session->isAuth()){
        $user = $session->getUserData();
        echo 'Hello '.$user['name'].', your user ID is '.$user['id'].'</br>';
		
		// app_request
		try {
			$requests = $facebook->api('/me/apprequests');
			echo count($requests['data']).' pending request</br>';
			foreach ($requests['data'] as $value) {
				echo 'from '.$value['from']['name'].' : '.$value['id'].'<br/>';
				//print_a($value);
			}
		}catch (FacebookApiException $e) {
			echo $e->getMessage();
		}
		
		echo '</br>';
		//deleteRequests($facebook);
		
		// get scores
		$score = 0;
		
		try {
			$scores = $facebook->api('/me/scores');
			$score = $scores['data'][0]['score'];
			//print_a($scores);
			echo 'Your score is '.$score;
		}catch (FacebookApiException $e) {
			echo $e->getMessage();
		}
		
		echo '</br>';
		echo '</br>';
		
		// set scores
		try {
			$scores = $facebook->api('me/scores', 'POST', array(
				'score' => ($score+1)
			));
		}
		catch(FacebookApiException $e){
			echo $e->getMessage();
		}
		
        if(!$session->hasPermission(FacebookPerms::publish_stream)){
            echo 'You must <a href="'.$session->getLoginURL().'" target="_parent">allow publish</a> to play';
        }else{
            echo 'You are ready to play !';
        }
    }else{
        echo '<a href="'.$session->getLoginURL().'" target="_parent">Play</a>';
    }
}else{
    echo 'Like to play';
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

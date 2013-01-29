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

require 'src/utils.php';
require 'src/facebook_utils.php';
require 'src/facebook.php'; // PHP SDK
require 'index.conf.php';


// Debug
FacebookDebug::$ACTIVE = true;

// Safari fix 
TransSID::init();


// Init Facebook PHP SDK
$facebook = new Facebook(array(
  'appId'  => APP_ID,
  'secret' => SECRET
));

// page
if(isset($_REQUEST['page'])){
	$page = urlencode($_REQUEST['page']);
	$use_session = true;
}else{
	$page = 0;
	$use_session = false;
}

// Init SignedRequest
$request = new FacebookSignedRequest($facebook, $use_session);
/*
if(!$use_session)
	$request->clear(); // Clear session
*/
$request->load();


// Define app url
switch($request->getAppType())
{
	case FacebookAppType::CANEVAS :
	$appURL = 'https://apps.facebook.com/facebook-utils/';
	break;
	
	case FacebookAppType::PAGE_TAB :
	$appURL = 'https://www.facebook.com/positronic.fr/?sk=app_473357126030652';
	break;
	
	case FacebookAppType::WEBSITE :
	$appURL = 'http://positronic.fr/apps/facebook/facebook-utils/';
	break;
}

// Init FacebookSession
$session = new FacebookSession($facebook, $use_session); 
$session->setAppURI($appURL);
$session->setScope(array(
    FacebookPerms::publish_stream,
    FacebookPerms::email
));

/*
if(!$use_session)
	$session->clear();  // Clear session
*/
$session->load();

?>

<!doctype html>
<html xmlns:fb="http://www.facebook.com/2008/fbml">
  <head>
    <title>FBUtils</title>
    <style>
      body {
        font-family: 'Lucida Grande', Verdana, Arial, sans-serif;
		margin: 0 0 0 30px;
      }
	  
	  h1 {
		font-size:20px;
	  }
	  
	  h2 {
		font-size:14px;
		background-color: #cccccc;
		padding: 10px 10px 10px 10px;
		margin-top: 40px;
		width: 300px; 
	  }
	  
      h2 a {
        text-decoration: none;
        color: #3b5998;
		
      }
      h2 a:hover {
        text-decoration: underline;
      }
    </style>
  </head>
  <body>
    <h1>FacebookUtils 
<?php 
    if($page !=0) 
		echo ' > page '.$page;
?> 
    </h1>
	
	<?php
		$u = 'index.php?page='.($page+1);
		//$u = addSID($u, $sid);
	?>

	<a href="<?php echo $u ?>">Next page</a><br/><br/>
<?php
		// Show user id
		echo '<h2>User ID</h2><pre>';
		echo $session->getUserID();
		echo '</pre>';

		// Show app type
		echo '<h2>App type</h2><pre>';
		echo $request->getAppType();
		if($request->isPageTab()){
			echo ' (liked = '.($request->isPageLiked() ? 'true' : 'false').')';
		}
		echo '</pre>';
		
		// Error
		/*
		if($utils->hasError()){
			echo '<h2>Error</h2><pre>';
			$error = $utils->getError();
			echo $error->getError().'<br/>';
			echo 'Reason : '.$error->getErrorReason().'<br/>';
			echo 'Description : '.$error->getErrorDescription().'<br/>';
			echo '</pre>';
		}
		*/
		
		// Check auth
		echo '<h2>Is Auth ?</h2><pre>';
		if($session->isAuth())
			echo 'Yes<br/><a href="https://www.facebook.com/settings/?tab=applications" target="_blank">Change</a><br/>';
		else
			echo 'No<br/><a href="'.$session->getLoginURL().'" target="_parent">Change</a><br/>';
		echo '</pre>';
		
		// Check permissions
		echo '<h2>Has publish Stream Permission ?</h2><pre>';
		if($session->hasPermission(FacebookPerms::publish_stream))
			echo 'Yes<br/><a href="https://www.facebook.com/settings/?tab=applications" target="_blank">Change</a><br/>';
		else
			echo 'No<br/><a href="'.$session->getLoginURL().'" target="_parent">Change</a><br/>';
		echo '</pre>';
        
        // Check permissions
        echo '<h2>Has email Permission ?</h2><pre>';
        if($session->hasPermission(FacebookPerms::email))
            echo 'Yes<br/><a href="https://www.facebook.com/settings/?tab=applications" target="_blank">Change</a><br/>';
        else
            echo 'No<br/><a href="'.$session->getLoginURL().'" target="_parent">Change</a><br/>';
        echo '</pre>';
		
		// Show request
		echo '<h2>Request</h2><pre>';
		print_r($_REQUEST);
		echo '</pre>';
		
		// Show signed request
		echo '<h2>Signed request (source = '.$request->getSource().')</h2><pre>';
		if($request->hasData())
			print_r($request->getData());
		else
			echo 'Not defined';
		echo '</pre>';
		
		
		
		// Show user data
		echo '<h2>User data (source = '.$session->getSource().')</h2><pre>';
		if($session->isAuth())
			print_r($session->getUserData());
		else
			echo 'Needs auth';
		echo '</pre>';
		
		
		// Show user permissions
		echo '<h2>User permissions</h2><pre>';
		if($session->isAuth())
			print_r($session->getUserPermissions());
		else
			echo 'Needs auth';
		echo '</pre>';
		
		
		// Show session
		echo '<h2>Session</h2><pre>';
		print_r($_SESSION);
		echo '</pre>';
		
		
		echo '<h2>Browser</h2><pre>';
		echo $_SERVER['HTTP_USER_AGENT'] . "\n\n";
		echo 'Safari : '.(FacebookBrowserUtil::isSafari() ? 'true' : 'false'); 
		/**
		try {
			$browser = get_browser(null, true);
			print_r($browser);
		}catch(Exception $e){
			//echo $e->getMessage();
		}
		*/
		echo '</pre>'; 
		
		
		// Demos
		/*
		echo '<h2>Demos</h2><pre>';
		$apps = $utils->getAppURI();
		echo '<a href="'.$apps[FacebookAppType::WEBSITE].'" target="_blank">Website demo</a><br/>';
		echo '<a href="'.$apps[FacebookAppType::CANEVAS].'" target="_blank">Canevas demo</a><br/>';
		echo '<a href="'.$apps[FacebookAppType::PAGE_TAB].'" target="_blank">PageTab demo</a><br/>';
		echo '</pre>';
		*/
		
		?>
		
  </body>
</html>

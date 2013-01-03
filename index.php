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

// Debug
Debug::$ACTIVE = false;

// Init Facebook PHP SDK
$facebook = new Facebook(array(
  'appId'  => '473357126030652',
  'secret' => '24033b93f64921da1f13c5e11f2b9baa' 
));




// page
// if we are on firt page we do not use session
if(isset($_REQUEST['page'])){
	$page = $_REQUEST['page'];
	$use_session = true;
}else{
	$use_session = false;
}


// use_session debug
if(isset($_REQUEST['use_session']) && $_REQUEST['use_session'] == 0)
	$use_session = false;
	
	
// Init FacebookUtils
$utils = new FacebookUtils($facebook); 
$utils->initSignedData($use_session);

/**
* Si use_session = true, la lib va chercher user, user_data et user_permission en session sans faire aucun appel à l'API facebook
* Si les données ne sont pas présente en session, la lib va utiliser l'API et enregister les données en session.
*/
$utils->initUser($use_session);

// Ask for publish_stream permission
$utils->setScope(array(
	FacebookPerms::publish_stream
));

// Define redirect URI for each app type we need
$utils->setAppURI(array(
	FacebookAppType::CANEVAS => 'https://apps.facebook.com/facebook-utils/',
	FacebookAppType::PAGE_TAB => 'https://www.facebook.com/positronic.fr/?sk=app_473357126030652',
	FacebookAppType::WEBSITE => 'http://positronic.fr/apps/facebook/facebook-utils/'
));
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
    echo FacebookUtils::VERSION.' ';
    if(isset($page)) echo ' > page';
?> 
    </h1>
<?php
		// Show user id
		echo '<h2>User ID</h2><pre>';
		echo $utils->getUserID();
		echo '</pre>';

		// Show app type
		echo '<h2>App type</h2><pre>';
		echo $utils->getAppType();
		if($utils->isPageTab()){
			echo ' (liked = '.($utils->isPageLiked() ? 'true' : 'false').')';
		}
		echo '</pre>';
		
		// Error
		if($utils->hasError()){
			echo '<h2>Error</h2><pre>';
			$error = $utils->getError();
			echo $error->getError().'<br/>';
			echo 'Reason : '.$error->getErrorReason().'<br/>';
			echo 'Description : '.$error->getErrorDescription().'<br/>';
			echo '</pre>';
		}
		
		// Check auth
		echo '<h2>Is Auth ?</h2><pre>';
		if($utils->isAuth())
			echo 'Yes<br/><a href="https://www.facebook.com/settings/?tab=applications" target="_blank">Change</a><br/>';
		else
			echo 'No<br/><a href="'.$utils->getLoginURL().'" target="_parent">Change</a><br/>';
		echo '</pre>';
		
		// Check permissions
		echo '<h2>Has publish Stream Permission ?</h2><pre>';
		if($utils->hasPermission('publish_stream'))
			echo 'Yes<br/><a href="https://www.facebook.com/settings/?tab=applications" target="_blank">Change</a><br/>';
		else
			echo 'No<br/><a href="'.$utils->getLoginURL().'" target="_parent">Change</a><br/>';
		echo '</pre>';
		
		// Show request
		echo '<h2>Request</h2><pre>';
		print_r($_REQUEST);
		echo '</pre>';
		
		// Show signed request
		echo '<h2>Signed request (source = '.$utils->getSignedDataSource().')</h2><pre>';
		if($utils->hasSignedData())
			print_r($utils->getSignedData());
		else
			echo 'Not defined';
		echo '</pre>';
		
		
		
		// Show user data
		echo '<h2>User data (source = '.$utils->getUserDataSource().')</h2><pre>';
		if($utils->isAuth())
			print_r($utils->getUserData());
		else
			echo 'Needs auth';
		echo '</pre>';
		
		
		// Show user permissions
		echo '<h2>User permissions</h2><pre>';
		if($utils->isAuth())
			print_r($utils->getUserPermissions());
		else
			echo 'Needs auth';
		echo '</pre>';
		
		
		// Show session
		echo '<h2>Session</h2><pre>';
		print_r($_SESSION);
		echo '</pre>';
		
		
		echo '<h2>Browser</h2><pre>';
		echo $_SERVER['HTTP_USER_AGENT'] . "\n\n";
		echo 'Safari : '.(Browser::isSafari() ? 'true' : 'false'); 
		/**
		try {
			$browser = get_browser(null, true);
			print_r($browser);
		}catch(Exception $e){
			//echo $e->getMessage();
		}
		 * 
		 */
		echo '</pre>'; 
		
		
		// Demos
		echo '<h2>Demos</h2><pre>';
		$apps = $utils->getAppURI();
		echo '<a href="'.$apps[FacebookAppType::WEBSITE].'" target="_blank">Website demo</a><br/>';
		echo '<a href="'.$apps[FacebookAppType::CANEVAS].'" target="_blank">Canevas demo</a><br/>';
		echo '<a href="'.$apps[FacebookAppType::PAGE_TAB].'" target="_blank">PageTab demo</a><br/>';
		echo '</pre>';
		
		
		?>
		<br/><a href="index.php?page=2">Next page</a><br/><br/>
  </body>
</html>

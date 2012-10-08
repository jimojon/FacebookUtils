<?php

/**
* FacebookUtils test
* Some utilities for use with the Facebook PHP SDK.
* https://github.com/jonasmonnier/FacebookUtils
*
* @author Jonas
* @version 0.1.3
* @date 2012-09-27
* 
*/

require 'src/facebook.php'; // PHP SDK
require 'src/facebook_utils.php';

// Init Facebook PHP SDK
$facebook = new Facebook(array(
  'appId'  => '473357126030652',
  'secret' => '#########################################'
));



// Init FacebookUtils
$utils = new FacebookUtils($facebook, $use_session);
$utils->initSignedData();


// Si use_session = true, la lib va chercher user, user_data et user_permission en session sans faire aucun appel à l'API facebook
// Si les données ne sont pas présente en session, la lib va utiliser l'API et enregister les données en session.

$use_session = true;
if(isset($_REQUEST['use_session']) && $_REQUEST['use_session'] == 0)
	$use_session = false;

$utils->initUser($use_session);


// Ask for publish_stream permission
$utils->setScope(array(
	FacebookPerms::publish_stream
));

// Define redirect URI for each app type we need
$utils->setAppURI(array(
	FacebookAppType::CANEVAS => 'https://facebook.digitaltank.fr/labs/facebook-utils/',
	FacebookAppType::PAGE_TAB => 'https://facebook.digitaltank.fr/labs/facebook-utils/',
	FacebookAppType::WEBSITE => 'https://facebook.digitaltank.fr/labs/facebook-utils/'
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
    <h1>FacebookUtils <?php if(isset($_REQUEST['page'])) echo ' > page' ?> </h1>
<?php
		// Demos
		echo '<h2>Demos</h2><pre>';
		$apps = $utils->getAppURI();
		echo '<a href="'.$apps[FacebookAppType::WEBSITE].'" target="_blank">Website demo</a><br/>';
		echo '<a href="'.$apps[FacebookAppType::CANEVAS].'" target="_blank">Canevas demo</a><br/>';
		echo '<a href="'.$apps[FacebookAppType::PAGE_TAB].'" target="_blank">PageTab demo</a><br/>';
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
		print_r($utils->getSignedData());
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
		
		
		
		echo '<h2>Browser</h2><pre>';
		echo $_SERVER['HTTP_USER_AGENT'] . "\n\n";
		echo 'Safari : '.(Browser::isSafari() ? 'true' : 'false'); 
		try {
			$browser = get_browser(null, true);
			print_r($browser);
		}catch(Exception $e){
			//echo $e->getMessage();
		}
		echo '</pre>'; 
	?>
		<br/><a href="index.php?page=2">Next page</a><br/><br/>
  </body>
</html>
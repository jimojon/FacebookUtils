<?php

/*

FacebookUtils
@author Jonas
@version 0.1

Some utilities for use with the Facebook PHP SDK :
https://developers.facebook.com/docs/reference/php/

Web demo : 
http://positronic.fr/apps/facebook/facebook-utils/

App demo : 
http://apps.facebook.com/facebook-utils

Tab demo : 
https://www.facebook.com/positronic.fr/app_473357126030652

*/

require 'src/facebook.php'; // PHP SDK
require 'src/facebook_utils.php';

// Init Facebook PHP SDK
$facebook = new Facebook(array(
  'appId'  => '473357126030652',
  'secret' => '###############'
));

// Init FacebookUtils
$utils = new FacebookUtils($facebook);

// Ask for publish_stream permission
$utils->setScope(array(
	'publish_stream'
));

// Define redirect URI for each app type we need
$utils->setAppURI(array(
	FacebookAppType::APP => 'https://apps.facebook.com/facebook-utils',
	FacebookAppType::TAB => 'http://www.facebook.com/positronic.fr?sk=app_473357126030652',
	FacebookAppType::WEB => 'http://positronic.fr/apps/facebook/facebook-utils/'
));
?>

<!doctype html>
<html xmlns:fb="http://www.facebook.com/2008/fbml">
  <head>
    <title>php-sdk</title>
    <style>
      body {
        font-family: 'Lucida Grande', Verdana, Arial, sans-serif;
      }
      h3 a {
        text-decoration: none;
        color: #3b5998;
      }
      h3 a:hover {
        text-decoration: underline;
      }
    </style>
  </head>
  <body>
    <h1>FacebookUtils</h1>
	Web demo : <a href="http://positronic.fr/apps/facebook/facebook-utils/" target="_blank">http://positronic.fr/apps/facebook/facebook-utils/</a><br/>
	App demo : <a href="http://apps.facebook.com/facebook-utils" target="_blank">http://apps.facebook.com/facebook-utils</a><br/>
	Tab demo : <a href="https://www.facebook.com/positronic.fr/app_473357126030652" target="_blank">https://www.facebook.com/positronic.fr/app_473357126030652</a><br/>
	<?php
		/*
		echo '<h3>Les paramètres reçus</h3><pre>';
		print_r($_REQUEST);
		echo '</pre>';
		*/
		echo '<h3>App type</h3><pre>';
		echo $utils->getAppType();
		if($utils->isPageTab()){
			echo ' (liked = '.($utils->isPageLiked() ? 'true' : 'false').')';
		}
		echo '</pre>';
		
		echo '<h3>Is Auth ?</h3><pre>';
		if($utils->isAuth())
			echo 'Yes<br/><a href="https://www.facebook.com/settings/?tab=applications" target="_blank">Change</a><br/>';
		else
			echo 'No<br/><a href="'.$utils->getLoginURL().'" target="_parent">Change</a><br/>';
		echo '</pre>';
		
		echo '<h3>Has publish Stream Permission ?</h3><pre>';
		if($utils->hasPermission('publish_stream'))
			echo 'Yes<br/><a href="https://www.facebook.com/settings/?tab=applications" target="_blank">Change</a><br/>';
		else
			echo 'No<br/><a href="'.$utils->getLoginURL().'" target="_parent">Change</a><br/>';
		echo '</pre>';
		
		echo '<h3>Signed request</h3><pre>';
		print_r($utils->getSignedData());
		echo '</pre>';
		
		echo '<h3>User data</h3><pre>';
		if($utils->isAuth())
			print_r($utils->getUserData());
		else
			echo 'Needs auth';
		echo '</pre>';
		
		echo '<h3>User permissions</h3><pre>';
		if($utils->isAuth())
			print_r($utils->getUserPermissions());
		else
			echo 'Needs auth';
		echo '</pre>';
	?>
  </body>
</html>

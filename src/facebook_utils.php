<?php

/**
* FacebookUtils
* Some utilities for use with the Facebook PHP SDK.
* https://github.com/jonasmonnier/FacebookUtils
*
* @author Jonas
* @version 0.1.4
* @date 2012-10-08
* 
*/

class FacebookUtils
{
    var $facebook;
    var $scope;
    var $signed_data;
    var $signed_data_source;
    var $user;
	var $user_source;
    var $user_data;
    var $user_permissions;
	var $app_uri;
	var $error;
	
    
    public function __construct($facebook, $use_session = true){
        $this->facebook = $facebook;
    }
	
	public function initSignedData(/*$use_session = true*/)
	{
		$this->signed_data = $this->facebook->getSignedRequest();
		
		// Si on a une signed data fournie par le PHP SDK
		if($this->signed_data != null){
			$_SESSION['fb']['signed_data'] = $this->signed_data;
			$this->signed_data_source = 'SDK';
		}
			
		// Sinon si on en a pas en POST mais qu'on l'a en SESSION
		else if(isset($_SESSION['fb']['signed_data'])){
			$this->signed_data = $_SESSION['fb']['signed_data'];
			if($this->signed_data != null)
				$this->signed_data_source = 'SESSION';
		}
	}
	
	public function initUser($use_session = true)
	{
		// Init user using API
		if(!$use_session || !isset($_SESSION['fb']['user'])){
			$this->user = $this->facebook->getUser(); 
			$this->user_source = 'API';
			
			// We may or may not have this data based on whether the user is logged in.
			// If we have a $user id here, it means we know the user is logged into
			// Facebook, but we don't know if the access token is valid. An access
			// token is invalid if the user logged out of Facebook.
			if ($this->user) 
			{
			  try {
				// Proceed knowing you have a logged in user who's authenticated.
				$this->user_data = $this->getUserData();
				$this->user_permissions = $this->getUserPermissions();
				$_SESSION['fb']['user'] = $this->user;
				
			  } 
			  catch (FacebookApiException $e) {
				$_SESSION['fb']['user'] = null;
				$this->user = null;
				$this->user_data = null;
				$this->user_permissions = null;
			  }
			}
			
		// Init user using session
		}else{
			$this->user_source = 'SESSION';
			$this->user = $_SESSION['fb']['user'];
			$this->user_data = $_SESSION['fb']['user_data'];
			$this->user_permissions = $_SESSION['fb']['user_permissions'];
		}
	}

	public function setScope($scope){
        $this->scope = $scope;
    }
	
	public function getScope(){
		return $this->scope;
	}
    
    public function setAppURI($app_uri){
        $this->app_uri = $app_uri;
    }
	
	public function getAppURI(){
		return $this->app_uri;
	}
    
    public function getLoginURL(){
        $params = array();
        if(count($this->scope) > 0)
            $params['scope'] = join($this->scope, ',');
			
        if($this->app_uri != null && isset($this->app_uri[$this->getAppType()]))
            $params['redirect_uri'] = $this->app_uri[$this->getAppType()];
        
        return $this->facebook->getLoginUrl($params);
    }
	
    public function isAuth(){
        return $this->user != null;
    }
	
	public function hasError(){
		return $this->getError() != null;
	}
	
	public function getError(){
		if($this->error == null && isset($_REQUEST['error'])){
			$this->error = new FacebookError($_REQUEST['error']);
			if(isset($_REQUEST['error_reason']))
				$this->error->error_reason = $_REQUEST['error_reason'];
			if(isset($_REQUEST['error_description']))
				$this->error->error_description = $_REQUEST['error_description'];
		}
		return $this->error;
	}
    
    public function hasPermission($name){
        if(!isset($this->user_permissions)){ 
            return null;
        }else{
            return isset($this->user_permissions['data'][0][$name]) && $this->user_permissions['data'][0][$name] == '1';
        }
    }
	
	public function getUserID(){
		return $this->user;
	}
	
	public function getUserDataSource(){
		return $this->user_source;
	}
    
    public function getUserPermissions($update = false){
        if($this->isAuth() && ($this->user_permissions == null || $update)){
            $this->user_permissions = $this->facebook->api('/me/permissions');
			$_SESSION['fb']['user_permissions'] = $this->user_permissions; 
		}
        return $this->user_permissions; 
    }
    
    public function getUserData($update = false){
        if($this->isAuth() && ($this->user_data == null || $update)){
            $this->user_data = $this->facebook->api('/me/');
			$_SESSION['fb']['user_data'] = $this->user_data;
		}
        return $this->user_data;
    }
    
    public function hasSignedData(){
        return $this->signed_data != null;
    }
	
	public function getSignedDataSource(){
		return $this->signed_data_source;
	}

    public function getSignedData(){
        return $this->signed_data;
    }
	
    public function isPageLiked(){
		if($this->hasSignedData())
			return $this->signed_data['page']['liked'] == 1;
		return null;
    }
	
	public function hasAppData(){
		if($this->hasSignedData())
			return isset($this->signed_data['app_data']);
		return null;
	}
	
	public function getAppData(){
		if($this->hasSignedData())
			$this->signed_data['app_data'];
		return null;
	}
	
	public function isPageAmin(){
		if($this->hasSignedData())
			return $this->signed_data['page']['admin'] == 1;
		return null;
    }
	
	// potentialy bugged with signed_data stored in session
	public function getAppType(){
        if(isset($this->signed_data['page'])){
            return FacebookAppType::PAGE_TAB;
        }else if(isset($this->signed_data['user'])){
            return FacebookAppType::CANEVAS;
        }else{
            return FacebookAppType::WEBSITE;
        }
    }
	
	public function isWebsite(){
		return $this->getAppType() == FacebookAppType::WEBSITE;
	}
	
	public function isPageTab(){
		return $this->getAppType() == FacebookAppType::PAGE_TAB;
	}
	
	public function isCanevas(){
		return $this->getAppType() == FacebookAppType::CANEVAS;
	}
}

/**
* FacebookError
* Example : error_reason=user_denied&error=access_denied&error_description=The+user+denied+your+request.
*/
class FacebookError 
{
	var $error;
	var $error_reason;
	var $error_description;
	
	public function __construct($error = ''){
		$this->error = $error;
	}
	
	public function getError(){
		return $this->error;
	}
	public function getErrorReason(){
		return $this->error_reason;
	}
	public function getErrorDescription(){
		return $this->error_description;
	}
}

/**
* FacebookAppType
*/
class FacebookAppType 
{
	const WEBSITE = 'Website';
	const PAGE_TAB = 'PageTab';
	const CANEVAS = 'Canevas';
}

/**
* FacebookPerms
* From https://developers.facebook.com/docs/authentication/permissions/
* @date 2012-20-09
*/
class FacebookPerms 
{
	//User and Friends Permissions
	const user_about_me = 'user_about_me';
	const user_activities = 'user_activities';
	const user_birthday = 'user_birthday';
	const user_checkins = 'user_checkins';
	const user_education_history = 'user_education_history';
	const user_events = 'user_events';
	const user_groups = 'user_groups';
	const user_hometown = 'user_hometown';
	const user_interests = 'user_interests';
	const user_likes = 'user_likes';
	const user_location = 'user_location';
	const user_notes = 'user_notes';
	const user_photos = 'user_photos';
	const user_questions = 'user_questions';
	const user_relationships = 'user_relationships';
	const user_relationship_details = 'user_relationship_details';
	const user_religion_politics = 'user_religion_politics';
	const user_status = 'user_status';
	const user_subscriptions = 'user_subscriptions';
	const user_videos = 'user_videos';
	const user_website = 'user_website';
	const user_work_history = 'user_work_history';
	const email = 'email';
	
	const friends_about_me = 'friends_about_me';
	const friends_activities = 'friends_activities';
	const friends_birthday = 'friends_birthday';
	const friends_checkins = 'friends_checkins';
	const friends_education_history = 'friends_education_history';
	const friends_events = 'friends_events';
	const friends_groups = 'friends_groups';
	const friends_hometown = 'friends_hometown';
	const friends_interests = 'friends_interests';
	const friends_likes = 'friends_likes';
	const friends_location = 'friends_location';
	const friends_notes = 'friends_notes';
	const friends_photos = 'friends_photos';
	const friends_questions = 'friends_questions';
	const friends_relationships = 'friends_relationships';
	const friends_relationship_details = 'friends_relationship_details';
	const friends_religion_politics = 'friends_religion_politics';
	const friends_status = 'friends_status';
	const friends_subscriptions = 'friends_subscriptions';
	const friends_videos = 'friends_videos';
	const friends_website = 'friends_website';
	const friends_work_history = 'friends_work_history';
	
	//Extended permissions
	const read_friendlists = 'read_friendlists';
	const read_insights = 'read_insights';
	const read_mailbox = 'read_mailbox';
	const read_requests = 'read_requests';
	const read_stream = 'read_stream';
	const xmpp_login = 'xmpp_login';
	const ads_management = 'ads_management';
	const create_event = 'create_event';
	const manage_friendlists = 'manage_friendlists';
	const manage_notifications = 'manage_notifications';
	const user_online_presence = 'user_online_presence';
	const friends_online_presence = 'friends_online_presence';
	const publish_checkins = 'publish_checkins';
	const publish_stream = 'publish_stream';
	const rsvp_event = 'rsvp_event';
	
	//Open Graph Permissions
	const publish_actions = 'publish_actions';
	const user_actions_music = 'user_actions.music';
	const user_actions_news = 'user_actions.news';
	const user_actions_video = 'user_actions.video';
	const user_games_activity = 'user_games_activity';
	const user_actions = 'user_actions:'; //user_actions:APP_NAMESPACE
	
	const friends_actions_music = 'friends_actions.music';
	const friends_actions_news = 'friends_actions.news';
	const friends_actions_video = 'friends_actions.video';
	const friends_games_activity = 'friends_games_activity';
	const friends_actions = 'friends_actions:'; //friends_actions:APP_NAMESPACE
	
	//Page permissions
	const manage_pages = 'manage_pages';
}

class Browser {

	public static function isSafari(){
		$u = $_SERVER['HTTP_USER_AGENT'];
		return preg_match('/Safari/', $u) && !preg_match('/Chrome/', $u);
	}
}

?>
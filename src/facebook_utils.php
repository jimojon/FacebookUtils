<?php

class FacebookUtils
{
    var $facebook;
    var $scope;
    var $signed_data;
    var $user;
    var $user_data;
    var $user_permissions;
	var $app_uri;
    
    public function __construct($facebook){
        $this->facebook = $facebook;
        $this->signed_data = $facebook->getSignedRequest();
        $this->user = $facebook->getUser();
        
        // We may or may not have this data based on whether the user is logged in.
        // If we have a $user id here, it means we know the user is logged into
        // Facebook, but we don't know if the access token is valid. An access
        // token is invalid if the user logged out of Facebook.
        if ($this->user) {
          try {
            // Proceed knowing you have a logged in user who's authenticated.
			$this->user_data = $this->getUserData();
            $this->user_permissions = $this->getUserPermissions();
          } catch (FacebookApiException $e) {
            $this->user = null;
            $this->user_data = null;
            $this->user_permissions = null;
          }
        }
    }

	public function setScope($scope){
        $this->scope = $scope;
    }
    
    public function setAppURI($app_uri){
        $this->app_uri = $app_uri;
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
    
    public function hasPermission($name){
        if(!isset($this->user_permissions)){ 
            return null;
        }else{
            return isset($this->user_permissions['data'][0][$name]) && $this->user_permissions['data'][0][$name] == '1';
        }
    }
    
    public function getUserPermissions($update = false){
        if($this->isAuth() && ($this->user_permissions == null || $update))
            $this->user_permissions = $this->facebook->api('/me/permissions');
        return $this->user_permissions; 
    }
    
    public function getUserData($update = false){
        if($this->isAuth() && ($this->user_data == null || $update))
            $this->user_data = $this->facebook->api('/me/');
        return $this->user_data;
    }
    
	// Signed data
	
    public function hasSignedData(){
        return $this->signed_data != null;
    }

    public function getSignedData(){
        return $this->signed_data;
    }
	
    public function isPageLiked(){
		if($this->hasSignedData())
			return $this->signed_data['page']['liked'] == 1;
		return null;
    }
	
	public function isPageAmin(){
		if($this->hasSignedData())
			return $this->signed_data['page']['admin'] == 1;
		return null;
    }
	
	// App type
	
	 public function getAppType(){
        if(!isset($_REQUEST['signed_request'])){
            return FacebookAppType::WEB;
        }else if(isset($this->signed_data['page'])){
            return FacebookAppType::TAB;
        }else{
            return FacebookAppType::APP;
        }
    }
	
	public function isWebApp(){
		return getAppType() == FacebookAppType::WEB;
	}
	
	public function isTabApp(){
		return getAppType() == FacebookAppType::TAB;
	}
}

class FacebookAppType {
	const WEB = 'Website';
	const TAB = 'PageTab';
	const APP = 'Canevas';
}

?>
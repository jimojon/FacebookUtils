<?php

namespace jonas\facebook;

use jonas\Debug;

class FBLogin
{
    const VERSION = '0.2.0';

    private $facebook;
    private $session;
    private $scope;
    private $source;
    private $user_id;
    private $user_data;
    private $user_permissions;
    private $app_uri;
    
    public function __construct($facebook, $session = true)
    {
        Debug::TRACE('FBSession '.self::VERSION.' - '.$facebook->getAppID());
        $this->facebook = $facebook;
        $this->session = $session;
    }
    
    /**
    * Core
    */

    // Called twice to fix "Error validating access token: The session was invalidated explicitly using an API call."
    // Usefull ?
    public function load($firstCall = true)
    {
        Debug::TRACE('FBSession :: load');
    
        $this->source = '';
    
        // Try to auth user using session
        if($this->session)
        {
            Debug::TRACE('FBSession :: Try to auth user using session');
            
            if(FBSessionUtil::has('user_id') && FBSessionUtil::has('user_data') &&FBSessionUtil::has('user_permissions'))
            {
                $this->user_id = FBSessionUtil::load('user_id');
                $this->user_data = FBSessionUtil::load('user_data');
                $this->user_permissions = FBSessionUtil::load('user_permissions');
                $this->source = 'session';
            }
        }
    
        // Try to auth user using API
        if($this->user_id == null)
        {
            Debug::TRACE('FBSession :: Try to auth user using API');
            
            $this->user_id = $this->facebook->getUser(); 
            
            // We may or may not have this data based on whether the user is logged in.
            // If we have a $user id here, it means we know the user is logged into
            // Facebook, but we don't know if the access token is valid. An access
            // token is invalid if the user logged out of Facebook.
            if ($this->user_id != null) 
            {
                try {
                    // Proceed knowing you have a logged in user who's authenticated.
                    Debug::TRACE('FBSession :: Get user data to check token validity');
                    
                    $this->user_data = $this->getUserData();
                    $this->user_permissions = $this->getUserPermissions();
                    $this->save();
                    $this->source = 'API';
                  } 
                catch (FacebookApiException $e) {
                    Debug::TRACE('FBSession :: '.$e->getMessage());
                    $this->clear();

                    // Second call
                    if($firstCall)
                        $this->load(false);
                }
            }else{
                $this->clear();
            }
        } 
        
        if($this->source == '')
            Debug::TRACE('FBSession :: User not auth');
        else
            Debug::TRACE('FBSession :: User '.$this->user_data['name'].' auth from '.$this->source.', '.$this->user_id);
    }
    
    public function save()
    {
        Debug::TRACE('FBSession :: save');

        FBSessionUtil::save('user_id', $this->user_id);
        FBSessionUtil::save('user_data', $this->user_data);
        FBSessionUtil::save('user_permissions', $this->user_permissions);
    }

    public function clear()
    {
        Debug::TRACE('FBSession :: clear');
        
        $this->user_id = null;
        $this->user_data = null;
        $this->user_permissions = null;
        
        FBSessionUtil::clear('user_id');
        FBSessionUtil::clear('user_data');
        FBSessionUtil::clear('user_permissions');
    }
    
    public function getSource(){
        return $this->source;
    }
    
    /**
    * Data
    */


    /**
     * @param Array $scope
     */
    public function setScope($scope){
        $this->scope = $scope;
    }
    public function getScope(){
        return $this->scope;
    }

    /**
     * @param String $app_uri
     */
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
            
        if($this->app_uri != null)
            $params['redirect_uri'] = $this->app_uri;
        
        return $this->facebook->getLoginUrl($params);
    }
    
    public function isAuth(){
        return $this->user_id != null;
    }




    /**
     * getUserID
     * @return mixed
     */
    public function getUserID(){
        return $this->user_id;
    }

    /**
     * setUserID (Debug purpose)
     * @param $id
     */
    public function setUserID($id){
        $this->user_id = $id;
    }




    /**
     * getUserData
     * @param bool $clear
     * @return Array
     */
    public function getUserData($clear = false){
        if($this->isAuth() && ($this->user_data == null || $clear)){
            try {
                $this->user_data = $this->facebook->api('/me/');
                FBSessionUtil::save('user_data', $this->user_data);
            }catch(FacebookApiException $e){}
        }
        return $this->user_data;
    }

    /**
     * setUserData
     * @param Array $data
     *
     * Array
     * (
     *      [id] => 123456789
     *      [name] => John Doe
     *      [first_name] => Doe
     *      [last_name] => Doe
     *      [link] => https://www.facebook.com/johndoe
     *      [username] => johndoe
     *      [gender] => male
     *      [email] => john.doe@gmail.com
     *      [timezone] => 1
     *      [locale] => fr_FR
     *      [verified] => 1
     *      [updated_time] => 2099-01-01T00:00:00+0000
     * )
     */
    public function setUserData($data){
        $this->user_data = $data;
        FBSessionUtil::save('user_data', $this->user_data);
    }

    /**
     * @param bool $clear
     * @return Array
     */
    public function getUserPermissions($clear = false){
        if($this->isAuth() && ($this->user_permissions == null || $clear)){
            $this->user_permissions = $this->facebook->api('/me/permissions');
            FBSessionUtil::save('user_permissions', $this->user_permissions);
        }
        return $this->user_permissions; 
    }

    /**
     * setUserPermissions
     * @param Array $perms
     */
    public function setUserPermissions($perms)
    {
        $this->user_permissions['data'][0] = $perms;
    }

    /**
     * hasPermission
     * @param string $name
     * @return bool|null
     */
    public function hasPermission($name = ''){
        if($name == '')
            return true;

        if(!isset($this->user_permissions)){
            return null;
        }else{
            return isset($this->user_permissions['data'][0][$name]) && $this->user_permissions['data'][0][$name] == '1';
        }
    }


}


?>
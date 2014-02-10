<?php

/**
* FacebookUtils
* Some utilities for use with the Facebook PHP SDK.
* https://github.com/jonasmonnier/FacebookUtils
*
* @author Jonas
* 
*/

//http://msdn.microsoft.com/en-us/library/ms537341%28v=vs.85%29.aspx
header('P3P: CP="IDC DSP COR CURa ADMa OUR IND PHY ONL COM STA"');


class FacebookSignedRequest
{
    const VERSION = '0.2.0';

    private $data;
    private $facebook;
    private $session;
    private $source;

    public function __construct($facebook, $session = true){
        Debug::TRACE('SignedRequest '.self::VERSION.' - '.$facebook->getAppID());
        $this->facebook = $facebook;
        $this->session = $session;
        FacebookSessionUtil::init($facebook);
    }
    
    /**
    * Core
    */
    
    public function save(){
        if($this->hasData()){
            Debug::TRACE('SignedRequest :: save');
            FacebookSessionUtil::save('signed_request', $this->data);
        }
    }
    public function clear(){
        Debug::TRACE('SignedRequest :: clear');
        FacebookSessionUtil::clear('signed_request');
    }
    public function load(){
        Debug::TRACE('SignedRequest :: load');
        
        // get data from facebook
        $this->data = $this->facebook->getSignedRequest();
        
        // autosave in session
        if($this->hasData()){
            $this->source = 'SDK';
            if($this->session)
                $this->save();
        
        // check session if data is null
        }else{
            $this->data = FacebookSessionUtil::load('signed_request');
            $this->source = 'session';
        }
        
        if($this->hasData()){
            Debug::TRACE('SignedRequest :: load success from '.$this->source);
        }else{
            Debug::TRACE('SignedRequest :: load error');
        }
    }
    
    public function getData(){
        return $this->data;
    }
    
    public function hasData(){
        return $this->data != null;
    }
    
    public function getSource(){
        return $this->source;
    }
    
    /**
    * Data
    */
    
    public function getPageID(){
        return $this->data['page']['id'];
    }
    
    public function isPageLiked(){
        return $this->data['page']['liked'] == 1;
    }
    
    public function isUserAdmin(){
        return $this->data['page']['admin'] == 1;
    }
    
    public function getUserCountry(){
        return $this->data['user']['country'];
    }
    
    public function getUserLocale(){
        return $this->data['user']['locale'];
    }
    
    public function getUserID(){
        return $this->data['user_id'];
    }
    
    public function isUserAmin(){
        if($this->hasSignedData())
            return $this->signed_data['page']['admin'] == 1;
        return null;
    }
    
    public function hasAppData(){
        return isset($this->data['app_data']);
    }
    
    public function getAppData(){
        if($this->hasAppData())
            return $this->data['app_data'];
        return null;
    }
    
    // potentialy bugged with signed_data stored in session
    public function getAppType(){
        if(isset($this->data['page'])){
            return FacebookAppType::PAGE_TAB;
        }else if(isset($this->data['user'])){
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

class FacebookSession
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
        Debug::TRACE('FacebookSession '.self::VERSION.' - '.$facebook->getAppID());
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
        Debug::TRACE('FacebookSession :: load');
    
        $this->source = '';
    
        // Try to auth user using session
        if($this->session)
        {
            Debug::TRACE('FacebookSession :: Try to auth user using session');
            
            if(FacebookSessionUtil::has('user_id') && FacebookSessionUtil::has('user_data') &&FacebookSessionUtil::has('user_permissions'))
            {
                $this->user_id = FacebookSessionUtil::load('user_id');
                $this->user_data = FacebookSessionUtil::load('user_data');
                $this->user_permissions = FacebookSessionUtil::load('user_permissions');
                $this->source = 'session';
            }
        }
    
        // Try to auth user using API
        if($this->user_id == null)
        {
            Debug::TRACE('FacebookSession :: Try to auth user using API');
            
            $this->user_id = $this->facebook->getUser(); 
            
            // We may or may not have this data based on whether the user is logged in.
            // If we have a $user id here, it means we know the user is logged into
            // Facebook, but we don't know if the access token is valid. An access
            // token is invalid if the user logged out of Facebook.
            if ($this->user_id != null) 
            {
                try {
                    // Proceed knowing you have a logged in user who's authenticated.
                    Debug::TRACE('FacebookSession :: Get user data to check token validity');
                    
                    $this->user_data = $this->getUserData();
                    $this->user_permissions = $this->getUserPermissions();
                    $this->save();
                    $this->source = 'API';
                  } 
                catch (FacebookApiException $e) {
                    Debug::TRACE('FacebookSession :: '.$e->getMessage());
                    $this->clear();
                    
                    if($firstCall)
                        $this->load(false);
                }
            }else{
                $this->clear();
            }
        } 
        
        if($this->source == '')
            Debug::TRACE('FacebookSession :: User not auth');
        else
            Debug::TRACE('FacebookSession :: User auth from '.$this->source.', '.$this->user_id);
    }
    
    public function save()
    {
        Debug::TRACE('FacebookSession :: save');

        FacebookSessionUtil::save('user_id', $this->user_id);
        FacebookSessionUtil::save('user_data', $this->user_data);
        FacebookSessionUtil::save('user_permissions', $this->user_permissions);
    }

    public function clear()
    {
        Debug::TRACE('FacebookSession :: clear');
        
        $this->user_id = null;
        $this->user_data = null;
        $this->user_permissions = null;
        
        FacebookSessionUtil::clear('user_id');
        FacebookSessionUtil::clear('user_data');
        FacebookSessionUtil::clear('user_permissions');
    }
    
    public function getSource(){
        return $this->source;
    }
    
    /**
    * Data
    */
    
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
            
        if($this->app_uri != null)
            $params['redirect_uri'] = $this->app_uri;
        
        return $this->facebook->getLoginUrl($params);
    }
    
    public function isAuth(){
        return $this->user_id != null;
    }
    
    public function getUserID(){
        return $this->user_id;
    }

    public function getUserData($clear = false){
        if($this->isAuth() && ($this->user_data == null || $clear)){
            $this->user_data = $this->facebook->api('/me/');
            FacebookSessionUtil::save('user_data', $this->user_data);
        }
        return $this->user_data;
    }
    
    public function getUserPermissions($clear = false){
        if($this->isAuth() && ($this->user_permissions == null || $clear)){
            $this->user_permissions = $this->facebook->api('/me/permissions');
            FacebookSessionUtil::save('user_permissions', $this->user_permissions); 
        }
        return $this->user_permissions; 
    }
    
    public function hasPermission($name){
        if(!isset($this->user_permissions)){ 
            return null;
        }else{
            return isset($this->user_permissions['data'][0][$name]) && $this->user_permissions['data'][0][$name] == '1';
        }
    }
}

/**
* FacebookError
* Example : error_reason=user_denied&error=access_denied&error_description=The+user+denied+your+request.
*/
class FacebookError 
{
    public $error;
    public $error_reason;
    public $error_description;
    
    public function __construct()
    {
        if(isset($_REQUEST['error']))
            $this->error = $_REQUEST['error'];
            
        if(isset($_REQUEST['error_reason']))
            $this->$error_reason = $_REQUEST['error_reason'];
            
        if(isset($_REQUEST['error_description']))
            $this->$error_description = $_REQUEST['error_description'];
    }
    
    public function hasError(){
        return $this->error != null;
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



class FacebookSessionUtil 
{
    private static $facebook;

    public static function init($facebook){
        self::$facebook = $facebook;
    }
    
    public static function getSessionName(){
        return 'fb_utils_'.self::$facebook->getAppID();
    }
    public static function has($name){
        return isset($_SESSION[self::getSessionName()][$name]);
    }
    public static function load($name){
        if(isset($_SESSION[self::getSessionName()][$name]))
            return $_SESSION[self::getSessionName()][$name];
        return null;
    }
    public static function save($name, $value){
        $_SESSION[self::getSessionName()][$name] = $value;
    }
    public static function clear($name){
        unset($_SESSION[self::getSessionName()][$name]);
    }
}






?>
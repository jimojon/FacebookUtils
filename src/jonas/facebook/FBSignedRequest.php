<?php

namespace jonas\facebook;

use jonas\Debug;
use jonas\facebook;

class FBSignedRequest
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
        FBSessionUtil::init($facebook);
    }

    /**
     * Core
     */

    public function save(){
        if($this->hasData()){
            Debug::TRACE('SignedRequest :: save');
            FBSessionUtil::save('signed_request', $this->data);
        }
    }
    public function clear(){
        Debug::TRACE('SignedRequest :: clear');
        FBSessionUtil::clear('signed_request');
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
            $this->data = FBSessionUtil::load('signed_request');
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

    public function setData($data){
        $this->data = $data;
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
        if(isset($this->data['page']['id']))
            return $this->data['page']['id'];
        return null;
    }

    public function isPageLiked(){
        if(isset($this->data['page']['liked']))
            return $this->data['page']['liked'] == 1;
        return null;
    }

    public function isUserAdmin(){
        if(isset($this->data['page']['admin']))
            return $this->data['page']['admin'] == 1;
        return null;
    }

    public function getUserCountry(){
        if(isset($this->data['user']['country']))
            return $this->data['user']['country'];
        return null;
    }

    public function getUserLocale(){
        if(isset($this->data['user']['locale']))
            return $this->data['user']['locale'];
        return null;
    }

    public function getUserID(){
        if(isset($this->data['user_id']))
            return $this->data['user_id'];
        return null;
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
            return FBAppType::PAGE_TAB;
        }else if(isset($this->data['user'])){
            return FBAppType::CANEVAS;
        }else{
            return FBAppType::WEBSITE;
        }
    }

    public function isWebsite(){
        return $this->getAppType() == FBAppType::WEBSITE;
    }

    public function isPageTab(){
        return $this->getAppType() == FBAppType::PAGE_TAB;
    }

    public function isCanevas(){
        return $this->getAppType() == FBAppType::CANEVAS;
    }
}

/**
 * FBAppType
 */
class FBAppType
{
    const WEBSITE = 'Website';
    const PAGE_TAB = 'PageTab';
    const CANEVAS = 'Canevas';
}
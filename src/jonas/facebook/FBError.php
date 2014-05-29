<?php

namespace jonas\facebook;

use jonas\Debug;

/**
 * FBError
 * Example : error_reason=user_denied&error=access_denied&error_description=The+user+denied+your+request.
 */
class FBError
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
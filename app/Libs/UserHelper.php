<?php

namespace App\Libs;

use Request;

class UserHelper
{
    public $deviceId        = "";
    public $tokenId         = "";
    public $appId           = "";
    public $appVersion      = "";
    public $hashKey         = "";
    public $isDebug         = "";
    public $loginId         = "";
    public $password        = "";
    public $apiKey          = "";
    public $userAgent       = "";
    public $ip              = "";

    function __construct()
    {
        $this->deviceId         = Request::header('x-device-id');
        $this->tokenId          = Request::header('token-id');
        $this->loginId          = Request::header('login-id');
        $this->password         = Request::header('password');
        $this->appId            = Request::header('x-app-id');
        $this->appVersion       = Request::header('x-app-version');
        $this->hashKey          = Request::header('x-hash-key');
        $this->isDebug          = Request::header('x-debug');
        $this->apiKey           = Request::header('x-apikey');
        $this->userAgent        = Request::server('HTTP_USER_AGENT');
        $this->ip               = Request::ip();
    }
}

<?php
namespace App\Libs;

use App\AppTraits\SettingTrait;
use App\Libs\DateTimeHelper;
use App\Models\Users;
use App\Models\Token;
use App\Libs\Helper;
use Request;
use Lang;

class ApiAuth
{
    use SettingTrait;
    public $errorCode       = "";
    public $errorMessage    = "";
    public $errorType       = "1";
    public $statusCode      = "200";
    public $result = array();

    public function error()
    {
        $response =  \Response::json([
            'err_code'      => $this->errorCode,
            'err_msg'       => $this->errorMessage,
            'header'        => array_merge($this->result, [
                'token_id'  => Request::header('token-id', '')
            ])
        ], 200);

        $response->send();

        die();
    }

    public function auth($user)
    {
        $isOk = true;

        if(!$this->isValid($user)) { // Data missing
            $this->errorCode        = $this->ERROR_USER_TOKEN_REQUIRE;
            $this->errorMessage     = trans('api_message.token_require');
            $isOk                   = false;
        }
        else {
            $userData = $this->vertifyToken($user);
            if(empty($userData) || is_null($userData)) {
                $this->errorCode        = $this->ERROR_USER_NOT_FOUND;
                $this->errorMessage     = trans('api_message.user_not_found');
                $isOk                   = false;
            }
            else {
                // Check token expire date
                $tokenExpireDate    = $userData->token_expire_date;
                $currentTime        = DateTimeHelper::dateNow();
                if($currentTime > $tokenExpireDate) {
                    $this->errorCode        = $this->ERROR_USER_TOKEN_EXPIRED;
                    $this->errorMessage     = trans('api_message.token_expired');
                    $isOk                   = false;
                }
            }
        }

        if(!$isOk) {
            $this->error();
        }

        return $userData;
    }

    public function isValid($user)
    {
        if(strlen($user->tokenId) == 0) {
            return false;
        }
        return true;
    }

    public function authenticate($user)
    {
        try {
            // Get User data
            $userData = Users::where('login_id', $user->loginId)
                                ->where('password', $user->password)
                                ->first();
            return $userData;
        }
        catch(\Exception $e) {
            echo $e->getMessage();
        }
    }

    public function vertifyToken($user)
    {
        try {
            $userData = Users::where('token_id', $user->tokenId)->first();
            return $userData;
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
}

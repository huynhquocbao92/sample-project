<?php

namespace App\Http\Controllers\Api\v12;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use App\AppTraits\SettingTrait;
use App\Models\Characters;
use App\Models\UserFriend;
use App\Models\Users;
use App\Libs\Helper;
use App\Libs\DateTimeHelper;
use App\Libs\UserHelper;
use App\Libs\ApiAuth;
use Validator;
use Hash;

/**
 * User controller
 * @author baohq
 * @date   2017-10-10
 */
class UsersController extends ApiBaseController
{
    /**
     * Check login_id param (only has alphanumeric)
     * @author baohq
     * @date   2017-10-11
     * @param  $value
     * @return pregex match
     */
    private function _checkLoginIdValid($value)
    {
        $regex = '/^[a-z0-9]*$/i';
        return preg_match($regex, $value);
    }

    /**
     * Check password param (only has alphanumeric)
     * @author baohq
     * @date   2017-10-11
     * @param  $value
     * @return pregex match
     */
    private function _checkPasswordValid($value)
    {
        $regex = '/^[a-z0-9]*$/i';
        return preg_match($regex, $value);
    }

    /**
     * Check full name param (only has japanese and alphanumeric)
     * @author baohq
     * @date   2017-10-11
     * @param  $value
     * @return pregex match
     */
    private function _checkFullNameValid($value)
    {
        $regex = '/[!@#$%^&*()+=\-_\[\]\';,.\/{}|":<>?~\\\\]/';
        return preg_match($regex, $value);
    }

    /**
     * Get Error message: user has been existed
     */
    private function _userHasBeenExistedMsg()
    {
        $this->_jsonNG($this->ERROR_USER_HAS_BEEN_EXIST, trans('api_message.user_has_been_exist'), 200)->send();
        die();
    }

    /**
     * Get Error message: login_id is invalid
     */
    private function _loginIdInvalidMsg()
    {
        $this->_jsonNG($this->ERROR_USER_INVALID_PARAM, trans('api_message.user_login_id_invalid'), 200)->send();
        die();
    }

    /**
     * Get Error message: full_name is invalid
     */
    private function _fullNameInvalidMsg()
    {
        $this->_jsonNG($this->ERROR_USER_INVALID_PARAM, trans('api_message.user_full_name_invalid'), 200)->send();
        die();
    }

    /**
     * Get Error message: password is invalid
     */
    private function _passwordInvalidMsg()
    {
        $this->_jsonNG($this->ERROR_USER_INVALID_PARAM, trans('api_message.user_password_invalid'), 200)->send();
        die();
    }

    /**
     * Check validate register param
     * @author baohq
     * @date   2017-10-11
     * @param  $value
     * @return result
     */
    private function _checkRegisterData($request)
    {
        $loginId    = $request->get('login_id', '');
        $password   = $request->get('password', '');
        $fullName   = $request->get('full_name', '');

        if(strlen($loginId) == 0 || strlen($password) == 0 || strlen($fullName) == 0) {
            $this->_jsonNG($this->ERROR_USER_INVALID_PARAM, trans('api_message.user_invalid_param'), 200)->send();
            die();
        }

        if(!$this->_checkLoginIdValid($loginId)) {
            $this->_loginIdInvalidMsg();
        }
        else if($this->_checkFullNameValid($fullName)) {
            $this->_fullNameInvalidMsg();
        }
        else if(!$this->_checkPasswordValid($password)) {
            $this->_passwordInvalidMsg();
        }

        // Check login_id has been exist or not
        $userData = Users::where('login_id', $loginId)->first();
        if(!is_null($userData)) {
            $this->_userHasBeenExistedMsg();
        }
    }

    /**
     * Register function
     * @author baohq
     * @date   2017-10-10
     * @param  Request    $request
     * @return
     */
    public function register(Request $request)
    {
        try {
            $loginId    = $request->get('login_id', '');
            $password   = $request->get('password', '');
            $fullName   = $request->get('full_name', '');

            // $validator = Validator::make($request->all(), Users::rules());
            $this->_checkRegisterData($request);
            // Create new use
            $user = new Users();
            $user->login_id     = $loginId;
            $user->full_name    = $fullName;
            $user->password     = $password;
            $user->status       = 1;
            $user->save();
            // Create token_id
            $this->_generateToken($user);

            // Add default user friend (Character has not setting keyword)
            $characterDefault = Characters::where('keyword_flag', 1)
                                            ->where('is_deleted', 0)
                                            ->orderBy('id', 'asc')
                                            ->pluck('id');

            if(count($characterDefault) > 0) {
                foreach ($characterDefault as $key => $value) {
                    $userFriend                 = new UserFriend();
                    $userFriend->user_id        = $user->id;
                    $userFriend->character_id   = $value;
                    $userFriend->save();
                }
            }

            return $this->_jsonOK($user);

        } catch (Exception $e) {
            return $this->_jsonCatchException($e->getMessage());
        }
    }

    /**
     * Login function
     * @author baohq
     * @date   2017-10-10
     * @param  Request    $request
     * @return
     */
    public function login(Request $request)
    {
        try {
            $param['login_id'] = $request->get('login_id', '');
            $param['password'] = $request->get('password', '');
            // Check param value
            $this->checkParam($param);

            $user = Users::where('login_id', $param['login_id'])->first();

            if(is_null($user)) {
                return $this->_jsonNG($this->ERROR_USER_NOT_FOUND, trans('api_message.user_not_found'), 200);
            }
            else if(!is_null($user) && !Hash::check($param['password'], $user->password)) {
                return $this->_jsonNG($this->ERROR_PASSWORD_NOT_MATCH, trans('api_message.user_password_not_match'), 200);
            }

            // Create token_id
            $this->_generateToken($user);

            return $this->_jsonOK($user);

        } catch (Exception $e) {
            return $this->_jsonCatchException($e->getMessage());
        }
    }

    /**
     * Update user information
     * @author baohq
     * @date   2017-10-11
     * @param  $value
     * @return user data
     */
    public function updateInfo(Request $request)
    {
        try {
            $loginId = $request->get('login_id', '');
            $password = $request->get('password', '');
            $fullName = $request->get('full_name', '');

            // Check if 3 params are empty
            if(strlen($loginId) == 0 && strlen($password) == 0 && strlen($fullName) == 0) {
                return $this->_jsonNG($this->ERROR_USER_INVALID_PARAM, trans('api_message.user_invalid_param'), 200);
            }

            // Update user data
            $updateUser = Users::where('token_id', $this->user->token_id)->first();

            if(is_null($updateUser)) {
                return $this->_jsonNG($this->ERROR_USER_NOT_FOUND, trans('api_message.user_not_found'), 200);
            }
            else {
                // Check login_id
                if(strlen($loginId) > 0 && $loginId != $updateUser->login_id) {
                    $checkUser = Users::where('login_id', $loginId)->first();
                    if(!is_null($checkUser)) {
                        // login_id has been existed
                        $this->_userHasBeenExistedMsg();
                    }
                    else if(!$this->_checkLoginIdValid($loginId)) {
                        // login_id invalid
                        $this->_loginIdInvalidMsg();
                    }

                    $updateUser->login_id = $loginId;
                }
                // Check full_name
                if(strlen($fullName) > 0) {
                    if($this->_checkFullNameValid($fullName)) {
                        $this->_fullNameInvalidMsg();
                    }

                    $updateUser->full_name = $fullName;
                }
                // Check password
                if(strlen($password) > 0) {
                    if(!$this->_checkPasswordValid($password)) {
                        $this->_passwordInvalidMsg();
                    }

                    $updateUser->password = $password;
                }
                // Save update data
                $updateUser->save();
            }

            return $this->_jsonOK($updateUser, trans('api_message.update_info_success'));

        } catch (Exception $e) {
            return $this->_jsonCatchException($e->getMessage());
        }
    }

    /**
     * Check User token expire
     * @author baohq
     * @date   2017-10-23
     * @param  Request    $request
     * @return Result
     */
    public function checkUserToken(Request $request)
    {
        try {
            if (empty($this->user) || is_null($this->user)) {

                return $this->_jsonNG($this->ERROR_USER_NOT_FOUND, trans('api_message.user_not_found'), 200);

            } else {
                // Check token expire date
                $tokenExpireDate    = $this->user->token_expire_date;
                $currentTime        = DateTimeHelper::dateNow();

                if ($currentTime > $tokenExpireDate) {

                    return $this->_jsonNG($this->ERROR_USER_TOKEN_EXPIRED, trans('api_message.token_expired'), 200);

                } else {

                    return $this->_jsonOK([]);
                }
            }
        } catch (Exception $e) {

            echo $e->getMessage();
        }
    }
}

<?php

namespace App\Http\Controllers\Api\v12;

use App\Libs\ApiAuth;
use App\Libs\DateTimeHelper;
use App\Libs\Helper;
use App\Libs\UserHelper;
use App\Models\Users;
use Illuminate\Routing\Controller as BaseController;
use App\AppTraits\SettingTrait;


class ApiBaseController extends BaseController
{
    use SettingTrait;

    public $userObj         = null;
    public $user            = null;
    public $gridPageSize    = 20;

    public function __construct()
    {
        $this->userObj  = new UserHelper();
        $this->user     = $this->_getUserData($this->userObj->tokenId);
    }

    protected function _generateToken($user)
    {
        if(!is_null($user)) {
            $user->token_id             = md5(gethostname()).md5(uniqid(rand(),1));
            $user->token_expire_date    = DateTimeHelper::dateAddDay(config('site.token_expire_time', 7));
            $user->last_login           = DateTimeHelper::dateNow();
            $user->save();
        }
    }

    protected function _getUserData($tokenId)
    {
        $userData = Users::where('token_id', $tokenId)->first();
        return $userData;
    }

    public function checkParam($params)
    {
        foreach($params as $param) {
            if(strlen(trim($param)) <= 0) {
                $header = [];
                if(Helper::debugMode()) {
                    $header = [
                        'token_id' => $this->userObj->tokenId
                    ];
                }

                $response =  \Response::json([
                    'err_code'  => $this->ERROR_USER_INVALID_PARAM,
                    'err_msg'   => trans('api_message.invalid_param'),
                    'header' => $header
                ], 200);

                $response->send();
                die();
            }
        }
        return true;
    }

    public function checkIdParam($params)
    {
        foreach($params as $param) {
            if($param <= 0) {
                $header = [];
                if(Helper::debugMode()) {
                    $header = [
                        'token_id' => $this->userObj->tokenId
                    ];
                }

                $response =  \Response::json([
                    'err_code'  => $this->ERROR_USER_INVALID_PARAM,
                    'err_msg'   => trans('api_message.invalid_param'),
                    'header' => $header
                ], 200);

                $response->send();
                die();
            }
        }
        return true;
    }

    /**
     * Return json data Error
     * @author baohq
     * @date   2017-10-10
     * @param  [type]     $data
     * @return json data
     */
    protected function _jsonNG($errorCode, $errorMsg, $statusCode = 200)
    {
        $header = [];
        if(Helper::debugMode()) {
            $header = [
                'token_id' => $this->userObj->tokenId
            ];
        }

        return response()->json([
            'err_code'  => $errorCode,
            'err_msg'   => $errorMsg,
            'header'    => $header
        ], $statusCode);
    }

    /**
     * Return json data not found
     * @author baohq
     * @date   2017-10-10
     * @param  [type]     $data
     * @return json data
     */
    protected function _jsonNotFound($statusCode = 200)
    {
        $header = [];
        if(Helper::debugMode()) {
            $header = [
                'token_id' => $this->userObj->tokenId
            ];
        }

        return response()->json([
            'err_code'  => $this->ERROR_DATA_NOT_FOUND,
            'err_msg'   => trans('api_message.data_not_found'),
            'header'    => $header
        ], $statusCode);
    }

    protected function _jsonOKMore($data, $other)
    {
        $ret['result'] = $data;
        return response()->json([
            'err_code'  => '0',
            'err_msg'   => '',
            'data'      => array_merge($ret, $other, [
                'header' => [
                    'token_id' => $this->userObj->tokenId
                ]
            ])
        ], 200);
    }

    protected function _jsonDefaultError($errorMsg)
    {
        $header = [];
        if(Helper::debugMode()) {
            $header = [
                'token_id' => $this->userObj->tokenId
            ];
        }
        return response()->json([
            'err_code'  => $this->ERROR_DEFAULT,
            'err_msg'   => $errorMsg,
            'data' => [
                'header' => $header
            ]
        ], 200);
    }

    protected function _jsonDefaultErrorMore($errorMsg, $oject = [])
    {
        $header = [];
        if(Helper::debugMode()) {
            $header = [
                'token_id' => $this->userObj->tokenId
            ];
        }
        return response()->json([
            'err_code'  => $this->ERROR_DEFAULT,
            'err_msg'   => $errorMsg,
            'data'      => [
                'result' => $oject,
                'header' => $header
            ]
        ], 200);
    }

    /**
     * Return json data Exception error
     * @author baohq
     * @date   2017-10-10
     * @param  [type]     $data
     * @return json data
     */
    protected function _jsonCatchException($errorMsg)
    {
        if(Helper::debugMode()) {
            $header = [];
            if(Helper::debugMode()) {
                $header = [
                    'token_id' => $this->userObj->tokenId
                ];
            }

            return response()->json([
                'err_code' => $this->CATCH_EXCEPTION,
                'err_msg'  => $errorMsg,
                'data'     => [
                    'header' => $header
                ]
            ], 200);
        }

        return response()->json([
            'err_code'  => $this->CATCH_EXCEPTION,
            'err_msg'   => trans('api_message.exception_error')
        ], 200);
    }

    /**
     * Return json data OK
     * @author baohq
     * @date   2017-10-10
     * @param  [type]     $data
     * @return json data
     */
    protected function _jsonOK($data, $message='')
    {
        $message = $message == '' ? trans('api_message.success') : $message;
        $ret['result'] = $data;
        return response()->json([
            'err_code'  => '0',
            'err_msg'   => $message,
            'data'      => array_merge($ret, [
                'header' => [
                    'token_id' => $this->userObj->tokenId
                ]
            ])
        ], 200);
    }

    /**
     * Format Media URL of Array data
     * @author baohq
     * @date   2018-03-01
     * @param  array      $data       [description]
     * @param  array      $fieldArray [description]
     * @return [type]                 [description]
     */
    public function formatMediaOfArray($data=[], $fieldArray=[])
    {
        try {
            if (!empty($data) && !empty($fieldArray)) {
                for ($i = 0; $i < count($data); $i++) {
                    for ($j = 0; $j < count($fieldArray); $j++) {
                        $fieldName              = $fieldArray[$j];
                        $data[$i]->$fieldName   = $this->_formatMediaUrl($data[$i]->$fieldName);
                    }
                }
            }

            return $data;

        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * Format Media URL of Object data
     * @author baohq
     * @date   2018-03-01
     * @param  array      $data       [description]
     * @param  array      $fieldArray [description]
     * @return [type]                 [description]
     */
    public function formatMediaOfObject($data=null, $fieldArray=[])
    {
        try {
            if (!is_null($data) && !empty($fieldArray)) {
                for ($j = 0; $j < count($fieldArray); $j++) {
                    $fieldName          = $fieldArray[$j];
                    $data->$fieldName   = $this->_formatMediaUrl($data->$fieldName);
                }
            }

            return $data;

        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * Replace Media URL
     * @author baohq
     * @date   2018-03-01
     * @param  [type]     $url [description]
     * @return [type]          [description]
     */
    protected function _formatMediaUrl($url)
    {
        try {
            // Code removed
            return $url;
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }
}

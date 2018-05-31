<?php

namespace App\Http\Controllers\Api\v12;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use App\AppTraits\SettingTrait;
use App\Models\UserFriend;
use App\Models\CharacterChat;
use App\Models\CharacterAlarm;
use App\Models\CharacterTel;
use App\Models\Characters;
use App\Models\UserTel;
use App\Libs\DateTimeHelper;
use App\Libs\ApiAuth;
use App\Libs\Helper;
use Validator;
use Hash;
use DB;

/**
 * Character controller
 * @author baohq
 * @date   2017-10-10
 */
class CharacterController extends ApiBaseController
{
    private $_currentDate = null;
    private $_defaultDate = null;

    public function __construct()
    {
        parent::__construct();
        $this->_currentDate  = DateTimeHelper::dateNow();
        $this->_defaultDate  = DateTimeHelper::getDefaultDateTime();
    }

    /**
     * Get user friend
     * @author baohq
     * @date   2017-10-10
     * @param  Request    $request
     * @return
     */
    public function getFriend(Request $request)
    {
        try {
            $page       = $request->get('page', 1);
            $offSet     = ($page - 1) * $this->gridPageSize;
            $userId     = $this->user->id;

            // Check user_friend data
            $insertFriend = DB::insert("INSERT INTO user_friend (user_id, character_id, created_at, updated_at, is_deleted) ". DB::raw("
                            SELECT '".$userId."', cha.id,  now(), now(), 0
                            FROM user_friend usf
                            RIGHT OUTER JOIN (
                                SELECT a.id
                                FROM characters a
                                WHERE a.keyword_flag = 1
                                AND a.is_deleted = 0
                                AND ((a.viewdate_start    = '".$this->_defaultDate."' AND a.viewdate_end  = '".$this->_defaultDate."')
				                        OR (a.viewdate_start <= '".$this->_currentDate."' AND a.viewdate_end  = '".$this->_defaultDate."')
				                        OR (a.viewdate_start  = '".$this->_defaultDate."' AND a.viewdate_end >= '".$this->_currentDate."')
				                        OR (a.viewdate_start <= '".$this->_currentDate."' AND a.viewdate_end >= '".$this->_currentDate."'))
                                ) AS cha
                            ON usf.character_id = cha.id
                            AND usf.user_id = '".$userId."'
                            AND usf.is_deleted = 0
                            WHERE usf.user_id IS NULL
                            AND usf.character_id IS NULL
                            "));

            $userFriend = DB::table('user_friend as usf')
                                ->leftJoin('characters as cha', 'cha.id', '=', 'usf.character_id')
                                ->where('usf.user_id', $userId)
                                ->where('usf.is_deleted', 0)
                                ->where('cha.status', 1)
                                ->where('cha.is_deleted', 0)
                                ->whereRaw('
				                        ((cha.viewdate_start    = "'.$this->_defaultDate.'" AND cha.viewdate_end  = "'.$this->_defaultDate.'")
				                        OR (cha.viewdate_start <= "'.$this->_currentDate.'" AND cha.viewdate_end  = "'.$this->_defaultDate.'")
				                        OR (cha.viewdate_start  = "'.$this->_defaultDate.'" AND cha.viewdate_end >= "'.$this->_currentDate.'")
				                        OR (cha.viewdate_start <= "'.$this->_currentDate.'" AND cha.viewdate_end >= "'.$this->_currentDate.'"))
				                    ')
                                ->select('usf.id','cha.id as char_id', 'cha.viewdate_start', 'cha.viewdate_end', 'cha.char_name', 'cha.icon_img', 'cha.status', 'cha.chat_status')
                                ->orderBy('cha.id', 'asc')
                                ->skip($offSet)
                                ->take($this->gridPageSize)
                                ->get();

            if(is_null($userFriend)) {

                return $this->_jsonNotFound();
            }

            // Replace media URL on 2018-03-01 by baohq (task #9967)
            $userFriend = $this->formatMediaOfArray($userFriend, ['icon_img']);

            return $this->_jsonOK($userFriend);

        } catch (Exception $e) {

            return $this->_jsonCatchException($e->getMessage());
        }
    }

    /**
     * Get Tel voice data of character (Call this API from call history list)
     * @author baohq
     * @date   2017-10-31
     * @param  Request    $request
     * @return
     */
    public function getTelVoice(Request $request)
    {
        try {
            $param['char_tel_id']  = $request->get('char_tel_id', '');
            // Check param value
            $this->checkParam($param);

            $voiceData   = null;

            $voiceData = CharacterTel::where('id', $param['char_tel_id'])
                                        ->where('is_deleted', 0)
                                        ->select('id', 'viewdate_start', 'viewdate_end', 'char_id', 'tel_name', 'char_img', 'voice_data', 'status', 'is_deleted')
                                        ->first();

            if(is_null($voiceData)) {

                return $this->_jsonNotFound();
            }

            // Replace media URL on 2018-03-01 by baohq (task #9967)
            $voiceData = $this->formatMediaOfObject($voiceData, ['char_img', 'voice_data']);

            return $this->_jsonOK($voiceData);

        } catch (Exception $e) {

            return $this->_jsonCatchException($e->getMessage());
        }
    }

    /**
     * Get Alarm voice data of character (Call this API from chat with content text set alarm)
     * 1 Character will set only 1 alarm voice
     * @author baohq
     * @date   2017-10-31
     * @param  int    $char_id
     * @return
     */
    public function getAlarmVoice(Request $request)
    {
        try {
            $param['char_id']  = $request->get('char_id', '');
            // Check param value
            $this->checkParam($param);

            $voiceData   = null;

            $voiceData = DB::table('character_alarm as ca')
                            ->leftJoin('characters as c', 'c.id', '=', 'ca.char_id')
                            ->where('ca.char_id', $param['char_id'])
                            ->where('ca.status', 1)
                            ->where('ca.is_deleted', 0)
                            ->whereRaw('
                                ((ca.viewdate_start    = "'.$this->_defaultDate.'" AND ca.viewdate_end  = "'.$this->_defaultDate.'")
                                OR (ca.viewdate_start <= "'.$this->_currentDate.'" AND ca.viewdate_end  = "'.$this->_defaultDate.'")
                                OR (ca.viewdate_start  = "'.$this->_defaultDate.'" AND ca.viewdate_end >= "'.$this->_currentDate.'")
                                OR (ca.viewdate_start <= "'.$this->_currentDate.'" AND ca.viewdate_end >= "'.$this->_currentDate.'"))
                            ')
                            ->select('ca.id', 'ca.viewdate_start', 'ca.viewdate_end', 'ca.char_id', 'ca.tel_name', 'ca.char_img', 'ca.voice_data', 'c.chat_miss_message', 'c.alert_set_message', 'c.alert_set_miss_message', 'c.alert_cancel_message','ca.status', 'ca.is_deleted')
                            ->first();

            // Replace media URL on 2018-03-01 by baohq (task #9967)
            $voiceData = $this->formatMediaOfObject($voiceData, ['char_img', 'voice_data']);

            if(is_null($voiceData)) {
                // Select default data with miss message
                $voiceData = Characters::where('is_deleted', 0)
                                        ->where('id', $param['char_id'])
                                        ->selectRaw('"" as id, "" as viewdate_start, "" as viewdate_end, id as char_id, "" as tel_name, "" as char_img, "" as voice_data, chat_miss_message, alert_set_message, alert_set_miss_message, alert_cancel_message, "" as status, "" as is_deleted')
                                        ->first();
                // return $this->_jsonNotFound();
            }

            return $this->_jsonOK($voiceData);

        } catch (Exception $e) {

            return $this->_jsonCatchException($e->getMessage());
        }
    }

    /**
     * Get voice content of character
     * Type: 1 (character tel) - 2 (character alarm)
     * @author baohq
     * @date   2017-10-13
     * @param  Request    $request
     * @return
     */
    public function getVoice(Request $request)
    {
        try {
            $param['char_id']  = $request->get('char_id', '');
            $param['type']     = $request->get('type', 1);
            // Check param value
            $this->checkParam($param);

            $voiceData   = null;

            if($param['type'] == 1) {
                $voiceData = CharacterTel::where('char_id', $param['char_id'])
                                            ->where('status', 1)
                                            ->where('is_deleted', 0)
                                            ->select('id', 'viewdate_start', 'viewdate_end', 'char_id', 'tel_name', 'char_img', 'voice_data', 'status', 'is_deleted')
                                            ->orderBy('id', 'asc')
                                            ->first();
            }
            else if($param['type'] == 2) {
                $voiceData = CharacterAlarm::where('char_id', $param['char_id'])
                                            ->where('status', 1)
                                            ->where('is_deleted', 0)
                                            ->select('id', 'viewdate_start', 'viewdate_end', 'char_id', 'tel_name', 'char_img', 'voice_data', 'status', 'is_deleted')
                                            ->orderBy('id', 'asc')
                                            ->first();
            }

            if(is_null($voiceData)) {

                return $this->_jsonNotFound();
            }

            // Replace media URL on 2018-03-01 by baohq (task #9967)
            $voiceData = $this->formatMediaOfArray($voiceData, ['char_img', 'voice_data']);

            return $this->_jsonOK($voiceData);

        } catch (Exception $e) {

            return $this->_jsonCatchException($e->getMessage());
        }
    }

    /**
     * Get voice history by character
     * @author baohq
     * @date   2017-10-13
     * @param  Request    $request
     * @return
     */
    public function getVoiceHistory(Request $request)
    {
        try {
            $page               = $request->get('page', 1);
            $offSet             = ($page - 1) * $this->gridPageSize;

            $param['char_id']  = $request->get('char_id', '');
            // Check param value
            $this->checkParam($param);

            $voiceHistory   = DB::table('user_tel as u')
                                ->leftJoin('character_tel as ctl', 'ctl.id', '=', 'u.char_tel_id')
                                ->leftJoin('characters as c', 'c.id', '=', 'ctl.char_id')
                                ->where('u.user_id', $this->user->id)
                                ->where('ctl.char_id', $param['char_id'])
                                ->where('u.is_deleted', 0)
                                ->select('u.id', 'ctl.id as char_tel_id', 'ctl.char_id', 'ctl.viewdate_start', 'ctl.viewdate_end', 'ctl.tel_name', 'c.icon_img as char_img', 'ctl.char_img as char_tel_img', 'ctl.voice_data', 'ctl.status', 'u.updated_at as created_at')
                                ->orderBy('u.updated_at', 'desc')
                                ->skip($offSet)
                                ->take($this->gridPageSize)
                                ->get();

            if(is_null($voiceHistory)) {

                return $this->_jsonNotFound();
            }

            // Replace media URL on 2018-03-01 by baohq (task #9967)
            $voiceHistory = $this->formatMediaOfArray($voiceHistory, ['char_img', 'char_tel_img', 'voice_data']);

            return $this->_jsonOK($voiceHistory);

        } catch (Exception $e) {

            return $this->_jsonCatchException($e->getMessage());
        }
    }

    /**
     * Set voice history
     * @author baohq
     * @date   2017-10-13
     * @param  Request    $request
     * @return
     */
    public function setVoiceHistory(Request $request)
    {
        try {
            $param['voice_id'] = (int)$request->get('voice_id', '');
            // Check param value
            $this->checkParam($param);
            $this->checkIdParam($param);

            if(!UserTel::isExist($this->user->id, $param['voice_id'])) {
                $userTel = new UserTel();
                $userTel->user_id = $this->user->id;
                $userTel->char_tel_id = $param['voice_id'];
                $userTel->save();
            }
            else {
                // Update call time if existed
                UserTel::where('user_id', $this->user->id)
                        ->where('char_tel_id', $param['voice_id'])
                        ->update(array('updated_at'	=> DateTimeHelper::dateNow()));
            }

            return $this->_jsonOK(1);

            // return $this->_jsonOK(0, trans('api_message.call_history_has_been_exist'));

        } catch (Exception $e) {

            return $this->_jsonCatchException($e->getMessage());
        }
    }

    /**
     * Search Character with keyword then return character information
     * @date   2017-11-21
     */
    public function searchFriendCharacter(Request $request)
    {
        try {
            $param['keyword'] = $request->get('keyword', '');
            // Check param value
            $this->checkParam($param);

            $characterResult = Characters::where('is_deleted', 0)
                                            ->where('status', 1)
                                            ->where('keyword_flag', 2)
                                            ->where('keyword', 'like', '%'.$param['keyword'].'%')
                                            ->whereRaw('
                                                ((viewdate_start    = "'.$this->_defaultDate.'" AND viewdate_end = "'.$this->_defaultDate.'")
                                                OR (viewdate_start <= "'.$this->_currentDate.'" AND viewdate_end = "'.$this->_defaultDate.'")
                                                OR (viewdate_start  = "'.$this->_defaultDate.'" AND viewdate_end >= "'.$this->_currentDate.'")
                                                OR (viewdate_start <= "'.$this->_currentDate.'" AND viewdate_end >= "'.$this->_currentDate.'"))
                                            ')
                                            ->select('id')
                                            ->get();

            if(count($characterResult) > 0) {
                // Check new count
                $count = 0;

                foreach($characterResult as $item) {
                    // Check User friend
                    if(!UserFriend::isExist($this->user->id, $item->id)) {
                        // Return this character information
                        $data = Characters::where('id', $item->id)
                                            ->where('is_deleted', 0)
                                            ->select('id', 'viewdate_start', 'viewdate_end', 'char_name', 'icon_img', 'status', 'chat_status')
                                            ->first();

                        // Replace media URL on 2018-03-01 by baohq (task #9967)
                        $data = $this->formatMediaOfObject($data, ['icon_img']);

                        return $this->_jsonOK($data, trans('api_message.add_new_friend_success'));
                    }
                }

                if($count == 0) {

                    return $this->_jsonOK(null, trans('api_message.friend_has_been_exist'));
                }
            }
            else {
                // Search not found
                return $this->_jsonNotFound();
            }

        } catch (Exception $e) {

            return $this->_jsonCatchException($e->getMessage());
        }
    }

    /**
     * Add User Friend
     * @date   2017-11-21
     */
    public function addFriendCharacter(Request $request)
    {
        try {
            $param['character_id'] = $request->get('character_id', '');
            // Check param value
            $this->checkParam($param);
            // Check character data
            $flag = Characters::where('id', $param['character_id'])
                                ->where('is_deleted', 0)
                                ->whereRaw('
                                    ((viewdate_start    = "'.$this->_defaultDate.'" AND viewdate_end = "'.$this->_defaultDate.'")
                                    OR (viewdate_start <= "'.$this->_currentDate.'" AND viewdate_end = "'.$this->_defaultDate.'")
                                    OR (viewdate_start  = "'.$this->_defaultDate.'" AND viewdate_end >= "'.$this->_currentDate.'")
                                    OR (viewdate_start <= "'.$this->_currentDate.'" AND viewdate_end >= "'.$this->_currentDate.'"))
                                ')
                                ->count();

            if($flag > 0) {
                $count = 0;
                // Check User Friend
                if(!UserFriend::isExist($this->user->id, $param['character_id'])) {
                    // Add User Friend
                    $userFriend                 = new UserFriend();
                    $userFriend->user_id        = $this->user->id;
                    $userFriend->character_id   = $param['character_id'];
                    $userFriend->save();

                    $count ++;

                    return $this->_jsonOK($count, trans('api_message.add_new_friend_success'));
                }

                if($count == 0) {

                    return $this->_jsonOK($count, trans('api_message.friend_has_been_exist'));
                }
            }
            else {
                // Search not found
                return $this->_jsonNotFound();
            }

        } catch (Exception $e) {

            return $this->_jsonCatchException($e->getMessage());
        }
    }
}

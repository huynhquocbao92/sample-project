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
 * Character chat controller
 * @author baohq
 * @date   2017-11-02
 */
class CharacterChatNormalController extends ApiBaseController
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
     * Detect request text is question or not
     * @author baohq
     * @date   2017-11-02
     * @param  [type]     $param [description]
     * @return [type]            [description]
     */
    private function _detectQuestion($value)
    {
        if (mb_substr($value, -1) == '?' || mb_substr($value, -1) == '？') {

            return true;
        }

        return false;
    }

    /**
     * Query search chat data
     * @author baohq
     * @date   2017-11-28
     * @param  [type]     $charId    [description]
     * @param  [type]     $type      [description]
     * @param  [type]     $text      [description]
     * @param  array      $existedId [description]
     * @return [type]                [description]
     */
    private function _querySearchChatData($charId, $type, $text, $existedId=[])
    {
        $returnFields = "id, viewdate_start, viewdate_end, char_id, '' as tel_name, '' as char_img, '' as voice_data, class, response_img, response_text_1_1, response_text_1_2, response_text_2_1, response_text_2_2, response_text_3_1, response_text_3_2, response_text_4_1, response_text_4_2, response_text_5_1, response_text_5_2, '' as chat_miss_message";

        $data = DB::table('character_chat as c')
                    ->where('c.status', 1)
                    ->where('c.is_deleted', 0)
                    ->whereRaw('
                        ((c.viewdate_start    = "'.$this->_defaultDate.'" AND c.viewdate_end  = "'.$this->_defaultDate.'")
                        OR (c.viewdate_start <= "'.$this->_currentDate.'" AND c.viewdate_end  = "'.$this->_defaultDate.'")
                        OR (c.viewdate_start  = "'.$this->_defaultDate.'" AND c.viewdate_end >= "'.$this->_currentDate.'")
                        OR (c.viewdate_start <= "'.$this->_currentDate.'" AND c.viewdate_end >= "'.$this->_currentDate.'"))
                    ')
                    ->where('c.char_id', $charId)
                    ->where('c.response_tel_flag', 1)
                    ->where('c.class', $type)
                    ->whereNotIn('c.id', $existedId)
                    ->where(function ($queryFunction) use ($text) {
                        // Append where for reaction_word_1
                        $queryFunction->whereRaw("(BINARY ? LIKE CONCAT('%', reaction_word_1,'%') AND reaction_word_1 <> '')", [$text]);
                        // Append where for reaction_word_2 to reaction_word_8
                        for($i = 2; $i <= 10; $i++) {
                            $param = 'reaction_word_'.$i;
                            $queryFunction->orWhereRaw("(BINARY ? LIKE CONCAT('%', $param,'%') AND $param <> '')", [$text]);
                        }
                    })
                    ->selectRaw($returnFields)
                    ->get();

        return $data;
    }

    /**
     * Search with call type
     * @author baohq
     * @date   2017-11-02
     * @param  [type]     $param [description]
     * @return [type]            [description]
     */
    private function _searchWithCallType($charId, $text)
    {
        try {
            $data = DB::table('character_chat as c')
                        ->where('c.status', 1)
                        ->where('c.is_deleted', 0)
                        ->whereRaw('
                            ((c.viewdate_start    = "'.$this->_defaultDate.'" AND c.viewdate_end = "'.$this->_defaultDate.'")
                            OR (c.viewdate_start <= "'.$this->_currentDate.'" AND c.viewdate_end = "'.$this->_defaultDate.'")
                            OR (c.viewdate_start  = "'.$this->_defaultDate.'" AND c.viewdate_end >= "'.$this->_currentDate.'")
                            OR (c.viewdate_start <= "'.$this->_currentDate.'" AND c.viewdate_end >= "'.$this->_currentDate.'"))
                        ')
                        ->where('c.char_id', $charId)
                        ->where('c.response_tel_flag', 2)
                        ->where(function ($queryFunction) use ($text) {
                            // Append where for reaction_word_1
                            $queryFunction->whereRaw("(BINARY ? LIKE CONCAT('%', reaction_word_1,'%') AND reaction_word_1 <> '')", [$text]);
                            // Append where for reaction_word_2 to reaction_word_8
                            for($i = 2; $i <= 10; $i++) {
                                $param = 'reaction_word_'.$i;
                                $queryFunction->orWhereRaw("(BINARY ? LIKE CONCAT('%', $param,'%') AND $param <> '')", [$text]);
                            }
                        })
                        ->selectRaw('id, char_tel_id')
                        ->get();

            $returnData = array();

            // Get Character Tel data
            if(!empty($data)) {
                foreach ($data as $item) {

                    $charTel = CharacterTel::where('id', $item->char_tel_id)
                                            ->where('status', 1)
                                            ->where('is_deleted', 0)
                                            ->whereRaw('
                                                ((viewdate_start    = "'.$this->_defaultDate.'" AND viewdate_end = "'.$this->_defaultDate.'")
                                                OR (viewdate_start <= "'.$this->_currentDate.'" AND viewdate_end = "'.$this->_defaultDate.'")
                                                OR (viewdate_start  = "'.$this->_defaultDate.'" AND viewdate_end >= "'.$this->_currentDate.'")
                                                OR (viewdate_start <= "'.$this->_currentDate.'" AND viewdate_end >= "'.$this->_currentDate.'"))
                                            ')
                                            ->selectRaw('id, viewdate_start, viewdate_end, char_id, tel_name, char_img, voice_data, "" as class, "" as response_img, "" as response_text_1, "" as response_text_2, "" as chat_miss_message')
                                            ->first();

                    if(!is_null($charTel)) {
                        // array_push($returnData, $charTel->toArray());
                        // Fix bug #10263 get field to format URL error by baohq (2018-03-06)
                        array_push($returnData, $charTel);
                    }
                }
            }

            return $returnData;

        } catch (Exception $e) {

            echo $e->getMessage();
        }
    }

    /**
     * Search with input type
     * @author baohq
     * @date   2017-11-02
     * @param  [type]     $param [description]
     * @return [type]            [description]
     */
    private function _searchWithType($charId, $text, $type=1)
    {
        try {
            // Return data array
            $data = array();

            // type = 2 is question type so search from right to left of input text
            if ($type == 2) {

                $data = $this->_querySearchChatData($charId, $type, $text);

            } else {
                $length     = mb_strlen($text); // Get length of text
                $existedId  = array(); // If record has been queried, will push id into existed array to skip in next query

                // Search with character from left to right of text
                for ($i = 0; $i <= $length ; $i++) {
                    $searchText = mb_substr($text, 0, $i);
                    $wordData   = $this->_querySearchChatData($charId, $type, $searchText, $existedId);

                    if (count($wordData) > 0) {
                        foreach ($wordData as $value) {
                            array_push($data, $value);
                            array_push($existedId, $value->id);
                        }
                        // Remove this text because has been search for question
                        // $text = mb_substr($text, $i, $length);
                    }
                }
            }

            return $data;

        } catch (Exception $e) {

            echo $e->getMessage();
        }
    }

    /**
     * Search with question type
     * @author baohq
     * @date   2017-11-07
     * @param  [type]     $param [description]
     * @return [type]            [description]
     */
    private function _searchWithQuestionType($charId, $text)
    {
        try {
            // Get text length
            $length             = mb_strlen($text);
            $normalSearchText   = '';
            $questionData       = array();
            $normalData         = array();
            // Search with character from right to left of text
            for ($i = $length-1; $i >= 0 ; $i--) {
                $searchText     = mb_substr($text, $i);
                // Search with question case first
                $questionData   = $this->_searchWithType($charId, $searchText, 2);

                if(count($questionData) > 0) {
                    // Remove this text because has been search for question
                    $normalSearchText = mb_substr($text, 0, $i);
                    break;
                }
            }
            // Countinue search with normal case
            $normalData = $this->_searchWithType($charId, $normalSearchText, 1);

            // Merge search data result
            $normalDataResults   = count($normalData) > 0 ? $normalData : array();
            $questionDataResults = count($questionData) > 0 ? $questionData->toArray() : array();

            // Merge data with first is normal data, second is question data
            $data = array_merge($normalDataResults, $questionDataResults);

            return $data;

        } catch (Exception $e) {

            echo $e->getMessage();
        }
    }

    /**
     * get Random response_text value not empty
     * 1. Get list response_text is not empty
     * 2. Get random data of list
     * 3. Check hasMsgFlag if hasMsgFlag == 0 will get default message
     */
    private function _getRandomResponseText($charId, $data=[])
    {
        try {
            // Has message response flag
            $hasMsgFlag     = 0;

            foreach ($data as $item) {
                // Check response_text field data
                $responseArr            = [];
                $resId                  = 0;
                // Set item response_text field
                $item->response_text_1  = '';
                $item->response_text_2  = '';

                // 1. Get list response_text is not empty
                for ($i=0; $i < 5; $i++) {
                    $resParam1 = 'response_text_'.($i + 1).'_1';
                    $resParam2 = 'response_text_'.($i + 1).'_2';

                    if(strlen($item->$resParam1) > 0 || strlen($item->$resParam2) > 0) {
                        $responseArr[$resId]  = array(
                            'response_text_1' => $item->$resParam1,
                            'response_text_2' => $item->$resParam2,
                        );
                        $resId ++;
                    }
                }

                // 2. Get random response text value
                if(count($responseArr) > 0) {
                    $rand                   = $responseArr[array_rand($responseArr)];
                    $item->response_text_1  = $rand['response_text_1'];
                    $item->response_text_2  = $rand['response_text_2'];

                    // increase $hasMsgFlag
                    $hasMsgFlag++;
                }

                // Remove not need params
                for ($i=1; $i <= 5; $i++) {
                    $param1 = 'response_text_'.$i.'_1';
                    $param2 = 'response_text_'.$i.'_2';
                    unset($item->$param1);
                    unset($item->$param2);
                }
            }

            // 3. If $hasMsgFlag == 0 will return default message
            if ($hasMsgFlag == 0) {
                $data = $this->_getChatMessageNotFound($charId);
            }

            return $data;

        } catch (Exception $e) {

            echo $e->getMessage();
        }
    }

    /**
     * get Chat message if search not found
     * @author baohq
     * @date   2017-11-15
     */
    private function _getChatMessageNotFound($charId)
    {
        try {
            $data = Characters::where('id', $charId)
                                ->selectRaw('1 as id, viewdate_start, viewdate_end, id as char_id, "" as tel_name, "" as char_img, "" as voice_data, "" as class, "" as response_img, "" as response_text_1, "" as response_text_2, chat_miss_message')
                                ->get();

            return $data;

        } catch (Exception $e) {

            echo $e->getMessage();
        }
    }

    /**
     * Search chat content of character by text:
     * I. REQUEST:
     * 1. Search text with call type first. If is call will return data else will search text countinue
     * 2. Search text with text type : Check text is question or normal text? => Filter with 3 class: Question, Special, Normal and search field (reaction_word_1 -> reaction_word_10)
     *  - If text is question => filter question then filter with normal case => return matching data or empty
     *  - If text is normal text => filter special first, if not match will filter normal => return matching data or empty
     * II. RESPONSE: 3 type
     * 1. Call data
     * 2. Image
     * 3. Text: will random 1 in 5 field (response_text_) and return a couple field (sample response_text_1_1 and response_text_1_2)
     * @author baohq
     * @date   2017-10-13
     * @param  Request    $request
     * @return
     */
    public function getReturnChat(Request $request)
    {
        try {
            $param['char_id']   = $request->get('char_id', '');
            $param['text']      = $request->get('text', '');
            // Check param value
            $this->checkParam($param);
            /**
             * type = 0: Not match result
             * type = 1: Normal
             * type = 2: Question
             * type = 3: Special
             * type = 4: Call
             */
            $charId = trim($param['char_id']);
            $text   = trim($param['text']);
            $type   = 4; // Default type is call

            // 1. Search text with call type: response_tel_flag = 2
            $data   = $this->_searchWithCallType($charId, $text);

            if(count($data) == 0) {
                // 2. Check text is question or normal text
                if($this->_detectQuestion($text)) {
                    // Search text with question type
                    $type = 2;
                    // $data = $this->_searchWithType($param, $type);
                    $data = $this->_searchWithQuestionType($charId, $text);
                } else {
                    // Search text with special type
                    $type = 3;
                    $data = $this->_searchWithType($charId, $text, $type);
                }
            }

            // Search with normal
            if (count($data) == 0) {
                $type = 1;
                $data = $this->_searchWithType($charId, $text, $type);
            }

            // Get message if search not match data will get default message
            if (count($data) == 0) {
                $type = 0;

                $data = $this->_getChatMessageNotFound($charId);

            } else {
                if ($type != 4) {
                    // Format random response_text data not need use for case type = 4 (call)
                    $data = $this->_getRandomResponseText($charId, $data);
                }
            }

            // Replace media URL on 2018-03-01 by baohq (task #9967)
            $data = $this->formatMediaOfArray($data, ['char_img', 'voice_data', 'response_img']);

            $returnData = array(
                'type'      => $type,
                'content'   => $data
            );

            return $this->_jsonOK($returnData);

        } catch (Exception $e) {

            return $this->_jsonCatchException($e->getMessage());
        }
    }

    /**
     * Get Character chat message for first time chat
     * @author baohq
     * @date   2017-11-08
     * @param  Request    $request [description]
     * @return [type]              [description]
     */
    public function getFirstTimeChat(Request $request)
    {
        try {
            $param['char_id']   = $request->get('char_id', '');
            // Check param value
            $this->checkParam($param);

            $data = Characters::where('id', $param['char_id'])
                                ->selectRaw('id, viewdate_start, viewdate_end, char_name, icon_img, first_chat_text_1, first_chat_text_2')
                                ->first();

            // Replace media URL on 2018-03-01 by baohq (task #9967)
            $data = $this->formatMediaOfObject($data, ['icon_img']);

            return $this->_jsonOK($data);

        } catch (Exception $e) {

            return $this->_jsonCatchException($e->getMessage());
        }
    }
}

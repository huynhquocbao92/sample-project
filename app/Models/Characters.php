<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Elasticquent\ElasticquentTrait;
use Illuminate\Http\Request;
use Validator;
use Auth;

/**
* Character Model
* @author baohq
* @date   2017-10-04
 */
class Characters extends Model
{
    use ElasticquentTrait;

    protected $table    = 'characters';
    protected $fillable = ['name'];

    /**
     * Set object data from request
     * @param  [type]     $item
     * @param  [type]     $request
     * @param  integer    $flag (1: create, 2: update)
     */
    public static function setInputParam($item, $request, $flag=1)
    {
        $item->viewdate_start               = $request->get('viewdate_start', '');
        $item->viewdate_end                 = $request->get('viewdate_end', '');
        $item->char_name                    = $request->get('char_name', '');
        $item->icon_img                     = $request->get('icon_img', '');
        $item->chat_miss_message            = $request->get('chat_miss_message', '');
        $item->alert_set_message            = $request->get('alert_set_message', '');
        $item->alert_set_miss_message       = $request->get('alert_set_miss_message', '');
        $item->alert_cancel_message         = $request->get('alert_cancel_message', '');
        $item->first_chat_text_1            = $request->get('first_chat_text_1', '');
        $item->first_chat_text_2            = $request->get('first_chat_text_2', '');
        // keyword_flag: (1): have not keyword unlock, (2): have keyword unlock
        $item->keyword_flag                 = $request->get('keyword_flag', 1);
        $item->keyword                      = trim($request->get('keyword', ''));
        // chat_status: (1): chat available, (2): chat not available
        $item->chat_status                  = $request->get('chat_status', 2);
        if($flag == 1) {
            $item->ins_id                   = Auth::user()->id;
        }
        else {
            $item->up_id                    = Auth::user()->id;
        }
    }

    /**
     * Set object data from confirm object
     * @param  [type]     $item
     * @param  [type]     $obj
     * @param  integer    $flag (1: create, 2: update)
     */
    public static function setInputParamFromObject($item, $obj, $flag=1)
    {
        $item->viewdate_start               = $obj->viewdate_start;
        $item->viewdate_end                 = $obj->viewdate_end;
        $item->char_name                    = $obj->char_name;
        $item->icon_img                     = $obj->icon_img;
        $item->chat_miss_message            = $obj->chat_miss_message;
        $item->alert_set_message            = $obj->alert_set_message;
        $item->alert_set_miss_message       = $obj->alert_set_miss_message;
        $item->alert_cancel_message         = $obj->alert_cancel_message;
        $item->first_chat_text_1            = $obj->first_chat_text_1;
        $item->first_chat_text_2            = $obj->first_chat_text_2;
        $item->keyword_flag                 = $obj->keyword_flag;
        $item->keyword                      = trim($obj->keyword);
        $item->chat_status                  = $obj->chat_status;
        if($flag == 1) {
            $item->ins_id                   = Auth::user()->id;
        }
        else {
            $item->up_id                    = Auth::user()->id;
        }
    }

    /**
     * Find item field by id
     * @author baohq
     * @date   2017-10-12
     * @param  [type]     $id
     * @param  [type]     $field
     * @return [type]
     */
    public static function findField($id, $field)
    {
        $data = Characters::where('id', $id)
                            ->where('is_deleted', 0)
                            ->pluck($field)
                            ->first();
        return $data;
    }

    /**
     * Get character data list use for select
     * @author baohq
     * @date   2017-10-12
     * @param  [type]     $id
     * @param  [type]     $field
     * @return [type]
     */
    public static function getSelectList()
    {
        $data = Characters::where('is_deleted', 0)
                            ->select('id', 'char_name')
                            ->orderBy('id', 'asc')
                            ->get();

        $characterList = array();
        // Add data into characterList with key => value
        if(!is_null($data)) {
            foreach ($data as $item) {
                $characterList[$item->id] = $item->char_name;
            }
        }

        return $characterList;
    }

    /**
     * Get reaction_word_ field list
     * @author baohq
     * @date   2017-10-31
     * @return array    reaction_word_list
     */
    public static function getReactionWordList()
    {
        $reactionWord = array();

        for($i = 1; $i <= 10; $i++) {
            array_push($reactionWord, 'reaction_word_'.$i);
        }

        return $reactionWord;
    }
}

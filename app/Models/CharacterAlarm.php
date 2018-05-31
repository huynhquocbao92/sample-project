<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Validator;
use Auth;

class CharacterAlarm extends Model
{
    protected $table = 'character_alarm';

    /**
     * Set object data from request
     * @param  [type]     $item
     * @param  [type]     $request
     * @param  integer    $flag (1: create, 2: update)
     */
    public static function setInputParam($charId, $item, $request, $flag=1)
    {
        $item->viewdate_start  = $request->get('viewdate_start', '');
        $item->viewdate_end    = $request->get('viewdate_end', '');
        $item->char_id         = $charId;
        $item->tel_name        = $request->get('tel_name', '');
        $item->char_img        = $request->get('char_img', '');
        $item->voice_data      = $request->get('voice_data', '');
        if($flag == 1) {
            $item->ins_id      = Auth::user()->id;
        }
        else {
            $item->up_id       = Auth::user()->id;
        }
    }

    /**
     * Set object data from confirm object
     * @param  [type]     $item
     * @param  [type]     $obj
     * @param  integer    $flag (1: create, 2: update)
     */
    public static function setInputParamFromObject($charId, $item, $obj, $flag=1)
    {
        $item->viewdate_start  = $obj->viewdate_start;
        $item->viewdate_end    = $obj->viewdate_end;
        $item->char_id         = $charId;
        $item->tel_name        = $obj->tel_name;
        $item->char_img        = $obj->char_img;
        $item->voice_data      = $obj->voice_data;
        if($flag == 1) {
            $item->ins_id      = Auth::user()->id;
        }
        else {
            $item->up_id       = Auth::user()->id;
        }
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;
use Validator;
use Auth;

class Notice extends Model
{
    protected $table = 'notice';

    /**
     * Set object data from request
     * @param  [type]     $item
     * @param  [type]     $request
     * @param  integer    $flag (1: create, 2: update)
     */
    public static function setInputParam($item, $request, $flag=1)
    {
        $item->viewdate          = $request->get('viewdate', '');
        $item->char_id           = $request->get('char_id', '');
        // 1：無し (false)、2：有り (true)
        $item->response_tel_flag = $request->get('response_tel_flag', 1);

        if ($item->response_tel_flag == 1) {
            $item->char_tel_id   = 0;
        } else {
            $item->char_tel_id   = $request->get('char_tel_id', 0);
        }

        $item->comment           = $request->get('comment', '');
        $item->url               = $request->get('url', '');
        $item->picture           = $request->get('picture', '');

        if ($flag == 1) {
            $item->ins_id        = Auth::user()->id;
        } else {
            $item->up_id         = Auth::user()->id;
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
        $item->viewdate          = $obj->viewdate;
        $item->char_id           = $obj->char_id;
        $item->char_tel_id       = $obj->char_tel_id;
        $item->response_tel_flag = $obj->response_tel_flag;
        $item->comment           = $obj->comment;
        $item->url               = $obj->url;
        $item->picture           = $obj->picture;

        if($flag == 1) {
            $item->ins_id        = Auth::user()->id;
        } else {
            $item->up_id         = Auth::user()->id;
        }
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Elasticquent\ElasticquentTrait;
use Illuminate\Http\Request;
use Validator;
use Auth;

class CharacterChat extends Model
{
    use ElasticquentTrait;

    protected $table = 'character_chat';

    /**
     * Set object data from request
     * @param  [type]     $item
     * @param  [type]     $request
     * @param  integer    $flag (1: create, 2: update)
     */
    public static function setInputParam($charId, $item, $request, $flag=1)
    {
        $item->viewdate_start           = $request->get('viewdate_start', '');
        $item->viewdate_end             = $request->get('viewdate_end', '');
        $item->char_id                  = $charId;
        $item->char_tel_id              = $request->get('char_tel_id', '');
        $item->class                    = $request->get('class', ''); // 1：通常応答、2：疑問応答、3：特典応答

        for($i = 1; $i <= 10; $i++) {
            $reactionWord               = 'reaction_word_'.$i;
            $item->$reactionWord        = $request->get($reactionWord, '');
        }

        $item->response_tel_flag        = $request->get('response_tel_flag', 1); // 1：無し、2：有り
        $item->response_img             = $request->get('response_img', '');

        for($i = 1; $i <= 5; $i++) {
            $response1                  = 'response_text_'.$i.'_1';
            $response2                  = 'response_text_'.$i.'_2';

            $item->$response1           = $request->get($response1, '');
            $item->$response2           = $request->get($response2, '');
        }

        if($flag == 1) {
            $item->ins_id               = Auth::user()->id;
        }
        else {
            $item->up_id                = Auth::user()->id;
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
        $item->viewdate_start           = $obj->viewdate_start;
        $item->viewdate_end             = $obj->viewdate_end;
        $item->char_id                  = $charId;
        $item->char_tel_id              = $obj->char_tel_id;
        $item->class                    = $obj->class; // 1：通常応答、2：疑問応答、3：特典応答

        for($i = 1; $i <= 10; $i++) {
            $reactionWord               = 'reaction_word_'.$i;
            $item->$reactionWord        = $obj->$reactionWord;
        }

        $item->response_tel_flag        = $obj->response_tel_flag; // 1：無し、2：有り
        $item->response_img             = $obj->response_img;

        for($i = 1; $i <= 5; $i++) {
            $response1                  = 'response_text_'.$i.'_1';
            $response2                  = 'response_text_'.$i.'_2';

            $item->$response1           = $obj->$response1;
            $item->$response2           = $obj->$response2;
        }

        if($flag == 1) {
            $item->ins_id               = Auth::user()->id;
        }
        else {
            $item->up_id                = Auth::user()->id;
        }
    }

    /**
     * Define class value
     * @author baohq
     * @date   2017-10-13
     * @return $class
     */
    public static function getClass($id='')
    {
        // 1：通常応答、2：疑問応答、3：特典応答
        $class[1] = trans('label.response_normal');
        $class[2] = trans('label.response_question');
        $class[3] = trans('label.response_reward');

        if($id == '') {
            return $class;
        }

        return $class[$id];
    }

    /**
     * Define Response tel flag value
     * @author baohq
     * @date   2017-10-16
     * @return $data
     */
    public static function getResponseTelFlag($id='')
    {
        // 1：無し、2：有り
        $data[1] = trans('label.response_tel_no');
        $data[2] = trans('label.response_tel_yes');

        if($id == '') {
            return $data;
        }

        return $data[$id];
    }
}

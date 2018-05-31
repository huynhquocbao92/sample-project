<?php

namespace App\Http\Controllers\Api\v12;

use Illuminate\Http\Request;
use Illuminate\Routing\Controller as BaseController;
use App\AppTraits\SettingTrait;
use App\Models\Notice;
use App\Libs\DateTimeHelper;
use App\Libs\UserHelper;
use App\Libs\ApiAuth;
use Validator;
use Hash;
use DB;

/**
 * Notice controller
 * @author baohq
 * @date   2017-10-11
 */
class NoticeController extends ApiBaseController
{
    public function getNotice(Request $request)
    {
        try {
            $param['char_id']   = $request->get('char_id', '');
            $page               = $request->get('page', 1);
            $offSet             = ($page - 1) * $this->gridPageSize;

            // Check param value
            $this->checkParam($param);

            // Get notice data
            $data = DB::table('notice as n')
                        ->leftJoin('character_tel as ctl', 'ctl.id', '=', 'n.char_tel_id')
                        ->where('n.viewdate', '<=', DateTimeHelper::dateNow())
                        ->where('n.char_id', $param['char_id'])
                        ->where('n.status', 1)
                        ->where('n.is_deleted', 0)
                        ->select('n.id', 'n.viewdate', 'n.comment', 'n.url', 'n.picture', 'n.status', 'n.response_tel_flag', 'n.char_tel_id', 'ctl.tel_name', 'ctl.char_img', 'ctl.voice_data')
                        ->orderBy('n.viewdate', 'asc')
                        ->orderBy('n.id', 'asc')
                        ->skip($offSet)
                        ->take($this->gridPageSize)
                        ->get();

            if(is_null($data)) {

                return $this->_jsonNotFound();
            }

            // Replace media URL on 2018-03-01 by baohq (task #9967)
            $data = $this->formatMediaOfArray($data, ['picture', 'char_img', 'voice_data']);

            return $this->_jsonOK($data);

        } catch (Exception $e) {

            return $this->_jsonCatchException($e->getMessage());
        }
    }
}

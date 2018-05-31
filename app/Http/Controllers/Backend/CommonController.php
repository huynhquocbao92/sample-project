<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Libs\DateTimeHelper;
use App\Models\CharacterTel;
use Auth;
use DB;

/**
 * Common Controller
 * @author baohq
 * @date   2017-10-05
 */
class CommonController extends Controller
{
	/**
	 * Return common js function
	 * @param  Request    $request
	 * @return response
	 */
	public function commonJs(Request $request)
	{
		$contents = view()->make('backend._partials.js.js-common');
		$response = response()->make($contents);

		$response->header('Content-Type', 'application/javascript');
		return $response;
	}

	/**
	 * Return popup js function
	 * @param  Request    $request
	 * @return response
	 */
	public function popupJs(Request $request)
	{
		$contents = view()->make('backend._partials.js.js-popup');
		$response = response()->make($contents);

		$response->header('Content-Type', 'application/javascript');
		return $response;
	}

	/**
	 * Ajax delete
	 * @param  Request    $request
	 * @return flag
	 */
	public function ajaxDelete(Request $request)
	{
		try {
			if($request->ajax()) {
				$id 	= $request->get('id', '');
				$table 	= $request->get('table', '');
				$flag 	= 0;

				if($id != '' || $table != '') {
					$flag = DB::table($table)->where('id', $id)
												->update(array(
													'is_deleted' 	=> 1,
													'up_id'		 	=> Auth::user()->id,
													'updated_at'	=> DateTimeHelper::dateNow()
												));
				}

				return $flag;
			}
		} catch (Exception $e) {
			echo $e->getMessage();
		}
	}

	/**
	 * Ajax change status
	 * @param  Request    $request
	 * @return flag
	 */
	public function ajaxChangeStatus(Request $request)
	{
		try {
			if($request->ajax()) {
				$id 	= $request->get('id', '');
				$table 	= $request->get('table', '');
				$status = $request->get('status', 1);
				$flag 	= 0;

				if($id != '' || $table != '') {
					$flag = DB::table($table)->where('id', $id)
												->update(array(
													'status' 		=> $status == 1 ? 0 : 1,
													'up_id'		 	=> Auth::user()->id,
													'updated_at'	=> DateTimeHelper::dateNow()
												));
				}

				return $flag;
			}
		} catch (Exception $e) {
			echo $e->getMessage();
		}
	}

	/**
	 * Get Character Tel List by char_id
	 * @author baohq
	 * @date   2017-11-01
	 * @param  Request    $request [description]
	 * @return [type]              [description]
	 */
	public function ajaxCharacterTelList(Request $request)
	{
		try {
			$charId  = $request->get('char_id', '');
			$charTel = array();
			$charTel = CharacterTel::getCharTelList($charId);

			return view('backend._partials.js-character-tel')->with('data', $charTel);
		} catch (Exception $e) {
			echo $e->getMessage();
		}
	}
}

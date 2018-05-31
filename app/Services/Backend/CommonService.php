<?php
namespace App\Services\Backend;

use Illuminate\Http\Request;
use App\Libs\DateTimeHelper;
use App\Models\UserFriend;
use App\Models\Users;
use Auth;
use DB;

class CommonService extends BaseService
{
	// Define action type
    protected $_actionDraft;
    protected $_actionConfirm;

    public function __construct()
    {
        $this->_actionDraft     = config('site.action_draft', 'draft_save');
        $this->_actionConfirm   = config('site.action_confirm', 'confirm');
    }

	/**
	 * Store data with action type Draft Save OR Confirm
	 * @author baohq
	 * @date   2017-10-17
	 * @param  [type]     $request
	 * @param  [type]     $object
	 * @param  [type]     $returnView
	 * @param  array      $confirmParam
	 * @param  array      $finishParam
	 * @return view
	 */
	public function storeData($request, $object, $returnView, $confirmParam=[], $finishParam=[])
	{
		try {
			// Get form submit action (draft save OR confirm)
            $action     = $request->get('action');

            if($action == $this->_actionDraft) {
                // Draft save data with status is private
                $object->status = 0;
                $object->save();
				// Merge finish param with object
				$returnArr = array_merge($finishParam, ['object' => $object, 'alert' => trans('message.created_success')]);
				// Return to finish view
                return view($returnView.'.finish', $returnArr);
            }
            else {
                // Show data in confirm page
                $request->merge(array_map('trim', $request->all()));
                // Put request value into sesion
 			    $request->flash();
                // Merge confirm param with object
				$returnArr = array_merge($confirmParam, ['object' => $object]);
				// Return to confirm view
				return view($returnView.'.confirm', $returnArr);
            }
		} catch (Exception $e) {
			echo $e->getMessage();
		}
	}

    /**
	 * Update data with action type Draft Save OR Confirm
	 * @author baohq
	 * @date   2017-10-17
	 * @param  [type]     $request
	 * @param  [type]     $object
	 * @param  [type]     $returnView
	 * @param  array      $confirmParam
	 * @param  array      $finishParam
	 * @return view
	 */
	public function updateData($request, $object, $returnView, $confirmParam=[], $finishParam=[])
	{
		try {
			// Get form submit action (draft save OR confirm)
            $action     = $request->get('action');

            if($action == $this->_actionDraft) {
                // Draft save data with status is private
                $object->status = 0;
                $object->save();
				// Merge finish param with object
				$returnArr = array_merge($finishParam, ['object' => $object, 'alert' => trans('message.updated_success')]);
				// Return to finish view
                return view($returnView.'.finish', $returnArr);
            }
            else {
                // Show data in confirm page
                $request->merge(array_map('trim', $request->all()));
                // Put request value into sesion
 			    $request->flash();
                // Merge confirm param with object
				$returnArr = array_merge($confirmParam, ['object' => $object]);
				// Return to confirm view
				return view($returnView.'.confirm', $returnArr);
            }
		} catch (Exception $e) {
			echo $e->getMessage();
		}
	}

	/**
	 * Delete item record by id
	 * @author baohq
	 * @date   2017-10-17
	 * @param  [type]     $request
	 * @param  [type]     $table
	 * @param  [type]     $id
	 * @param  [type]     $returnRoute
	 * @return view
	 */
	public function delete($request, $table, $id, $returnRoute, $returnParam=[])
	{
		try {
			if($request->isMethod('post')) {
                $flag = DB::table($table)
							->where('id', $id)
                            ->update(array(
                                'is_deleted' 	=> 1,
                                'up_id'		 	=> Auth::user()->id,
                                'updated_at'	=> DateTimeHelper::dateNow()
                            ));

                if($flag == 0) {

                    return back()->withInput()->withErrors(trans('message.deleted_failed'));
                }

                return redirect()->route($returnRoute, $returnParam)->with('alert', trans('message.deleted_success'));
            }
		} catch (Exception $e) {
			echo $e->getMessage();
		}
	}

    /**
     * Add User friend
     * @author baohq
     * @date   2017-12-04
     * @param  [type]     $character [description]
     */
    public static function addFriendForUser($character)
    {
        try {
            $count = 0;
            // Check if character is not setting keyword
            if (!is_null($character) && $character->keyword_flag == 1) {
                // Get all user
                $userList = Users::where('is_deleted', 0)->pluck('id');
                if (count($userList) > 0) {
                    foreach ($userList as $user) {
                        if(!UserFriend::isExist($user, $character->id)) {
                            $userFriend = new UserFriend();
                            $userFriend->user_id        = $user;
                            $userFriend->character_id   = $character->id;
                            $userFriend->save();
                            $count ++;
                        }
                    }
                }
            }
            return $count;
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
}

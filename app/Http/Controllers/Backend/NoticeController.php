<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Backend\Controller;
use App\Services\Backend\CommonService;
use Illuminate\Support\Facades\View;
use App\Http\Requests\StoreNotice;
use Illuminate\Http\Request;
use App\Libs\DateTimeHelper;
use App\Models\Characters;
use App\Models\Notice;
use App\Libs\Helper;
use Auth;
use DB;

/**
 * Notice Controller
 * @author baohq
 * @date   2017-10-11
 */
class NoticeController extends Controller
{
    private $_returnView = 'backend.notice.';

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            $page   = $request->get('page', 1);
            $offSet = ($page - 1) * $this->_gridPageSize;

            $data = DB::table('notice as n')
                        ->leftJoin('characters as c', 'c.id', '=', 'n.char_id')
                        ->where('n.is_deleted', 0)
                        ->select('n.id', 'n.viewdate', 'n.comment', 'n.url', 'n.picture', 'n.status', 'n.response_tel_flag', 'n.char_id', 'c.char_name', 'n.updated_at as updated_at')
                        ->orderBy($this->_orderBy, $this->_sortType)
                        ->skip($offSet)
                        ->take($this->_gridPageSize)
                        ->paginate($this->_gridPageSize);

            return View::make($this->_returnView.'list', compact('data'));

        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        try {
            // Get character list
            $characterList = Characters::getSelectList();

            return View::make($this->_returnView.'create', compact('characterList'));

        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StoreNotice $request)
    {
        try {
            // Get form submit action (draft save OR confirm)
            $action    = $request->get('action');
            // Create new notice object
            $notice    = new Notice();
            // Set param value from request
            Notice::setInputParam($notice, $request);

            if($action == 'draft_save') {
                // Draft save data with status is private
                $notice->status = 0;
                $notice->save();

                return view($this->_returnView.'finish')->with('id', $notice->id)
                                                        ->with('alert', trans('message.created_success'));
            }
            else {
                // Show data in confirm page
                $request->merge(array_map('trim', $request->all()));
                // Put request value into sesion
 			    $request->flash();
                // Get character name
                $notice->char_name = Characters::findField($notice->char_id, 'char_name');

                return view($this->_returnView.'confirm')->with('noticeObj', $notice);
            }

        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * Store data with status is public
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function publicStore(Request $request)
    {
        try {
            // Get object data from confirm view
            $noticeObj = json_decode($request->get('notice_obj'));

            if(!empty($noticeObj)) {
                $notice          = new Notice();
                Notice::setInputParamFromObject($notice, $noticeObj);
                $notice->status  = 1;
                $notice->save();

                return view($this->_returnView.'finish')->with('id', $notice->id)
                                                        ->with('alert', trans('message.created_success'));
            }

            return back()->with('alert', trans('message.data_error'));

        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function edit($id)
    {
        try {
            $characterList  = Characters::getSelectList();
            $data           = Notice::find($id);

            if(!empty($data)) {

                return View::make($this->_returnView.'edit', compact('data', 'characterList'));
            }

            return back()->with('alert', trans('message.data_error'));

        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function update(StoreNotice $request, $id)
    {
        try {
            // Get form submit action
            $action     = $request->get('action');
            // Find notice data
            $notice  = Notice::find($id);
            // Set param value from request
            Notice::setInputParam($notice, $request, 2);

            if($action == 'draft_save') {
                // Draft save data with status is private
                $notice->status = 0;
                $notice->save();

                return view($this->_returnView.'finish')->with('id', $notice->id)
                                                        ->with('alert', trans('message.updated_success'));
            }
            else {
                // Show data in confirm page
                $request->merge(array_map('trim', $request->all()));
                // Put request value into sesion
			    $request->flash();
                // Get character name
                $notice->char_name = Characters::findField($notice->char_id, 'char_name');

                return view($this->_returnView.'confirm')->with('noticeObj', $notice);
            }

        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * Update data with status is public
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function publicUpdate(Request $request, $id)
    {
        try {
            // Get object data from confirm view
            $noticeObj = json_decode($request->get('notice_obj'));

            if(!empty($noticeObj)) {
                $notice  = Notice::find($id);
                Notice::setInputParamFromObject($notice, $noticeObj, 2);
                $notice->status  = 1;
                $notice->save();

                return view($this->_returnView.'finish')->with('id', $notice->id)
                                                        ->with('alert', trans('message.updated_success'));
            }

            return back()->with('alert', trans('message.data_error'));

        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(Request $request, $id)
    {
        try {
            $common         = new CommonService();

            // Define param for delete function
            $table          = 'notice';
            $returnRoute    = Helper::getBePrefix().'notice';

            // Call delete from Common Service
            return $common->delete($request, $table, $id, $returnRoute);

        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
}

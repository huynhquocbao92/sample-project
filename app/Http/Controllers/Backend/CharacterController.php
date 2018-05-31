<?php

namespace App\Http\Controllers\Backend;

use App\Http\Controllers\Backend\Controller;
// use App\Services\Backend\ElasticSearchService;
use App\Services\Backend\CommonService;
use App\Http\Requests\StoreCharacters;
use Illuminate\Support\Facades\View;
use App\Libs\DateTimeHelper;
use Illuminate\Http\Request;
use App\Models\Characters;
use App\Models\CharacterChat;
use App\Models\CharacterTel;
use App\Models\CharacterAlarm;
use App\Libs\Helper;
use Auth;

/**
 * Character Controller
 * @author baohq
 * @date   2017-10-04
 */
class CharacterController extends Controller
{
    private $_returnView = 'backend.characters.';

    /**
     * Display a listing of character.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        try {
            /**
             * @param _gridPageSize, _orderBy, _sortType
             * @desc  call from Backend\Controller __construct
             */
            $page           = $request->get('page', 1);
            $offSet         = ($page - 1) * $this->_gridPageSize;
            $chatStatus     = $request->get('chat_status', '');
            $keywordFlag    = $request->get('keyword_flag', '');
            $keyword        = $request->get('keyword', '');

            // Define query
            $query = Characters::where('is_deleted', 0);

            // Query with chat_status
            if(strlen($chatStatus) > 0 && ($chatStatus != 0)) {
                $query = $query->where('chat_status', $chatStatus);
            }
            // Query with keyword_flag
            if(strlen($keywordFlag) > 0 && ($keywordFlag != 0)) {
                $query = $query->where('keyword_flag', $keywordFlag);
            }
            // Query with keyword
            if(strlen($keyword) > 0) {
                $query = $query->where(function ($queryFunction) use ($keyword) {
                    $queryFunction->where('keyword', 'like', '%'.$keyword.'%')
                                    ->orWhere('char_name', 'like', '%'.$keyword.'%');
                });
            }

            $data = $query->orderBy($this->_orderBy, $this->_sortType)
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
            return view($this->_returnView.'create');

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
    public function store(StoreCharacters $request)
    {
        try {
            // Get form submit action (draft save OR confirm)
            $action     = $request->get('action');
            // Create new character object
            $character  = new Characters();
            // Set param value from request
            Characters::setInputParam($character, $request);

            if($action == $this->_actionDraft) {
                // Draft save data with status is private
                $character->status = 0;
                $character->save();

                // Add user friend if character not setting keyword
                // Comment out on 20180327 because performance problem => will add friend when App call API get-friend 
                // CommonService::addFriendForUser($character);
                // Index into Elasticsearch
                // ElasticSearchService::indexCharacter($character->id);

                return view($this->_returnView.'finish')->with('id', $character->id)
                                                        ->with('alert', trans('message.created_success'));
            }
            else {
                // Show data in confirm page
                $request->merge(array_map('trim', $request->all()));
                // Put request value into sesion
			    $request->flash();

                return view($this->_returnView.'confirm')->with('characterObj', $character);
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
            $characterObj = json_decode($request->get('character_obj'));

            if(!empty($characterObj)) {
                $character          = new Characters();
                Characters::setInputParamFromObject($character, $characterObj);
                $character->status  = 1;
                $character->save();

                // Add user friend if character not setting keyword
                // Comment out on 20180327 because performance problem => will add friend when App call API get-friend
                // CommonService::addFriendForUser($character);

                // Index into Elasticsearch
                // ElasticSearchService::indexCharacter($character->id);

                return view($this->_returnView.'finish')->with('id', $character->id)
                                                        ->with('alert', trans('message.created_success'));
            }

            return back()->with('alert', trans('message.data_error'));

        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function show($id)
    {
        try {
            $data = Characters::find($id);

            if(!empty($data)) {

                return View::make($this->_returnView.'detail', compact('data'));
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
            $data = Characters::find($id);

            if(!empty($data)) {

                return View::make($this->_returnView.'edit', compact('data'));
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
    public function update(StoreCharacters $request, $id)
    {
        try {
            // Get form submit action
            $action     = $request->get('action');
            // Create new character object
            $character  = Characters::find($id);
            // Set param value from request
            Characters::setInputParam($character, $request, 2);

            if($action == $this->_actionDraft) {
                // Draft save data with status is private
                $character->status = 0;
                $character->save();

                // Index into Elasticsearch
                // ElasticSearchService::indexCharacter($character->id);

                return view($this->_returnView.'finish')->with('id', $character->id)
                                                        ->with('alert', trans('message.updated_success'));
            }
            else {
                // Show data in confirm page
                $request->merge(array_map('trim', $request->all()));
                // Put request value into sesion
			    $request->flash();

                return view($this->_returnView.'confirm')->with('characterObj', $character);
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
            $characterObj = json_decode($request->get('character_obj'));

            if(!empty($characterObj)) {
                $character  = Characters::find($id);
                Characters::setInputParamFromObject($character, $characterObj, 2);
                $character->status  = 1;
                $character->save();

                // Index into Elasticsearch
                // ElasticSearchService::indexCharacter($character->id);

                return view($this->_returnView.'finish')->with('id', $character->id)
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
            if($request->isMethod('post')) {
                $flag = Characters::where('id', $id)
                                    ->update(array(
                                        'is_deleted' 	=> 1,
                                        'up_id'		 	=> Auth::user()->id,
                                        'updated_at'	=> DateTimeHelper::dateNow()
                                    ));

                if($flag == 0) {
                    return back()->withInput()->withErrors(trans('message.deleted_failed'));
                }

                // Update into Elasticsearch
                // ElasticSearchService::indexCharacter($id);

                // Update Chacracter Chat
                CharacterChat::where('char_id', $id)
                                ->update(array(
                                    'is_deleted' 	=> 1,
                                    'up_id'		 	=> Auth::user()->id,
                                    'updated_at'	=> DateTimeHelper::dateNow()
                                ));
                // Update Chacracter Tel
                CharacterTel::where('char_id', $id)
                                ->update(array(
                                    'is_deleted' 	=> 1,
                                    'up_id'		 	=> Auth::user()->id,
                                    'updated_at'	=> DateTimeHelper::dateNow()
                                ));
                // Update Chacracter Alarm
                CharacterAlarm::where('char_id', $id)
                                ->update(array(
                                    'is_deleted' 	=> 1,
                                    'up_id'		 	=> Auth::user()->id,
                                    'updated_at'	=> DateTimeHelper::dateNow()
                                ));

                return redirect()->route(Helper::getBePrefix().'character')->with('alert', trans('message.deleted_success'));
            }

        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }

    /**
     * Add user friend manual
     * @author baohq
     * @date   2017-12-04
     * @param  [type]     $id [description]
     */
    public function addUserFriendManual($id)
    {
        try {
            $character  = Characters::find($id);
            $count      = CommonService::addFriendForUser($character);
            dd('addUserFriendManual DONE: '.$count);
        } catch (Exception $e) {
            echo $e->getMessage();
        }
    }
}

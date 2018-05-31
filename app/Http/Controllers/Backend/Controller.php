<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\AppTraits\SettingTrait;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;

abstract class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests, SettingTrait;

    // $auth;
    protected $_gridPageSize;
    protected $_orderBy;
    protected $_sortType;
    protected $_actionDraft;
    protected $_actionConfirm;

    public function __construct(Request $request)
    {
        // $this->auth = $auth;
        $this->_gridPageSize    = config('site.grid_page_size', 25);
        $this->_orderBy         = $request->get('sort_field', config('site.order_by', 'updated_at'));
        $this->_sortType        = $request->get('sort_type', config('site.sort_type', 'desc'));
        $this->_actionDraft     = config('site.action_draft', 'draft_save');
        $this->_actionConfirm   = config('site.action_confirm', 'confirm');
    }

}

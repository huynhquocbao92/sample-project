<?php

namespace App\Http\Controllers\Frontend;

use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Routing\Controller as BaseController;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use App\AppTraits\SettingTrait;
use Illuminate\Contracts\Auth\Guard;

abstract class Controller extends BaseController
{
    use AuthorizesRequests, DispatchesJobs, ValidatesRequests, SettingTrait;
    protected $auth;
    public function __construct(Guard $auth)
    {
        $this->auth = $auth;
    }
}

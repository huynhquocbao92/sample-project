<?php

namespace App\Http\Middleware;

use Illuminate\Contracts\Auth\Guard;
use App\AppTraits\SettingTrait;
use App\Libs\ApiAuth;
use App\Libs\UserHelper;
use Closure;

class ApiAuthenticate
{
    /**
     * The Guard implementation.
     *
     * @var Guard
     */
    protected $auth     = null;
    protected $userObj  = null;
    protected $user     = null;

    /**
     * Create a new filter instance.
     *
     * @param  Guard  $auth
     * @return void
     */
    public function __construct(Guard $auth)
    {
        $apiAuth        = new ApiAuth();
        $this->userObj  = new UserHelper();
        $this->user     = $apiAuth->auth($this->userObj);
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @return mixed
     */
    public function handle($request, Closure $next)
    {
        return $next($request);
    }
}

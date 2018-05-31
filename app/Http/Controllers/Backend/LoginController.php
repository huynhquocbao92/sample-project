<?php

namespace App\Http\Controllers\Backend;

use Illuminate\Foundation\Auth\AuthenticatesUsers;
use Illuminate\Contracts\Auth\Guard;
use Illuminate\Http\Request;
use App\Models\AdminUsers;
use App\Libs\Helper;
use DateTime;
use Validator;
use Auth;

/**
* Backend Login Controller
* @author baohq
* @date   2017-10-04
 */
class LoginController extends Controller
{
    use AuthenticatesUsers;

    protected $_loginPath           = '';
    protected $_redirectTo          = '';
    protected $_redirectAfterLogout = '';
    protected $_maxLoginAttempts    = 5;
    protected $_resetKeyDuration    = 24;  // 24 hours

    /**
     * construct
     * @author baohq
     * @date   2017-10-04
     * @param  Guard      $auth
     * @param  Request    $request
     */
    function __construct(Guard $auth, Request $request)
    {
        // parent::__construct($auth);
        // Define path URL
        $this->_redirectTo          = Helper::getBeUrl();
        $this->_loginPath           = Helper::getBeUrl('login');
        $this->_redirectAfterLogout = Helper::getBeUrl('login');
    }

    /**
    * Override the username method used to validate login
    *
    * @return string
    */
    public function username()
    {
        return 'login_id';
    }

    /**
     * show login view
     * @author baohq
     * @date   2017-10-04
     * @return view
     */
    public function showLoginForm()
    {
        return view('backend.admin-users.login');
    }

    /**
     * Check login account
     * @author baohq
     * @date   2017-10-04
     * @param  Request    $request
     * @return response
     */
    public function postLogin(Request $request)
	{
		$loginId  = $request->get('login_id', '');
		$password = $request->get('password', '');
        // Check validate login_id and password
        $validator = $this->validateLogin($request);

        if($validator->fails()) {
            return redirect()->intended($this->_loginPath)
                                ->withErrors($validator->errors());
        }

        // Check login_id and password data
		if(Auth::attempt(['login_id' => $loginId, 'password' => $password])) {
            // If account is not activated will back with error
			if(Auth::user()->status == 0) {
				return redirect()->intended($this->_loginPath)
                                    ->withErrors(array('login' => trans('auth.failed')));
			}

            // Update last login time
			Auth::user()->last_login = new DateTime();
			Auth::user()->save();
            // Redirect to home
			return redirect()->intended($this->_redirectTo);
		}
		return redirect()->intended($this->_loginPath)
                            ->withErrors(array('login' => trans('auth.failed')));
	}

    /**
	 * Log the user out of the application.
	 *
	 * @param \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\Response
	 */
	public function logout(Request $request)
	{
        // Update last logout time
		Auth::user()->last_logout = new DateTime();
		Auth::user()->save();
        // Clear session
		$this->guard()->logout();
		$request->session()->flush();
		$request->session()->regenerate();

		return redirect($this->_redirectAfterLogout);
	}

    /**
     * Check validation
     * @param  [type]     $request
     * @return [type]     $validator
     */
    public function validateLogin($request)
    {
        $validator = Validator::make($request->all(), $this->_loginRules());
        $validator->setAttributeNames($this->_setAttributeName());
        return $validator;
    }

    /**
     * Login rules
     * @param  array      $merge
     * @return array      $rule
     */
    private function _loginRules($merge = [])
    {
        return array_merge(
            [
                'login_id'  => 'required',
                'password'  => 'required'
            ], $merge);
    }

    /**
     * Login rules
     * @param  array      $merge
     * @return array      $attribute
     */
    private function _setAttributeName($merge = [])
    {
        return array_merge(
            [
                'login_id'  => trans('label.login_id'),
                'password'  => trans('label.password')
            ], $merge);
    }

	/** Overide
	 * Get the failed login response instance.
	 *
	 * @param  \Illuminate\Http\Request  $request
	 * @return \Illuminate\Http\RedirectResponse
	 */
	protected function sendFailedLoginResponse(Request $request) {
		$errors = [$this->username() => trans('auth.failed')];

		if ($request->expectsJson()) {
			return response()->json($errors, 422);
		}

		return redirect()->back()
                            ->withInput($request->only($this->username(), 'remember'))
                            ->withErrors($errors);
	}
}

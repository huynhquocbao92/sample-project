<?php
namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;
use App\Libs\Helper;

class HomeController extends Controller
{
    public function index()
    {
        // return view('backend.home');
        return redirect()->route(Helper::getBePrefix().'notice');
    }
}

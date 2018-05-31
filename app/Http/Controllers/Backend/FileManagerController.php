<?php
namespace App\Http\Controllers\Backend;

use Illuminate\Http\Request;

class FileManagerController extends Controller
{
    public function index()
    {
        return view('backend.setting.file-manager.list');
    }
}

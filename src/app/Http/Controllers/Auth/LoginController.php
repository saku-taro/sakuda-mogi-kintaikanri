<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class LoginController extends Controller
{
    public function showEmployeeLogin()
    {
        return view('employee.auth.login');
    }

    public function showAdminLogin()
    {
        return view('admin.auth.login');
    }
}

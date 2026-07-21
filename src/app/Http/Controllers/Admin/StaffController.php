<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class StaffController extends Controller
{
    public function index()
    {
        // スタッフの一覧を取得（必要に応じてページネーションや絞り込み）
        $staffs = User::all();

        return view('admin.staff-list', compact('staffs'));
    }
}

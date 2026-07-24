<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\LoginRequest;
use Laravel\Fortify\Http\Controllers\AuthenticatedSessionController;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

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

    public function store(LoginRequest $request)
    {
        // $response = app(AuthenticatedSessionController::class)->store($request);

        // if ($request->is('admin/login')) {
        //     return redirect()->route('admin.index');
        // }

        // return $response;

        // リクエストのパスが admin を含むかどうかで管理者か一般かを判定
        $isAdmin = $request->is('admin*');

        // ログイン情報の取得
        $credentials = $request->only('email', 'password');

        // デフォルトのガード（web）で認証を試行する
        if (Auth::attempt($credentials, $request->boolean('remember'))) {
            $request->session()->regenerate();

            // 成功時のリダイレクト先
            return $isAdmin
                ? redirect()->route('admin.index')
                : redirect()->intended('/attendance');
        }

        // 失敗時：バリデーションエラーを発生させて元の画面に戻す
        throw ValidationException::withMessages([
            'email' => [trans('auth.failed')],
        ]);
    }

    public function destroy(Request $request)
    {
        // ログアウトする直前のURLを取得（管理者か判定）
        $isAdmin = str_contains($request->header('referer'), 'admin');

        Auth::guard('web')->logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        // 判定結果に基づいてリダイレクト
        return $isAdmin ? redirect('/admin/login') : redirect('/login');
    }
}

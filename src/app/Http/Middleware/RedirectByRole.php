<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

use App\Http\Controllers\Admin\AdminAttendanceRequestController;

class RedirectByRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure(\Illuminate\Http\Request): (\Illuminate\Http\Response|\Illuminate\Http\RedirectResponse)  $next
     * @return \Illuminate\Http\Response|\Illuminate\Http\RedirectResponse
     */
    public function handle(Request $request, Closure $next)
    {
        $user = $request->user();

        // 管理者の場合かつクエリパラメータに from=admin がある場合
        if ($user->admin_status && $request->query('from') === 'admin') {
            // ルートのアクションを管理者用コントローラーに差し替える
            $route = $request->route();
            $route->setAction(array_merge($route->getAction(), [
                'uses' => AdminAttendanceRequestController::class . '@index',
                'controller' => AdminAttendanceRequestController::class . '@index',
            ]));

            // コントローラーのバインディングをリセットして再設定させる
            $route->controller = null;
        }

        // 一般ユーザーの場合はそのまま次の処理（一般用コントローラー）へ通す
        return $next($request);
    }
}

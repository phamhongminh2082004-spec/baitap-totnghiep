<?php

/**
 * MIDDLEWARE chặn mọi người không phải quản trị viên khỏi khu vực /admin (yêu cầu nhóm D).
 */

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    public function handle(Request $request, Closure $next): Response
    {
        // Không phải admin -> trả lỗi 403 (hiện trang errors/403.blade.php).
        abort_unless($request->user()?->isAdmin(), 403);

        return $next($request);
    }
}

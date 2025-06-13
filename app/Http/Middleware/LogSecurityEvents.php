<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class LogSecurityEvents
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Логируем подозрительную активность
        $this->logSuspiciousActivity($request, $response);

        return $response;
    }

    private function logSuspiciousActivity(Request $request, Response $response): void
    {
        $statusCode = $response->getStatusCode();
        $userAgent = $request->userAgent();
        $ip = $request->ip();
        $path = $request->path();

        // Логируем 403 и 401 ошибки
        if (in_array($statusCode, [401, 403])) {
            Log::warning('Попытка несанкционированного доступа', [
                'ip' => $ip,
                'user_agent' => $userAgent,
                'path' => $path,
                'method' => $request->method(),
                'status_code' => $statusCode,
                'user_id' => Auth::guard('employees')->id(),
                'session_id' => $request->session()->getId(),
            ]);
        }

        // Логируем подозрительные User-Agent
        if ($this->isSuspiciousUserAgent($userAgent)) {
            Log::warning('Подозрительный User-Agent', [
                'ip' => $ip,
                'user_agent' => $userAgent,
                'path' => $path,
                'method' => $request->method(),
            ]);
        }

        // Логируем попытки доступа к админским путям
        if ($this->isAdminPath($path) && !Auth::guard('employees')->check()) {
            Log::warning('Попытка доступа к админской зоне без авторизации', [
                'ip' => $ip,
                'user_agent' => $userAgent,
                'path' => $path,
                'method' => $request->method(),
            ]);
        }

        // Логируем подозрительные параметры запроса
        if ($this->hasSuspiciousParameters($request)) {
            Log::warning('Подозрительные параметры в запросе', [
                'ip' => $ip,
                'user_agent' => $userAgent,
                'path' => $path,
                'method' => $request->method(),
                'parameters' => $request->all(),
            ]);
        }
    }

    private function isSuspiciousUserAgent(?string $userAgent): bool
    {
        if (!$userAgent) {
            return true;
        }

        $suspiciousPatterns = [
            'sqlmap',
            'nmap',
            'nikto',
            'masscan',
            'zap',
            'scanner',
            'bot',
            'crawler',
            'python-requests',
            'curl',
            'wget'
        ];

        foreach ($suspiciousPatterns as $pattern) {
            if (stripos($userAgent, $pattern) !== false) {
                return true;
            }
        }

        return false;
    }

    private function isAdminPath(string $path): bool
    {
        $adminPaths = [
            'employee',
            'admin',
            'dashboard',
            'management'
        ];

        foreach ($adminPaths as $adminPath) {
            if (str_starts_with($path, $adminPath)) {
                return true;
            }
        }

        return false;
    }

    private function hasSuspiciousParameters(Request $request): bool
    {
        $suspiciousPatterns = [
            'union',
            'select',
            'drop',
            'insert',
            'update',
            'delete',
            'script',
            'javascript:',
            'vbscript:',
            'onload',
            'onerror',
            '<script',
            '</script>',
            'eval(',
            'base64_decode',
            'file_get_contents'
        ];

        $allInput = $request->all();
        $inputString = strtolower(json_encode($allInput));

        foreach ($suspiciousPatterns as $pattern) {
            if (strpos($inputString, strtolower($pattern)) !== false) {
                return true;
            }
        }

        return false;
    }
}

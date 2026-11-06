<?php
namespace App\Http\Middleware;
use App\Models\AdminActivityLog;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
class RecordAdminActivity
{
    public function handle(Request $request, Closure $next): Response
    {
        $before = $request->user();
        $response = $next($request);
        if (!$request->isMethod('GET') && !$request->isMethod('HEAD') && $request->is('admin/*')) {
            $route = $request->route();
            $name = $route?->getName() ?? '';
            if (!in_array($name, ['admin.forgot.submit','admin.reset.submit'], true)) {
                $after = $request->user();
                $actor = $after ?? $before;
                // Don't retain passwords, payloads, tokens or reset URLs in an audit record.
                if ($actor && $response->getStatusCode() < 400) {
                    $parts = explode('.', $name);
                    $module = $parts[1] ?? 'admin';
                    $action = $name === 'admin.login.submit' ? 'login' : ($name === 'admin.logout' ? 'logout' : end($parts));
                    try {
                        AdminActivityLog::create([
                            'actor_id'=>$actor->id,
                            'actor_name'=>$actor->name,
                            'action'=>$action,
                            'module'=>$module,
                            'method'=>$request->method(),
                            'path'=>substr($request->path(),0,250),
                            'ip_address'=>$request->ip(),
                            'user_agent'=>substr((string) $request->userAgent(),0,255),
                            'status_code'=>$response->getStatusCode(),
                            'created_at'=>now(),
                        ]);
                    } catch (\Throwable $exception) {
                        // Logging must never break a completed request (e.g. before migration).
                        report($exception);
                    }
                }
            }
        }
        return $response;
    }
}

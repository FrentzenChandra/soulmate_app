<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class ApiMenuLogger
{
    /**
     * Fields that should be masked in the payload log for security.
     */
    protected array $maskedFields = [
        'password',
        'password_confirmation',
        'current_password',
        'token',
        'access_token',
        'pin',
        'secret',
        'api_key',
        'credit_card',
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $startTime = microtime(true);

        /** @var Response $response */
        $response = $next($request);

        $duration = round((microtime(true) - $startTime) * 1000);

        $this->writeLog($request, $response, $duration);

        return $response;
    }

    /**
     * Determine menu name from request path/referer and write single-line log.
     */
    protected function writeLog(Request $request, Response $response, float $duration): void
    {
        $path = trim($request->path(), '/');

        // Ignore static assets, images, and health checks
        if ($this->shouldSkip($path)) {
            return;
        }

        $menu = $this->resolveMenuName($request, $path);
        $status = $response->getStatusCode();
        $method = $request->method();
        $displayPath = '/' . $path;

        $user = $request->user();
        $userInfo = $user ? "User: {$user->id} ({$user->name})" : 'User: Guest';
        $ip = $request->ip();

        $parts = [
            "{$status} | {$method} {$displayPath}",
            $userInfo,
            "IP: {$ip}",
            "{$duration}ms",
        ];

        // Format payload cleanly (special handling for Livewire calls to keep logs clean)
        $payloadSummary = $this->formatPayloadSummary($request, $path);
        if (!empty($payloadSummary)) {
            $parts[] = 'Payload: ' . $payloadSummary;
        }

        // If client error or server error, extract error message if available
        if ($status >= 400 && method_exists($response, 'getContent')) {
            $content = json_decode((string) $response->getContent(), true);
            if (is_array($content)) {
                if (!empty($content['errors'])) {
                    $parts[] = 'Error: ' . json_encode($content['errors'], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                } elseif (!empty($content['message'])) {
                    $parts[] = 'Error: ' . $content['message'];
                }
            }
        }

        $logLine = implode(' | ', $parts);

        // Determine log level
        $level = match (true) {
            $status >= 500 => 'error',
            $status >= 400 => 'warning',
            default => 'info',
        };

        // File format: storage/logs/menu-{menu}-YYYY-MM-DD.log
        $logFilename = "menu-{$menu}.log";
        $logDir = storage_path('logs');

        try {
            if (!is_dir($logDir)) {
                @mkdir($logDir, 0777, true);
            }

            $retentionDays = (int) env('LOG_MENU_DAYS', 5);

            Log::build([
                'driver' => 'daily',
                'path' => "{$logDir}/{$logFilename}",
                'days' => $retentionDays,
                'permission' => 0666,
            ])->$level($logLine);

            // Also if route is API, write to api-{menu}.log for backward compatibility
            if (str_starts_with($path, 'api/')) {
                Log::build([
                    'driver' => 'daily',
                    'path' => "{$logDir}/api-{$menu}.log",
                    'days' => $retentionDays,
                    'permission' => 0666,
                ])->$level($logLine);
            }
        } catch (\Throwable $e) {
            // Failsafe: Never allow logging failure to crash user requests
        }
    }

    /**
     * Resolve menu identifier from request path, Livewire component, or Referer.
     */
    protected function resolveMenuName(Request $request, string $path): string
    {
        // 1. Livewire AJAX Requests (/livewire/update, /livewire/upload-file)
        if (str_starts_with($path, 'livewire/')) {
            // Check Referer header (e.g. http://localhost/servis/masuk -> 'servis-masuk')
            $referer = $request->header('Referer');
            if ($referer) {
                $refererPath = trim((string) parse_url($referer, PHP_URL_PATH), '/');
                $menu = $this->extractMenuFromPath($refererPath);
                if ($menu !== 'general') {
                    return $menu;
                }
            }

            // Fallback: Check component name from Livewire payload
            $components = $request->input('components', []);
            if (!empty($components) && is_array($components)) {
                $firstComp = $components[0] ?? [];
                $memoName = $firstComp['snapshot'] ?? ($firstComp['memo']['name'] ?? '');
                if ($memoName) {
                    $cleaned = strtolower(str_replace(['App\\Livewire\\', '\\', '.'], ['', '/', '-'], $memoName));
                    return $this->extractMenuFromPath($cleaned);
                }
            }
        }

        return $this->extractMenuFromPath($path);
    }

    /**
     * Extract menu slug from route path segments.
     */
    protected function extractMenuFromPath(string $path): string
    {
        $segments = explode('/', trim($path, '/'));

        // If path starts with "api", strip it
        if (isset($segments[0]) && $segments[0] === 'api') {
            array_shift($segments);
        }

        if (empty($segments) || empty($segments[0])) {
            return 'general';
        }

        $root = strtolower($segments[0]);

        // Hierarchical sub-menu grouping
        if (in_array($root, ['master', 'servis', 'laporan', 'kasir', 'cabang', 'transaksi'], true) && isset($segments[1])) {
            $sub = preg_replace('/[^a-z0-9_-]/', '', strtolower($segments[1]));
            return "{$root}-{$sub}";
        }

        return preg_replace('/[^a-z0-9_-]/', '', $root) ?: 'general';
    }

    /**
     * Format payload summary cleanly.
     */
    protected function formatPayloadSummary(Request $request, string $path): string
    {
        // Special format for Livewire: show calls and updates cleanly
        if (str_starts_with($path, 'livewire/')) {
            $components = $request->input('components', []);
            if (!empty($components) && is_array($components)) {
                $summary = [];
                foreach ($components as $comp) {
                    if (!empty($comp['updates'])) {
                        $summary['updates'] = $this->sanitizePayload($comp['updates']);
                    }
                    if (!empty($comp['calls'])) {
                        $summary['calls'] = $this->sanitizePayload($comp['calls']);
                    }
                }
                if (!empty($summary)) {
                    return json_encode($summary, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
                }
            }
            return '';
        }

        $payload = $this->sanitizePayload($request->all());
        if (!empty($payload)) {
            return json_encode($payload, JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);
        }

        return '';
    }

    /**
     * Mask sensitive fields in data array recursively.
     */
    protected function sanitizePayload(array $data): array
    {
        foreach ($data as $key => $value) {
            if (in_array($key, $this->maskedFields, true)) {
                $data[$key] = '********';
            } elseif (is_array($value)) {
                $data[$key] = $this->sanitizePayload($value);
            }
        }

        return $data;
    }

    /**
     * Check if request path should be skipped from logging.
     */
    protected function shouldSkip(string $path): bool
    {
        return str_starts_with($path, 'build/') ||
               str_starts_with($path, 'storage/') ||
               str_starts_with($path, '_debugbar') ||
               str_ends_with($path, '.js') ||
               str_ends_with($path, '.css') ||
               str_ends_with($path, '.ico') ||
               str_ends_with($path, '.png') ||
               str_ends_with($path, '.jpg') ||
               str_ends_with($path, '.jpeg') ||
               str_ends_with($path, '.svg') ||
               str_ends_with($path, '.woff') ||
               str_ends_with($path, '.woff2') ||
               $path === 'up';
    }
}

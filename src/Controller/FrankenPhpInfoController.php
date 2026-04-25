<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

class FrankenPhpInfoController extends AbstractController
{
    #[Route('/', name: 'frankenphp_home', methods: ['GET'])]
    public function home(Request $request): Response
    {
        return $this->render('frankenphp/index.html.twig', [
            'info' => $this->collect($request),
        ]);
    }

    #[Route('/api/frankenphp', name: 'frankenphp_api', methods: ['GET'])]
    public function api(Request $request): JsonResponse
    {
        return new JsonResponse($this->collect($request));
    }

    private function collect(Request $request): array
    {
        $frankenVersion = phpversion('frankenphp') ?: null;
        $sapi = PHP_SAPI;
        $workerConfig = $_ENV['FRANKENPHP_CONFIG'] ?? getenv('FRANKENPHP_CONFIG') ?: null;
        $workerMode = $workerConfig !== null && str_contains($workerConfig, 'worker');

        $opcache = function_exists('opcache_get_status')
            ? @opcache_get_status(false)
            : null;

        $extensions = [];
        foreach (
            [
                'opcache',
                'intl',
                'pdo_pgsql',
                'pdo_mysql',
                'zip',
                'mbstring',
                'sodium',
                'curl',
                'apcu',
                'gd',
            ] as $ext
        ) {
            $extensions[$ext] = extension_loaded($ext);
        }

        $envKeys = [
            'APP_ENV',
            'APP_DEBUG',
            'SERVER_NAME',
            'FRANKENPHP_CONFIG',
            'FRANKENPHP_NO_DEPRECATION_WARNINGS',
            'PLOYDOK_BUILD_ID',
        ];
        $envExposed = [];
        foreach ($envKeys as $k) {
            $envExposed[$k] = $_ENV[$k] ?? getenv($k) ?: null;
        }

        return [
            'frankenphp' => [
                'version' => $frankenVersion,
                'detected' => $sapi === 'frankenphp' || $frankenVersion !== null,
                'sapi' => $sapi,
                'worker_mode' => $workerMode,
                'worker_script' => $workerMode
                    ? trim(str_replace('worker', '', (string) $workerConfig))
                    : null,
            ],
            'php' => [
                'version' => PHP_VERSION,
                'memory_limit' => ini_get('memory_limit'),
                'opcache_enabled' => (bool) ini_get('opcache.enable'),
                'opcache_jit' => ini_get('opcache.jit') ?: null,
                'opcache_jit_buffer_size' => ini_get('opcache.jit_buffer_size') ?: null,
                'opcache_memory_consumption' => ini_get('opcache.memory_consumption') ?: null,
                'opcache_validate_timestamps' => (bool) ini_get('opcache.validate_timestamps'),
                'opcache_runtime' => $opcache !== null && isset($opcache['memory_usage']) ? [
                    'used_mb' => round($opcache['memory_usage']['used_memory'] / 1048576, 2),
                    'free_mb' => round($opcache['memory_usage']['free_memory'] / 1048576, 2),
                    'cached_scripts' => $opcache['opcache_statistics']['num_cached_scripts'] ?? null,
                    'hits' => $opcache['opcache_statistics']['hits'] ?? null,
                    'misses' => $opcache['opcache_statistics']['misses'] ?? null,
                    'hit_rate' => isset($opcache['opcache_statistics']['opcache_hit_rate'])
                        ? round($opcache['opcache_statistics']['opcache_hit_rate'], 2)
                        : null,
                ] : null,
            ],
            'extensions' => $extensions,
            'request' => [
                'scheme' => $request->getScheme(),
                'http_version' => $request->server->get('SERVER_PROTOCOL'),
                'server_software' => $request->server->get('SERVER_SOFTWARE'),
                'remote_ip' => $request->getClientIp(),
                'host' => $request->getHost(),
                'method' => $request->getMethod(),
                'path' => $request->getPathInfo(),
            ],
            'env' => $envExposed,
            'symfony' => [
                'env' => $this->getParameter('kernel.environment'),
                'debug' => $this->getParameter('kernel.debug'),
                'project_dir' => $this->getParameter('kernel.project_dir'),
            ],
        ];
    }
}

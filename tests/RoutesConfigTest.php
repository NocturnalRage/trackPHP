<?php
namespace TrackPHP\Tests;

use PHPUnit\Framework\TestCase;
use TrackPHP\Router\Router;

final class RoutesConfigTest extends TestCase
{
    public function test_routes_file_returns_router_and_home_route_dispatches(): void
    {
        require __DIR__ . '/../vendor/autoload.php';
        $router = require __DIR__ . '/../config/routes.php';

        $this->assertInstanceOf(Router::class, $router);

        $html = $router->dispatch('GET', '/');
        $this->assertStringContainsString('<h1>👋 Welcome to TrackPHP!</h1>', $html);
    }
}


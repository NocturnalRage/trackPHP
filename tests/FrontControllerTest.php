<?php
namespace TrackPHP\Tests;

use PHPUnit\Framework\TestCase;

final class FrontControllerTest extends TestCase
{
    public function test_index_php_outputs_home_page(): void
    {
        $_SERVER['REQUEST_METHOD']  = 'GET';
        $_SERVER['REQUEST_URI']     = '/';

        ob_start();
        require __DIR__ . '/../public/index.php';
        $out = ob_get_clean();

        $this->assertStringContainsString('<h1>👋 Welcome to TrackPHP!</h1>', $out);
    }
}

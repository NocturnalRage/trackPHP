<?php

namespace TrackPHP\Tests\Router;

use PHPUnit\Framework\TestCase;
use TrackPHP\Router\Router;
use TrackPHP\Router\Route;

final class RouterTest extends TestCase
{
    public function test_it_registers_a_get_route_with_no_params(): void
    {
        $router = new Router();
        $router->get('/home', 'home#index');

        $route = $router->match('GET', '/home');

        $this->assertInstanceOf(Route::class, $route);
        $this->assertSame('GET', $route->method);
        $this->assertSame('/home', $route->pattern);
        $this->assertSame('#^/home$#', $route->regexPattern);
        $this->assertSame('HomeController', $route->controller);
        $this->assertSame('index', $route->action);
        $this->assertSame([], $route->params);
    }

    public function test_it_matches_route_with_single_parameter(): void
    {
        $router = new Router();
        $router->get('/posts/{postId}', 'posts#show');

        $route = $router->match('GET', '/posts/42');

        $this->assertInstanceOf(Route::class, $route);
        $this->assertSame('GET', $route->method);
        $this->assertSame('/posts/{postId}', $route->pattern);
        $this->assertSame('#^/posts/([^/]+)$#', $route->regexPattern);
        $this->assertSame('PostsController', $route->controller);
        $this->assertSame('show', $route->action);
        $this->assertSame(['postId' => '42'], $route->params);
    }

    public function test_it_matches_dynamic_route_with_multiple_params(): void
    {
        $router = new Router();
        $router->get('/posts/{postId}/comments/{commentId}', 'comments#show');

        $route = $router->match('GET', '/posts/42/comments/56');

        $this->assertInstanceOf(Route::class, $route);
        $this->assertSame('GET', $route->method);
        $this->assertSame('/posts/{postId}/comments/{commentId}', $route->pattern);
        $this->assertSame('#^/posts/([^/]+)/comments/([^/]+)$#', $route->regexPattern);
        $this->assertSame('CommentsController', $route->controller);
        $this->assertSame('show', $route->action);
        $this->assertSame([
            'postId' => '42',
            'commentId' => '56'
        ], $route->params);
    }

    public function test_it_returns_null_for_unmatched_pattern(): void
    {
        $router = new Router();
        $router->get('/about', 'pages#about');

        $this->assertNull($router->match('GET', '/not-found'));
    }

    public function test_it_distinguishes_between_http_methods(): void
    {
        $router = new Router();
        $router->get('/login', 'auth#form');
        $router->post('/login', 'auth#submit');

        $getRoute = $router->match('GET', '/login');
        $postRoute = $router->match('POST', '/login');

        $this->assertSame('form', $getRoute->action);
        $this->assertSame('submit', $postRoute->action);
    }

    public function test_it_throws_exception_for_invalid_handler_string(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        $router = new Router();
        $router->get('/bad', 'missingSeparator');
    }

    public function test_it_treats_trailing_slash_as_different_pattern(): void
    {
        $router = new Router();
        $router->get('/about', 'pages#about');

        $this->assertNull($router->match('GET', '/about/')); // if strict
    }

    public function test_it_matches_first_route_on_duplicate_pattern(): void
    {
        $router = new Router();
        $router->get('/home', 'pages#first');
        $router->get('/home', 'pages#second');

        $route = $router->match('GET', '/home');

        $this->assertSame('first', $route->action);
    }

    public function test_it_handles_root_route(): void
    {
        $r = new Router();
        $r->get('/', 'home#index');

        $route = $r->match('GET', '/');

        $this->assertInstanceOf(Route::class, $route);
        $this->assertSame('HomeController', $route->controller);
        $this->assertSame('index', $route->action);
        $this->assertSame('#^/$#', $route->regexPattern);
    }

    public function test_it_returns_null_for_post_when_only_get_is_registered(): void
    {
        $r = new Router();
        $r->get('/login', 'auth#form');

        $this->assertNull($r->match('POST', '/login'));
    }

    public function test_it_returns_null_for_get_when_only_post_is_registered(): void
    {
        $r = new Router();
        $r->post('/login', 'auth#submit');

        $this->assertNull($r->match('GET', '/login'));
    }

    public function test_it_returns_null_for_completely_unsupported_method(): void
    {
        $r = new Router();
        $r->get('/anything', 'pages#show');

        $this->assertNull($r->match('PUT', '/anything'));
    }

    public function test_it_does_not_match_when_single_param_is_empty(): void
    {
        $r = new Router();
        $r->get('/posts/{id}', 'posts#show');

        $this->assertNull($r->match('GET', '/posts/'));   // missing id
        $this->assertNull($r->match('GET', '/posts//'));   // missing id with extra slash
    }

    public function test_it_does_not_match_when_any_multi_param_is_empty(): void
    {
        $r = new Router();
        $r->get('/posts/{postId}/comments/{commentId}', 'comments#show');

        $this->assertNull($r->match('GET', '/posts//comments/56'));   // missing postId
        $this->assertNull($r->match('GET', '/posts/42/comments/'));   // missing commentId
        $this->assertNull($r->match('GET', '/posts/42/comments//'));   // missing commentId with ending slash
    }
}


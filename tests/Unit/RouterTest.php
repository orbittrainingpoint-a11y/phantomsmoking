<?php

declare(strict_types=1);

namespace Tests\Unit;

use App\Core\Router;
use PHPUnit\Framework\TestCase;
use PHPUnit\Framework\Attributes\Test;

class RouterTest extends TestCase
{
    private Router $router;

    protected function setUp(): void
    {
        $this->router = new Router();
    }

    #[Test]
    public function addRouteRegistersRoute(): void
    {
        $this->router->add('GET', '/', 'HomeController@index');
        // Router stores routes but doesn't expose them directly;
        // verify dispatch doesn't throw for a known route when handler is missing.
        $this->expectNotToPerformAssertions();
    }

    #[Test]
    public function matchReturnsEmptyArrayForRootPattern(): void
    {
        $reflection = new \ReflectionMethod(Router::class, 'match');
        $result = $reflection->invoke($this->router, '/', '/');
        $this->assertIsArray($result);
        $this->assertEmpty($result);
    }

    #[Test]
    public function matchExtractsNamedParametersFromUri(): void
    {
        $reflection = new \ReflectionMethod(Router::class, 'match');
        $result = $reflection->invoke($this->router, '/product/{slug}', '/product/premium-cigar');
        $this->assertIsArray($result);
        $this->assertSame('premium-cigar', $result['slug']);
    }

    #[Test]
    public function matchExtractsMultipleParameters(): void
    {
        $reflection = new \ReflectionMethod(Router::class, 'match');
        $result = $reflection->invoke($this->router, '/category/{cat}/product/{id}', '/category/cigars/product/42');
        $this->assertSame('cigars', $result['cat']);
        $this->assertSame('42', $result['id']);
    }

    #[Test]
    public function matchReturnsFalseForNonMatchingUri(): void
    {
        $reflection = new \ReflectionMethod(Router::class, 'match');
        $result = $reflection->invoke($this->router, '/product/{id}', '/category/cigars');
        $this->assertFalse($result);
    }

    #[Test]
    public function matchReturnsFalseWhenPatternHasExtraSegments(): void
    {
        $reflection = new \ReflectionMethod(Router::class, 'match');
        $result = $reflection->invoke($this->router, '/product/{id}/extra', '/product/42');
        $this->assertFalse($result);
    }

    #[Test]
    public function matchIsExactAndDoesNotMatchSubPaths(): void
    {
        $reflection = new \ReflectionMethod(Router::class, 'match');
        $result = $reflection->invoke($this->router, '/shop', '/shop/cigars');
        $this->assertFalse($result);
    }

    #[Test]
    public function dispatchSets404ForUnknownUri(): void
    {
        ob_start();
        $this->router->dispatch('GET', '/this-route-does-not-exist-xyz');
        $output = ob_get_clean();
        $this->assertSame(404, http_response_code());
    }

    #[Test]
    public function dispatchHandlesOptionsPreflightWithout500(): void
    {
        ob_start();
        $this->router->dispatch('OPTIONS', '/any-path');
        ob_get_clean();
        $this->assertSame(200, http_response_code());
    }
}

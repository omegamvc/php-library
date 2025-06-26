<?php

/**
 * Part of Omega - Tests\Testing Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Tests\Testing\Traits;

use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;
use Omega\Http\Response;
use Omega\Testing\TestResponse;

/**
 * Class ResponseStatusTest
 *
 * This test class verifies the correctness of the HTTP response status assertions
 * provided by the {@see ResponseStatusTrait}, as used within the {@see TestResponse} class.
 *
 * It covers standard HTTP response codes such as OK (200), Created (201), No Content (204),
 * Bad Request (400), Unauthorized (401), Forbidden (403), Not Found (404), and Method Not Allowed (405),
 * ensuring that the corresponding assertion methods behave as expected.
 *
 * @category   Omega\Tests
 * @package    Testing
 * @subpackage Traits
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html GPL V3.0+
 * @version    2.0.0
 */
#[CoversClass(Response::class)]
#[CoversClass(TestResponse::class)]
class ResponseStatusTest extends TestCase
{
    /**
     * Test it can test response assert ok.
     *
     * @return void
     */
    public function testItCanTestResponseAssertOk(): void
    {
        $response = new TestResponse(new Response('test', 200, []));

        $response->assertOk();
    }

    /**
     * Test it can test response assert create.
     *
     * @return void
     */
    public function testItCanTestResponseAssertCreate(): void
    {
        $response = new TestResponse(new Response('test', 201, []));

        $response->assertCreated();
    }

    /**
     * Test it can test response assert no content.
     *
     * @return void
     */
    public function testItCanTestResponseAssertNoContent(): void
    {
        $response = new TestResponse(new Response('', 204, []));

        $response->assertNoContent();
    }

    /**
     * Test it can test response assert bad request.
     *
     * @return void
     */
    public function testItCanTestResponseAssertBadRequest(): void
    {
        $response = new TestResponse(new Response('', 400, []));

        $response->assertBadRequest();
    }

    /**
     * Test it can test response assert unauthorized.
     *
     * @return void
     */
    public function testItCanTestResponseAssertUnauthorized(): void
    {
        $response = new TestResponse(new Response('', 401, []));

        $response->assertUnauthorized();
    }

    /**
     * Test it can test response assert not forbidden.
     *
     * @return void
     */
    public function testItCanTestResponseAssertForbidden(): void
    {
        $response = new TestResponse(new Response('', 403, []));

        $response->assertForbidden();
    }

    /**
     * Test it can test response assert not found.
     *
     * @return void
     */
    public function testItCanTestResponseAssertNotFound(): void
    {
        $response = new TestResponse(new Response('', 404, []));

        $response->assertNotFound();
    }

    /**
     * Test it can test response assert not allowed.
     *
     * @return void
     */
    public function testItCanTestResponseAssertNotAllowed(): void
    {
        $response = new TestResponse(new Response('', 404, []));

        $response->assertNotFound();
    }
}

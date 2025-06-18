<?php

/**
 * Part of Omega - Tests\Http Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Tests\Http;

use Exception;
use Omega\Http\JsonResponse;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

use function json_decode;
use function ob_get_clean;
use function ob_start;

use const JSON_FORCE_OBJECT;
use const JSON_HEX_AMP;
use const JSON_HEX_APOS;
use const JSON_HEX_QUOT;
use const JSON_HEX_TAG;

/**
 * Unit tests for the JsonResponse class.
 *
 * These tests verify the correct JSON encoding, content rendering,
 * error handling for invalid data, and encoding options management
 * of the JsonResponse HTTP response class.
 *
 * @category   Omega
 * @package    Tests
 * @subpackage Http
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html GPL V3.0+
 * @version    2.0.0
 */
#[CoversClass(JsonResponse::class)]
class JsonResponseTest extends TestCase
{
    /**
     * Test it can render json string.
     *
     * @return void
     */
    public function testItCanRenderJsonString(): void
    {
        $response = new JsonResponse([
            'language' => 'php',
            'ver'      => 80,
        ]);

        ob_start();
        $response->send();
        $json = ob_get_clean();

        $this->assertJson($json);
        $data = json_decode($json, true);
        $this->assertEquals('{"language":"php","ver":80}', $response->getContent());
        $this->assertEquals($data, $response->getData());
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json', $response->getContentType());
    }

    /**
     * Test it will throws invalid exception.
     *
     * @return void
     */
    public function testItWillThrowsInvalidException(): void
    {
        $this->expectExceptionMessage('Invalid encode data.');
        new JsonResponse(['say' => "Hello \x80 World"]);
    }

    /**
     * Test it constructor empty creates json object.
     *
     * @return void
     */
    public function testItConstructorEmptyCreatesJsonObject(): void
    {
        $response = new JsonResponse();
        $this->assertSame('{}', $response->getContent());
    }

    /**
     * Test it constructor with array creates json array.
     *
     * @return void
     */
    public function testItConstructorWithArrayCreatesJsonArray(): void
    {
        $response = new JsonResponse([0, 1, 2, 3]);
        $this->assertSame('[0,1,2,3]', $response->getContent());
    }

    /**
     * Test it set json.
     *
     * @return void
     */
    public function testItSetJson(): void
    {
        $response = new JsonResponse();
        $response->setJson('1');
        $this->assertEquals('1', $response->getContent());

        $response = new JsonResponse();
        $response->setJson('true');
        $this->assertEquals('true', $response->getContent());
    }

    /**
     * Test it json encode flags.
     *
     * @return void
     * @throws Exception
     */
    public function testItJsonEncodeFlags(): void
    {
        $response = new JsonResponse();
        $response->setData('<>\'&"');

        $this->assertEquals('"\u003C\u003E\u0027\u0026\u0022"', $response->getContent());
    }

    /**
     * Test it get encoding options.
     *
     * @return void
     */
    public function testItGetEncodingOptions(): void
    {
        $response = new JsonResponse();

        $this->assertEquals(JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_AMP | JSON_HEX_QUOT, $response->getEncodingOptions());
    }

    /**
     * Test it can set encoding options.
     *
     * @return void
     * @throws Exception
     */
    public function testItCanSetEncodingOptions(): void
    {
        $response = new JsonResponse();
        $response->setData([[1, 2, 3]]);

        $this->assertEquals('[[1,2,3]]', $response->getContent());

        $response->setEncodingOptions(JSON_FORCE_OBJECT);

        $this->assertEquals('{"0":{"0":1,"1":2,"2":3}}', $response->getContent());
    }
}

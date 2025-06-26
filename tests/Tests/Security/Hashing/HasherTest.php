<?php

/**
 * Part of Omega - Tests\Security Package
 * php version 8.3
 *
 * @link      https://omegamvc.github.io
 * @author    Adriano Giovannini <agisoftt@gmail.com>
 * @copyright Copyright (c) 2024 - 2025 Adriano Giovannini (https://omegamvc.github.io)
 * @license   https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version   2.0.0
 */

declare(strict_types=1);

namespace Tests\Security\Hashing;

use Omega\Security\Hashing\Argon2IdHasher;
use Omega\Security\Hashing\ArgonHasher;
use Omega\Security\Hashing\BcryptHasher;
use Omega\Security\Hashing\DefaultHasher;
use PHPUnit\Framework\Attributes\CoversClass;
use PHPUnit\Framework\TestCase;

/**
 * Unit tests for individual hasher implementations.
 *
 * This test suite ensures that all supported hashers — including Default, Bcrypt, Argon, and Argon2id —
 * function correctly when generating, verifying, and validating password hashes.
 *
 * Each test verifies that:
 * - The hasher creates a secure hash that differs from the plain text input.
 * - The hash can be successfully verified against the original password.
 * - The resulting hash conforms to the expected algorithm format.
 *
 * These tests provide confidence in the security and correctness of the hashing mechanisms used
 * within the application.
 *
 * @category   Omega\Tests
 * @package    Security
 * @subpackage Hashing
 * @link       https://omegamvc.github.io
 * @author     Adriano Giovannini <agisoftt@gmail.com>
 * @copyright  Copyright (c) 2024 - 2025 Adriano Giovannini
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html GPL V3.0+
 * @version    2.0.0
 */
#[CoversClass(Argon2IdHasher::class)]
#[CoversClass(ArgonHasher::class)]
#[CoversClass(BcryptHasher::class)]
#[CoversClass(DefaultHasher::class)]
class HasherTest extends TestCase
{
    /**
     * Test it can hash default hasher.
     *
     * @return void
     */
    public function testItCanHashDefaultHasher(): void
    {
        $hasher = new DefaultHasher();
        $hash   = $hasher->make('password');
        $this->assertNotSame('password', $hash);
        $this->assertTrue($hasher->verify('password', $hash));
        $this->assertTrue($hasher->isValidAlgorithm($hash));
    }

    /**
     * Test it can hash bcrypt hasher.
     *
     * @return void
     */
    public function testItCanHashBcryptHasher(): void
    {
        $hasher = new BcryptHasher();
        $hash   = $hasher->make('password');
        $this->assertNotSame('password', $hash);
        $this->assertTrue($hasher->verify('password', $hash));
        $this->assertTrue($hasher->isValidAlgorithm($hash));
    }

    /**
     * Test it can hash argon hasher.
     *
     * @return void
     */
    public function testItCanHashArgonHasher(): void
    {
        $hasher = new ArgonHasher();
        $hash   = $hasher->make('password');
        $this->assertNotSame('password', $hash);
        $this->assertTrue($hasher->verify('password', $hash));
        $this->assertTrue($hasher->isValidAlgorithm($hash));
    }

    /**
     * Test it can hash argon 2 id hasher.
     *
     * @return void
     */
    public function testItCanHashArgon2IdHasher(): void
    {
        $hasher = new Argon2IdHasher();
        $hash   = $hasher->make('password');
        $this->assertNotSame('password', $hash);
        $this->assertTrue($hasher->verify('password', $hash));
        $this->assertTrue($hasher->isValidAlgorithm($hash));
    }
}

<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * The backend layout footer reads these straight from $_SERVER, which the
     * CLI test client does not populate.
     */
    protected function setUp(): void
    {
        parent::setUp();

        $_SERVER['SERVER_SOFTWARE'] ??= 'PHPUnit';
        $_SERVER['SERVER_NAME'] ??= 'localhost';
    }
}

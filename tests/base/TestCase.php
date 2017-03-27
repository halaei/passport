<?php

namespace PassportTests\Base;

use Illuminate\Container\Container;

class TestCase extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        parent::setUp();
    }

    public function tearDown()
    {
        parent::tearDown();
        \Mockery::close();
        Container::setInstance();
    }
}

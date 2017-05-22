<?php

declare(strict_types = 1);

namespace Sjdaws\Tests\LoyaltyCorpMailchimp;

use PHPUnit\Framework\TestCase;

/**
 * Base test class for all tests to extend
 */
class BaseTestCase extends TestCase
{
    /**
     * Setup env environment values from dotenv
     *
     * @return void
     */
    public function setUp()
    {
        // Add a fake mailchimp api key to env
        putenv('MAILCHIMP_APIKEY=12345678901234567890123456789012-us1');
    }

    /**
     * Call protected/private method of a class
     *
     * @param object $object     Instantiated object that we will run method on
     * @param string $methodName Method name to call
     * @param array  $parameters Array of parameters to pass into method
     *
     * @return mixed Method return
     */
    protected function callPrivateMethod(&$object, $methodName, array $parameters = [])
    {
        $reflection = new \ReflectionClass(get_class($object));
        $method = $reflection->getMethod($methodName);
        $method->setAccessible(true);

        return $method->invokeArgs($object, $parameters);
    }

    /**
     * Add a valid test to get rid of warning about no tests
     *
     * @return void
     */
    public function testPhpUnit()
    {
        $this->assertTrue(true);
    }
}

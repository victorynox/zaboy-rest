<?php

namespace zaboy\test\rest\DataStore\Factory;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2016-01-29 at 18:23:51.
 */
class MemoryAbstractFactoryTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Returner
     */
    protected $object;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {

    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {

    }

    public function testDataStoreMemory__invoke()
    {
        $container = include 'config/container.php';
        $this->object = $container->get('testMemory');
        $this->assertSame(
                get_class($returnedResponse = $this->object), 'zaboy\rest\DataStore\Memory'
        );
    }

}

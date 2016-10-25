<?php

namespace zaboy\test\rest\DataStore\Factory;

use zaboy\rest\DataStore\Eav\EavAbstractFactory;
use zaboy\rest\DataStore\Eav\Example\StoreCatalog;
use Interop\Container\ContainerInterface;
use zaboy\rest\DataStore\Eav\SysEntities;
use zaboy\rest\DataStore\Eav\Entity;
use zaboy\rest\DataStore\Eav\Prop;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2016-01-29 at 18:23:51.
 */
class EavAbstractFactoryTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Returner
     */
    protected $object;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->container = include './config/container.php';
        $this->object = new EavAbstractFactory();
    }

    public function test__canCreateIfConfigAbsent()
    {
        $requestedName = 'the_name_which_has_not_config';
        $result = $this->object->canCreate($this->container, $requestedName);
        $this->assertSame(
                false, $result
        );
    }

    public function test__CreateSysEntity()
    {
        $requestedName = SysEntities::TABLE_NAME;
        $result = $this->container->get($requestedName);
        $this->assertSame(
                SysEntities::class, get_class($result)
        );
    }

    public function test__CreateEntity()
    {
        $requestedName = StoreCatalog::PRODUCT_TABLE_NAME;
        $result = $this->container->get($requestedName);
        $this->assertSame(
                Entity::class, get_class($result)
        );
    }

    public function test__CreateProp()
    {
        $requestedName = StoreCatalog::PROP_LINKED_URL_TABLE_NAME;
        $result = $this->container->get($requestedName);
        $this->assertSame(
                Prop::class, get_class($result)
        );
    }

}
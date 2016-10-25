<?php

/**
 * Zaboy lib (http://zaboy.org/lib/)
 *
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\test\rest\DataStore\Eav;

use zaboy\rest\DataStore\DbTable;
use Zend\Db\Adapter\Adapter;
use Zend\Db\TableGateway\TableGateway;
use Interop\Container\ContainerInterface;
use zaboy\rest\DataStore\Eav\SysEntities;
use Xiag\Rql\Parser\Query;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2016-01-11 at 16:19:25.
 */
class SysEntitiesTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var SysEntities
     */
    protected $object;

    /** @var  ContainerInterface */
    protected $container;

    protected function setUp()
    {
        $this->container = include 'config/container.php';
        $this->object = $this->container->get(SysEntities::TABLE_NAME); //resource name is table name
        $this->object->deleteAll();
    }

    public function test__queryFromEmptyTable()
    {
        $this->object = $this->container->get(SysEntities::TABLE_NAME); //resource name is table name
        $this->assertEquals(SysEntities::class, get_class($this->object));
        $this->assertEquals([], $this->object->query(new Query()));
    }

}
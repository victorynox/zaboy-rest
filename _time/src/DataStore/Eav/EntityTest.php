<?php

/**
 * Zaboy lib (http://zaboy.org/lib/)
 *
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\test\rest\DataStore\Eav;

use Xiag\Rql\Parser\DataType\DateTime;
use Xiag\Rql\Parser\Node\Query\ScalarOperator\EqNode;
use Xiag\Rql\Parser\Node\SelectNode;
use Xiag\Rql\Parser\Node\SortNode;
use zaboy\rest\RqlParser\AggregateFunctionNode;
use zaboy\rest\DataStore\DbTable;
use Zend\Db\Adapter\Adapter;
use Zend\Db\TableGateway\TableGateway;
use Interop\Container\ContainerInterface;
use zaboy\rest\DataStore\Eav\SysEntities;
use Xiag\Rql\Parser\Query;
use zaboy\rest\DataStore\Eav\Entity;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2016-01-11 at 16:19:25.
 */
class EntityTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Entity
     */
    protected $object;

    /** @var  ContainerInterface */
    protected $container;

    protected function setUp()
    {
        $this->container = include 'config/container.php';
        $sysEntities = $this->container->get(SysEntities::TABLE_NAME);
        $sysEntities->deleteAll();
    }

    public function test__getEntityName()
    {
        $this->object = $this->container->get(SysEntities::ENTITY_PREFIX . 'product');
        $this->assertEquals('product', $this->object->getEntityName());
    }

    public function test__create()
    {
        $this->object = $this->container->get(SysEntities::ENTITY_PREFIX . 'product');
        $this->object->create([ 'title' => 'title_1', 'price' => 100]);
        $this->assertEquals(1, $this->object->count());
    }

    public function test__query()
    {
        $this->object = $this->container->get(SysEntities::ENTITY_PREFIX . 'product');
        for ($i = 1; $i < 10; $i++) {
            $data = [
                'title' => 'title_' . $i,
                'price' => 100 * $i
            ];

            $data['id'] = $this->object->create($data)['id'];
            $this->assertEquals($i, $this->object->count());

            $query = new Query();

            $query->setQuery(new EqNode('title', $data['title']));

            $result = $this->object->query($query);

            $unset = array_diff(array_keys($result[0]), array_keys($data));

            foreach ($unset as $key) {
                unset($result[0][$key]);
            }

            $this->assertEquals(true, is_array($result) && isset($result[0]));
            $this->assertEquals(1, count($result));
            $this->assertEquals($data, $result[0]);
        }
    }

    public function test__query_with_select()
    {
        $this->object = $this->container->get(SysEntities::ENTITY_PREFIX . 'product');
        for ($i = 1; $i < 10; $i++) {
            $data = [
                'title' => 'title_' . $i,
                'price' => 100 * $i
            ];

            $this->object->create($data);
            $this->assertEquals($i, $this->object->count());

            $query = new Query();

            $query->setQuery(new EqNode('title', $data['title']));
            $query->setSelect(new SelectNode(['price']));

            $result = $this->object->query($query);
            $unset = array_diff(array_keys($result[0]), array_keys($data));

            foreach ($unset as $key) {
                unset($result[0][$key]);
            }

            $this->assertEquals(1, count($result[0]));
            $this->assertEquals($data['price'], $result[0]['price']);
        }
    }

    public function test__query_with_select_aggregate()
    {
        $this->object = $this->container->get(SysEntities::ENTITY_PREFIX . 'product');
        for ($i = 1; $i < 10; $i++) {
            $data = [
                'title' => 'title_' . $i,
                'price' => 100 * $i
            ];

            $this->object->create($data);
        }
        $query = new Query();

        $query->setSelect(new SelectNode([new AggregateFunctionNode('max', 'price')]));

        $result = $this->object->query($query);
        $this->assertEquals(900, $result[0]['price->max']);
    }

    public function test__query_with_select_sys_entities_aggregate()
    {
        $this->object = $this->container->get(SysEntities::ENTITY_PREFIX . 'product');
        $time = (new DateTime())->format("Y-m-d") . " 00:00:00";
        for ($i = 1; $i < 10; $i++) {
            $data = [
                'title' => 'title_' . $i,
                'price' => 100 * $i
            ];

            $this->object->create($data);
        }
        $query = new Query();
        $query->setSelect(new SelectNode([new AggregateFunctionNode('max', 'sys_entities.add_date')]));

        $result = $this->object->query($query);
        $this->assertEquals(true, $result[0]['sys_entities.add_date->max'] >= $time);
    }

    public function test__query_with_sort()
    {
        $this->object = $this->container->get(SysEntities::ENTITY_PREFIX . 'product');
        for ($i = 1; $i < 10; $i++) {
            $data = [
                'title' => 'title_' . $i,
                'price' => 100 * $i
            ];

            $this->object->create($data);
            $this->assertEquals($i, $this->object->count());
        }

        $query = new Query();

        $query->setSort(new SortNode([SysEntities::TABLE_NAME . '.add_date' => -1]));

        $result = $this->object->query($query);

        $res = true;

        $prev = $result[0];
        if (count($result) == 1) {
            $res = true;
        }
        for ($i = 0; $i < count($result); $i++) {
            $curr = $result[$i];
            if ($prev['add_date'] < $curr['add_date']) {
                $res = false;
                break;
            }
            $prev = $curr;
        }
        $this->assertEquals(true, $res);
    }

    public function test__delete()
    {
        $this->object = $this->container->get(SysEntities::ENTITY_PREFIX . 'product');
        for ($i = 1; $i < 10; $i++) {
            $data = [
                'title' => 'title_' . $i,
                'price' => 100 * $i
            ];

            $id = $this->object->create($data)['id'];
            $this->object->delete($id);
            $this->assertEquals(null, $this->object->read($id));
        }
    }

    public function test__delete_all()
    {
        //$idArr = [];
        $this->object = $this->container->get(SysEntities::ENTITY_PREFIX . 'product');
        for ($i = 1; $i < 10; $i++) {
            $data = [
                'title' => 'title_' . $i,
                'price' => 100 * $i
            ];

            //$idArr[] = $this->object->create($data)['id'];
        }
        $this->object->deleteAll();
        $result = $this->object->query(new Query());
        $this->assertEquals(true, empty($result));
    }

}

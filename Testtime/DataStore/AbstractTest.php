<?php

/**
 * Zaboy lib (http://zaboy.org/lib/)
 *
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\test\res\DataStore;

use zaboy\rest\DataStore\DbTable;
use zaboy\rest\DataStore\DataStoreAbstract;
use Xiag\Rql\Parser\Node\Query\ScalarOperator;
use Xiag\Rql\Parser\Node\Query\LogicOperator;
use Xiag\Rql\Parser\Query;
use Xiag\Rql\Parser\Node;
use Xiag\Rql\Parser\DataType\Glob;

/**
 * Generated by PHPUnit_SkeletonGenerator 1.2.0 on 2013-08-25 at 15:44:45.
 */
abstract class AbstractTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var array
     */
    protected $_optionsDelault = array();

    /**
     * @var DbTable
     */
    protected $object;

    /**
     * @var array
     */
    protected $config;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     *
     * @var array
     */
    protected $_itemsArrayDelault = array(
        array('id' => 1, 'anotherId' => 10, 'fString' => 'val1', 'fFloat' => 400.0004),
        array('id' => 2, 'anotherId' => 20, 'fString' => 'val2', 'fFloat' => 300.003),
        array('id' => 3, 'anotherId' => 40, 'fString' => 'val2', 'fFloat' => 300.003),
        array('id' => 4, 'anotherId' => 30, 'fString' => 'val2', 'fFloat' => 100.1)
            )
    ;
    protected $_itemsArrayEnhanced = array(
        array('id' => 1, 'anotherId' => 10, 'fString' => 'val1', 'fFloat' => 400.0004, 'nll' => 1, 'abs' => 'val_abs'),
        array('id' => 2, 'anotherId' => 20, 'fString' => 'val2', 'fFloat' => 300.003, 'nll' => null),
        array('id' => 3, 'anotherId' => 40, 'fString' => 'val2', 'fFloat' => 300.003, 'nll' => null),
        array('id' => 4, 'anotherId' => 30, 'fString' => 'val2', 'fFloat' => 100.1, 'nll' => null)
            )

    ;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->container = include './config/container.php';
        $this->config = $this->container->get('config')['dataStore'];
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {

    }

    /**
     * This method init $this->object
     */
    abstract protected function _initObject($data = null);

// **************************** Identifier ************************

    public function testSetIdentifier()
    {
        $this->_initObject();
        $this->assertEquals(
                'id', $this->object->getIdentifier()
        );
    }

// *************************** Item **************************************************
    public function testRead_defaultId()
    {
        $this->_initObject();
        $this->assertEquals(
                $this->_itemsArrayDelault[2 - 1], $this->object->read(2)
        );

        $this->assertEquals(
                $this->_itemsArrayDelault['1'], $this->object->read('2')
        );
    }

    public function testHas_defaultId()
    {
        $this->_initObject();
        $this->assertTrue($this->object->has(2));
        $this->assertFalse($this->object->has(20));
    }

    public function testCreate_withoutId()
    {
        $this->_initObject();
        $newItem = $this->object->create(
                array(
                    'fFloat' => 1000.01,
                    'fString' => 'Create_withoutId_'
                )
        );
        $id = $newItem['id'];
        $insertedItem = $this->object->read($id);
        $this->assertEquals(
                'Create_withoutId_', $insertedItem['fString']
        );
        $this->assertEquals(
                1000.01, $insertedItem['fFloat']
        );
    }

    public function testCreate_withtId()
    {
        $this->_initObject();
        $newItem = $this->object->create(
                array(
                    'id' => 1000,
                    'fFloat' => 1000.01,
                    'fString' => 'Create_withId'
                )
        );
        $id = $newItem['id'];
        $insertedItem = $this->object->read($id);
        $this->assertEquals(
                'Create_withId', $insertedItem['fString']
        );
        $this->assertEquals(
                1000, $id
        );
    }

    public function testCreate_withtIdRewrite()
    {
        $this->_initObject();
        $newItem = $this->object->create(
                array(
            'id' => 2,
            'fString' => 'Create_withtIdRewrite'
                ), true
        );
        $id = $newItem['id'];
        $insertedItem = $this->object->read($id);
        $this->assertEquals(
                'Create_withtIdRewrite', $insertedItem['fString']
        );
        $this->assertEquals(
                2, $id
        );
    }

    public function testCreate_withtIdRewriteException()
    {
        $this->_initObject();
        $this->setExpectedException('\zaboy\rest\DataStore\DataStoreException');
        $this->object->create(
                array(
            'id' => 2,
            'fString' => 'Create_withtIdRewrite'
                ), false
        );
    }

    public function testUpdate_withoutId()
    {
        $this->_initObject();
        $this->setExpectedException('\zaboy\rest\DataStore\DataStoreException');
        $id = $this->object->update(
                array(
                    'fFloat' => 1000.01,
                    'fString' => 'Create_withoutId'
                )
        );
    }

    public function testUpdate_withtId_WhichPresent()
    {

        $this->_initObject();
        $row = $this->object->update(
                array(
                    'id' => 3,
                    'fString' => 'withtId_WhichPresent'
                )
        );

        $item = $this->object->read(3);
        $this->assertEquals(
                40, $item['anotherId']
        );
        $this->assertEquals(
                'withtId_WhichPresent', $item['fString']
        );
        $this->assertEquals(
                array('id' => 3, 'anotherId' => 40, 'fString' => 'withtId_WhichPresent', 'fFloat' => 300.003), $row
        );
    }

    public function testUpdate_withtId_WhichAbsent()
    {
        $this->_initObject();
        $this->setExpectedException('zaboy\rest\DataStore\DataStoreException');
        $this->object->update(
                array(
                    'id' => 1000,
                    'fFloat' => 1000.01,
                    'fString' => 'withtIdwhichAbsent'
                )
        );
    }

    public function testUpdate_withtIdwhichAbsent_ButCreateIfAbsent_True()
    {
        $this->_initObject();
        $row = $this->object->update(
                array(
            'id' => 1000,
            'fFloat' => 1000.01,
            'fString' => 'withtIdwhichAbsent'
                ), true
        );
        $item = $this->object->read(1000);
        $this->assertEquals(
                'withtIdwhichAbsent', $item['fString']
        );
        $this->assertEquals(
                array(
            'id' => 1000,
            'fFloat' => 1000.01,
            'fString' => 'withtIdwhichAbsent'
                ), $row
        );
    }

    public function testDelete_withtId_WhichAbsent()
    {
        $this->_initObject();
        $count = $this->object->delete(1000);
        $this->assertEquals(
                0, $count
        );
    }

    public function testDelete_withtId_WhichPresent()
    {
        $this->_initObject();
        $count = $this->object->delete(4);
        $this->assertEquals(
                1, $count
        );
        $this->assertNull(
                $this->object->read(4)
        );
    }

    public function testDelete_withtId_Null()
    {
        $this->_initObject();
        $this->setExpectedException('zaboy\rest\DataStore\DataStoreException');
        $count = $this->object->delete(null);
    }

    public function testDeleteAll()
    {
        $this->_initObject();
        $count = $this->object->deleteAll();
        $this->assertEquals(
                4, $count
        );
        $count = $this->object->deleteAll();
        $this->assertEquals(
                0, $count
        );
    }

    public function testCount_count4()
    {
        $this->_initObject();
        $this->assertEquals(
                4, $this->object->count()
        );
    }

    public function testCount_count0()
    {
        $this->_initObject();
        $count = $this->object->deleteAll();
        $this->assertEquals(
                0, $this->object->count()
        );
    }

//************************* Countable **********************

    public function testShouldImplementCountable()
    {
        $this->assertTrue(is_a(get_class($this), 'Countable', true));
    }

    public function testCountNull()
    {
        $this->_initObject();
        $this->assertEquals(
                4, $this->object->count()
        );
    }

    public function testCount2()
    {
        $this->_initObject();
        $count = $this->object->deleteAll();
        $this->assertEquals(
                0, $this->object->count()
        );
    }

//************************** Iterator ************************

    public function testIteratorInterfaceStepToStep()
    {
        $this->_initObject();
        $i = 0;
        foreach ($this->object as $key => $value) {
            $i = $i + 1;
            $this->assertEquals($value, $this->object->read($key));
            $this->assertEquals(
                    $this->_itemsArrayDelault[$key - 1], $value
            );

            unset($this->_itemsArrayDelault[$key - 1]);
        }
        $this->assertEquals(
                $i, $this->object->count()
        );
        $this->assertEmpty($this->_itemsArrayDelault);
    }

//************************** RQL ************************
    public function test_QueryEq()
    {
        $this->_initObject();
        $query = new Query();
        $eqNode = new ScalarOperator\EqNode(
                'fString', 'val1'
        );
        $query->setQuery($eqNode);
        $this->assertEquals(
                $this->_itemsArrayDelault[0], $this->object->query($query)[0]
        );
    }

    public function test_QueryNe()
    {
        $this->_initObject();
        $query = new Query();
        $eqNode = new ScalarOperator\NeNode(
                'fString', 'val2'
        );
        $query->setQuery($eqNode);

        $this->assertEquals(
                1, count($this->object->query($query))
        );
    }

    public function test_QueryAndNode()
    {
        $this->_initObject();
        $query = new Query();
        $endNode = new LogicOperator\AndNode(
                [
            new ScalarOperator\EqNode('id', '2'),
            new ScalarOperator\EqNode('anotherId', '20')
                ]
        );
        $query->setQuery($endNode);
        $this->assertEquals(
                $this->_itemsArrayDelault[2 - 1], $this->object->query($query)[0]
        );
    }

    public function testQuery_Empty()
    {
        $this->_initObject();
        $query = new Query();
        $eqNode = new ScalarOperator\EqNode(
                'fString', 'not_exist_value'
        );
        $query->setQuery($eqNode);
        $this->assertEquals(
                [], $this->object->query($query)
        );
    }

    public function testQuery_all()
    {
        $this->_initObject();
        $query = new Query();
        $queryArray = $this->object->query($query);
        for ($index = 0; $index < count($this->_itemsArrayDelault); $index++) {
            $this->assertEquals(
                    array_pop($this->_itemsArrayDelault), array_pop($queryArray)
            );
        }
    }

    public function testQuery_orderId()
    {
        $this->_initObject();
        $query = new Query();
        $sortNode = new Node\SortNode(['id' => '1']);
        $query->setSort($sortNode);
        $queryArray = $this->object->query($query);
        for ($index = 0; $index < count($this->_itemsArrayDelault); $index++) {
            $this->assertEquals(
                    array_pop($this->_itemsArrayDelault), array_pop($queryArray)
            );
        }
    }

    public function testQuery_orderAnotherId()
    {
        $this->_initObject();
        $query = new Query();
        $sortNode = new Node\SortNode(['anotherId' => '1']);
        $query->setSort($sortNode);
        $queryArray = $this->object->query($query);
        $this->assertEquals(
                array_pop($this->_itemsArrayDelault), $queryArray[3 - 1]
        );
        $this->assertEquals(
                array_pop($this->_itemsArrayDelault), $queryArray[4 - 1]
        );
    }

    public function testQuery_orderDesc()
    {
        $this->_initObject();
        $query = new Query();
        $sortNode = new Node\SortNode(['id' => '-1']);
        $query->setSort($sortNode);
        $queryArray = $this->object->query($query);
        $this->assertEquals(
                $this->_itemsArrayDelault[1 - 1], $queryArray[4 - 1]
        );
        $this->assertEquals(
                $this->_itemsArrayDelault[2 - 1], $queryArray[3 - 1]
        );
    }

    public function testQuery_orderCombo()
    {
        $this->_initObject();
        $query = new Query();
        $sortNode = new Node\SortNode(['fString' => '-1', 'fFloat' => 1, 'anotherId' => '-1']);
        $query->setSort($sortNode);
        $queryArray = $this->object->query($query);
        $this->assertEquals(
                $this->_itemsArrayDelault[4 - 1], $queryArray[1 - 1]
        );
        $this->assertEquals(
                $this->_itemsArrayDelault[3 - 1], $queryArray[2 - 1]
        );
        $this->assertEquals(
                $this->_itemsArrayDelault[1 - 1], $queryArray[4 - 1]
        );
    }

    public function testQuery_WhereId()
    {
        $this->_initObject();
        $query = new Query();
        $eqNode = new ScalarOperator\EqNode(
                'id', 2
        );
        $query->setQuery($eqNode);
        $queryArray = $this->object->query($query);
        $this->assertEquals(
                $this->_itemsArrayDelault[2 - 1], $queryArray[1 - 1]
        );
        $this->assertEquals(
                1, count($queryArray)
        );
    }

    public function testQuery_WhereCombo()
    {
        $this->_initObject();
        $query = new Query();
        $eqNode1 = new ScalarOperator\EqNode(
                'fString', 'val2'
        );
        $eqNode2 = new ScalarOperator\EqNode(
                'fFloat', 300.003
        );
        $endNode = new LogicOperator\AndNode([$eqNode1, $eqNode2]);
        $query->setQuery($endNode);
        $sortNode = new Node\SortNode(['id' => 1]);
        $query->setSort($sortNode);
        $queryArray = $this->object->query($query);
        $this->assertEquals(
                $this->_itemsArrayDelault[2 - 1], $queryArray[1 - 1]
        );
        $this->assertEquals(
                2, count($queryArray)
        );
    }

    public function testQuery_fildsCombo()
    {
        $this->_initObject();
        $query = new Query();
        $eqNode1 = new ScalarOperator\EqNode(
                'fString', 'val2'
        );
        $eqNode2 = new ScalarOperator\EqNode(
                'fFloat', 300.003
        );
        $endNode = new LogicOperator\AndNode([$eqNode1, $eqNode2]);
        $query->setQuery($endNode);
        $sortNode = new Node\SortNode(['id' => 1]);
        $query->setSort($sortNode);
        $selectNode = new Node\SelectNode(['fFloat']);
        $query->setSelect($selectNode);
        $queryArray = $this->object->query($query);
        $this->assertEquals(
                array('fFloat' => $this->_itemsArrayDelault[2 - 1]['fFloat']), $queryArray[1 - 1]
        );
        $this->assertEquals(
                2, count($queryArray)
        );
    }

    public function testQuery_limitCombo()
    {
        $this->_initObject();
        $query = new Query();
        $eqNode1 = new ScalarOperator\EqNode(
                'fString', 'val2'
        );
        $eqNode2 = new ScalarOperator\EqNode(
                'fFloat', 300.003
        );
        $endNode = new LogicOperator\AndNode([$eqNode1, $eqNode2]);
        $query->setQuery($endNode);
        $sortNode = new Node\SortNode(['id' => 1]);
        $query->setSort($sortNode);
        $selectNode = new Node\SelectNode(['fFloat']);
        $query->setSelect($selectNode);
        $limitNode = new Node\LimitNode(1);
        $query->setLimit($limitNode);
        $queryArray = $this->object->query($query);
        $this->assertEquals(
                array('fFloat' => $this->_itemsArrayDelault[2 - 1]['fFloat']), $queryArray[1 - 1]
        );
        $this->assertEquals(
                1, count($queryArray)
        );
    }

    public function testQuery_offsetCombo()
    {
        $this->_initObject();
        $query = new Query();
        $eqNode1 = new ScalarOperator\EqNode(
                'fString', 'val2'
        );
        $eqNode2 = new ScalarOperator\EqNode(
                'fFloat', 300.003
        );
        $endNode = new LogicOperator\AndNode([$eqNode1, $eqNode2]);
        $query->setQuery($endNode);
        $sortNode = new Node\SortNode(['id' => 1]);
        $query->setSort($sortNode);
        $selectNode = new Node\SelectNode(['fFloat']);
        $query->setSelect($selectNode);
        $limitNode = new Node\LimitNode(DataStoreAbstract::LIMIT_INFINITY, 1);

        $query->setLimit($limitNode);
        $queryArray = $this->object->query($query);
        $this->assertEquals(
                array('fFloat' => $this->_itemsArrayDelault[2 - 1]['fFloat']), $queryArray[1 - 1]
        );
        $this->assertEquals(
                1, count($queryArray)
        );
    }

    public function testQuery_limitOffsetCombo()
    {
        $this->_initObject();
        $query = new Query();
        $eqNode1 = new ScalarOperator\EqNode(
                'fString', 'val2'
        );
        $query->setQuery($eqNode1);
        $sortNode = new Node\SortNode(['id' => '1']);
        $query->setSort($sortNode);
        $selectNode = new Node\SelectNode(['fFloat']);
        $query->setSelect($selectNode);
        $limitNode = new Node\LimitNode(2, 1);
        $query->setLimit($limitNode);
        $queryArray = $this->object->query($query);
        $this->assertEquals(
                array('fFloat' => $this->_itemsArrayDelault[3 - 1]['fFloat']), $queryArray[1 - 1]
        );
        $this->assertEquals(
                array('fFloat' => $this->_itemsArrayDelault[4 - 1]['fFloat']), $queryArray[2 - 1]
        );
        $this->assertEquals(
                2, count($queryArray)
        );
    }

    public function provider_Query_Where_Like_True()
    {
        return array(
            array('*l1', 1, 1),
            array('*1*', 1, 1),
            array('*2*', 2, 3),
            array('val1', 1, 1),
            array('val2', 2, 3),
            array('?al1', 1, 1),
            array('?al2', 2, 3),
            array('?al?', 1, 4),
            array('v*2', 2, 3),
            array('?al?', 1, 4),
            array('?a*l?', 1, 4),
        );
    }

    /**
     * @dataProvider provider_Query_Where_Like_True
     */
    public function testQuery_Where_Like_True($globString, $arrayDelaultKeys, $count)
    {
        $this->_initObject();
        $query = new Query();
        $likeNode = new ScalarOperator\LikeNode(
                'fString', new Glob($globString)
        );
        $query->setQuery($likeNode);
        $queryArray = $this->object->query($query);
        $this->assertEquals(
                $this->_itemsArrayDelault[$arrayDelaultKeys - 1], $queryArray[1 - 1]
        );
        $this->assertEquals(
                $count, count($queryArray)
        );
    }

    public function provider_Query_Where_Like_False()
    {
        return array(
            array('*ol1'),
            array('*s1*'),
            array('dl1*'),
            array('vol1 '),
            array('?ol1'),
            array('?s1*'),
            array('dl1?'),
            array('*vol1? '),
        );
    }

    /**
     * @dataProvider provider_Query_Where_Like_False
     */
    public function testQuery_Where_Like_False($globString)
    {
        $this->_initObject();
        $query = new Query();
        $likeNode = new ScalarOperator\LikeNode(
                'fString', new Glob($globString)
        );
        $query->setQuery($likeNode);
        $queryArray = $this->object->query($query);
        $this->assertEquals(
                0, count($queryArray)
        );
    }

}

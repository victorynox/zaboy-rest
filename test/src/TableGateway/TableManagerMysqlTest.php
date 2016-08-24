<?php

namespace zaboy\test\TableGateway;

use zaboy\rest\TableGateway\TableManagerMysql;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2016-01-29 at 18:23:51.
 */
class TableManagerMysqlTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Returner
     */
    protected $object;

    /**
     * @var Zend\Db\Adapter\Adapter
     */
    protected $adapter;

    /**
     * @var ContainerInterface
     */
    protected $container;

    /**
     *
     * @var string
     */
    protected $tableName;

    /**
     *
     * @var array
     */
    protected $config = [
        TableManagerMysql::KEY_TABLES_CONFIGS => [
            'test_config_table' => [
                'id' => [
                    'field_type' => 'Integer',
                    'field_params' => [
                        'options' => ['autoincrement' => true]
                    ]
                ],
                'name' => [
                    'field_type' => 'Varchar',
                    'field_params' => [
                        'length' => 10,
                        'nullable' => true,
                        'default' => 'what?'
                    ]
                ]
            ]
        ]
    ];

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {

        $this->container = include './config/container.php';
        $this->adapter = $this->container->get('db');
        $this->tableName = 'test_create_table';

        $this->object = new TableManagerMysql($this->adapter, $this->config);
        if ($this->object->hasTable($this->tableName)) {
            $this->object->deleteTable($this->tableName);
        }
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {

    }

    public function testTableManagerMysql_Create()
    {
        $this->object->createTable($this->tableName, 'test_config_table');

        $this->assertSame(
                '    With columns: ' . PHP_EOL .
                '        id -> int' . PHP_EOL .
                '        name -> varchar' . PHP_EOL . PHP_EOL .
                '    With constraints: ' . PHP_EOL .
                '        _zf_test_create_table_PRIMARY -> PRIMARY KEY' . PHP_EOL .
                '            column: id'
                , $this->object->getTableInfoStr($this->tableName)
        );
    }

}

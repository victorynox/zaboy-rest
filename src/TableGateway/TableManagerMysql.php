<?php

/**
 * Zaboy lib (http://zaboy.org/lib/)
 *
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\rest\TableGateway;

use zaboy\rest\DataStore\DataStoreException;
use Zend\Db\Sql\Ddl\CreateTable;
use Zend\Db\Sql\Ddl\Constraint;
use zaboy\rest\RestException;
use Zend\Db\Sql;
use Zend\Db\Adapter;
use Zend\Db\Metadata\Source\Factory;
use Zend\Db\Metadata\Source;

/**
 * Creates table and gets its info
 *
 * Uses:
 * <code>
 *  $tableManager = new TableManagerMysql($adapter);
 *  $tableData = [
 *      'id' => [
 *          'field_type' => 'Integer',
 *          'field_params' => [
 *          'options' => ['autoincrement' => true]
 *          ]
 *      ],
 *      'name' => [
 *          'field_type' => 'Varchar',
 *          'field_params' => [
 *              'length' => 10,
 *              'nullable' => true,
 *              'default' => 'what?'
 *          ]
 *      ]
 *  ];
 *  $tableManager->createTable($tableData);
 * </code>
 *
 * As you can see, array $tableData has 3 keys and next structure:
 * <code>
 *  $tableData = [
 *      'FieldName' => [
 *          'field_type' => 'Integer',
 *          'field_params' => [
 *          'options' => ['autoincrement' => true]
 *          ]
 *      ],
 *      'NextFieldName' => [
 *  ...
 * </code>
 *
 * About value of key <b>'field_type'</b> - see {@link TableManagerMysql::$fieldClasses}<br>
 * About value of key <b>'field_params'</b> - see {@link TableManagerMysql::$parameters}<br>
 *
 * The <b>'options'</b> may be:
 * <ul>
 * <li>unsigned</li>
 * <li>zerofill</li>
 * <li>identity</li>
 * <li>serial</li>
 * <li>autoincrement</li>
 * <li>comment</li>
 * <li>columnformat</li>
 * <li>format</li>
 * <li>storage</li>
 * </ul>
 *
 * @see Examples/TableGateway/index.php
 * @category   rest
 * @package    zaboy
 */
class TableManagerMysql
{

    const FIELD_TYPE = 'field_type';
    const FIELD_PARAMS = 'field_params';
    const KEY_IN_CONFIG = 'tableManagerMysql';
    const KEY_TABLES_CONFIGS = 'tablesConfigs';
    const KEY_AUTOCREATE_TABLES = 'autocreateTables';

    /**
     *
     * @var \Zend\Db\Adapter\Adapter
     */
    protected $db;

    /**
     *
     * @var array
     */
    protected $config;

    /**
     *
     * @var array
     */
    protected $fieldClasses = [
        'Column' => [ 'BigInteger', 'Boolean', 'Date', 'Datetime', 'Integer', 'Time', 'Timestamp'],
        'LengthColumn' => [ 'Binary', 'Blob', 'Char', 'Text', 'Varbinary', 'Varchar'],
        'PrecisionColumn' => [ 'Decimal', 'Float', 'Floating']
    ];

    /**
     *
     * @var array
     */
    protected $parameters = [
        'Column' => [ 'nullable' => false, 'default' => null, 'options' => []],
        'LengthColumn' => [ 'length' => null, 'nullable' => false, 'default' => null, 'options' => []],
        'PrecisionColumn' => [ 'digits' => null, 'decimal' => null, 'nullable' => false, 'default' => null, 'options' => []]
    ];


    /**
     * TableManagerMysql constructor.
     * 
     * @param Adapter\Adapter $db
     * @param null $config
     */
    public function __construct(Adapter\Adapter $db, $config = null)
    {
        $this->db = $db;
        $this->config = $config;

        if (!isset($this->config[self::KEY_AUTOCREATE_TABLES])) {
            return;
        }
        $autocreateTables = $this->config[self::KEY_AUTOCREATE_TABLES];
        foreach ($autocreateTables as $tableName => $tableConfig) {
            if (!$this->hasTable($tableName)) {
                $this->create($tableName, $tableConfig);
            }
        }
    }

    /**
     * Preparing method of creating table
     *
     * Checks if the table exists and than if one don't creates the new table
     *
     * @param string $tableName
     * @param string $tableConfig
     * @return mixed
     * @throws DataStoreException
     * @throws RestException
     */
    public function createTable($tableName, $tableConfig)
    {
        if ($this->hasTable($tableName)) {
            throw new DataStoreException(
                "Table with name $tableName is exist. Use rewriteTable()"
            );
        }
        return $this->create($tableName, $tableConfig);
    }

    /**
     * Rewrites the table.
     *
     * Rewrite == delete existing table + create the new table
     *
     * @param string $tableName
     * @param string $tableConfig
     * @return mixed
     */
    public function rewriteTable($tableName, $tableConfig)
    {
        if ($this->hasTable($tableName)) {
            $this->deleteTable($tableName);
        }
        return $this->create($tableName, $tableConfig);
    }

    /**
     * Deletes Table
     *
     * @todo use zend deleteTable
     */
    public function deleteTable($tableName)
    {
        $deleteStatementStr = "DROP TABLE IF EXISTS "
                . $this->db->platform->quoteIdentifier($tableName);
        $deleteStatement = $this->db->query($deleteStatementStr);
        return $deleteStatement->execute();
    }

    /**
     * Builds and gets table info
     *
     * @see http://framework.zend.com/manual/current/en/modules/zend.db.metadata.html
     * @param string $tableName
     * @return string
     */
    public function getTableInfoStr($tableName)
    {
        $result = '';

        $metadata = Factory::createSourceFromAdapter($this->db);

        // gets the table names
        $tableNames = $metadata->getTableNames();

        $table = $metadata->getTable($tableName);


        $result .= '    With columns: ' . PHP_EOL;
        foreach ($table->getColumns() as $column) {
            $result .= '        ' . $column->getName()
                    . ' -> ' . $column->getDataType()
                    . PHP_EOL;
        }

        $result .= PHP_EOL;
        $result .= '    With constraints: ' . PHP_EOL;

        foreach ($metadata->getConstraints($tableName) as $constraint) {

            /** @var $constraint \Zend\Db\Metadata\Object\ConstraintObject */
            $result .= '        ' . $constraint->getName()
                    . ' -> ' . $constraint->getType()
                    . PHP_EOL;
            if (!$constraint->hasColumns()) {
                continue;
            }
            $result .= '            column: ' . implode(', ', $constraint->getColumns());
            if ($constraint->isForeignKey()) {
                $fkCols = array();
                foreach ($constraint->getReferencedColumns() as $refColumn) {
                    $fkCols[] = $constraint->getReferencedTableName() . '.' . $refColumn;
                }
                $result .= ' => ' . implode(', ', $fkCols);
            }

            return $result;
        }
    }

    /**
     * Checks if the table exists
     *
     * @param string $tableName
     * @return bool
     */
    public function hasTable($tableName)
    {
        $dbMetadata = Source\Factory::createSourceFromAdapter($this->db);
        $tableNames = $dbMetadata->getTableNames();
        return in_array($tableName, $tableNames);
    }

    /**
     * Returns the table config
     *
     * @return array|null
     */
    public function getConfig()
    {
        return $this->config;
    }

    /**
     * Fetchs the table config from common config of all the tables
     *
     * @param $tableConfig
     * @return mixed
     * @throws RestException
     */
    public function getTableConfig($tableConfig)
    {
        if (is_string($tableConfig)) {
            $config = $this->getConfig();
            if (isset($config[self::KEY_TABLES_CONFIGS][$tableConfig])) {
                $tableConfig = $config[self::KEY_TABLES_CONFIGS][$tableConfig];
            } else {
                throw new RestException('$tableConfig mast be an array or key in config');
            }
        }
        return $tableConfig;
    }


    /**
     * Creates table by its name and config
     *
     * @param $tableName
     * @param $tableConfig
     * @return Adapter\Driver\StatementInterface|\Zend\Db\ResultSet\ResultSet
     * @throws RestException
     */
    protected function create($tableName, $tableConfig)
    {
        $tableConfigArray = $this->getTableConfig($tableConfig);
        $table = new CreateTable($tableName);
        foreach ($tableConfigArray as $fieldName => $fieldData) {
            $fieldType = $fieldData[self::FIELD_TYPE];
            switch (true) {
                case in_array($fieldType, $this->fieldClasses['Column']):
                    $fieldParamsDefault = $this->parameters['Column'];
                    break;
                case in_array($fieldType, $this->fieldClasses['LengthColumn']):
                    $fieldParamsDefault = $this->parameters['LengthColumn'];
                    break;
                case in_array($fieldType, $this->fieldClasses['PrecisionColumn']):
                    $fieldParamsDefault = $this->parameters['PrecisionColumn'];
                    break;
                default:
                    throw new RestException('Unknown field type:' . $fieldType);
            }
            $fieldParams = [];
            foreach ($fieldParamsDefault as $key => $value) {
                if (key_exists($key, $fieldData[self::FIELD_PARAMS])) {
                    $fieldParams[] = $fieldData[self::FIELD_PARAMS][$key];
                } else {
                    $fieldParams[] = $value;
                }
            }
            array_unshift($fieldParams, $fieldName);
            $fieldClass = '\\Zend\\Db\\Sql\\Ddl\\Column\\' . $fieldType;
            $reflectionObject = new \ReflectionClass($fieldClass);
            $fieldInstance = $reflectionObject->newInstanceArgs($fieldParams); // it' like new class($callParamsArray[1], $callParamsArray[2]...)
            $table->addColumn($fieldInstance);
        }

        $table->addConstraint(new Constraint\PrimaryKey('id'));


        $ctdMysql = new Sql\Platform\Mysql\Ddl\CreateTableDecorator();
        $mySqlPlatformDbAdapter = new Adapter\Platform\Mysql();
        $mySqlPlatformDbAdapter->setDriver($this->db->getDriver());
        $sqlString = $ctdMysql->setSubject($table)->getSqlString($mySqlPlatformDbAdapter);

        // this is simpler version, not MySQL only, but without options[] support
        //$mySqlPlatformSql = new Sql\Platform\Mysql\Mysql();
        //$sql = new Sql\Sql($this->db, null, $mySqlPlatformSql);
        //$sqlString = $sql->buildSqlString($table);

        return $this->db->query(
                        $sqlString, \Zend\Db\Adapter\Adapter::QUERY_MODE_EXECUTE
        );
    }

}

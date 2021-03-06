<?php

return [
    'tableManagerMysql' => [
        'tablesConfigs' => [
            'test_table_config' => [],
        ],
        'autocreateTables' => [
            'test_autocreate_table' => 'test_table_config'
        ]
    ],

    'tableGateway' =>[
        'test_res_tablle' => [
            'sql' => 'zaboy\rest\TableGateway\DbSql\MultiInsertSql',
        ],
    ],

    'dataStore' => [
        'test_DataStoreDbTableWithNameAsResourceName' => [
            'class' => 'zaboy\rest\DataStore\DbTable',
            'tableName' => 'table_for_db_data_store'
        ],
        'test_StoreForMiddleware' => [
            'class' => 'zaboy\rest\DataStore\Memory',
        ],
        'testDbTable' => [
            'class' => 'zaboy\rest\DataStore\DbTable',
            'tableName' => 'test_res_tablle'
        ],

        'testDbTableMultiInsert' => [
            'class' => 'zaboy\rest\DataStore\DbTable',
            'tableGateway' => 'test_res_tablle',
        ],
        /*'testHttpClient' => [
            'class' => 'zaboy\rest\DataStore\HttpClient',
            'tableName' => 'test_res_http',
            'url' => 'http://zaboy-rest.loc/api/rest/test_res_http',
            'options' => ['timeout' => 30]
        ],
        'testEavOverHttpClient' => [
            'class' => 'zaboy\rest\DataStore\HttpClient',
            'url' => 'http://zaboy-rest.loc/api/rest/entity_product',
            'options' => ['timeout' => 30]
        ],
         'testEavOverHttpDbClient' => [
            'class' => 'zaboy\rest\DataStore\HttpClient',
            'url' => 'http://localhost:9090/api/rest/db.entity_product',
            'options' => ['timeout' => 30]
        ],
        */
        'testMemory' => [
            'class' => 'zaboy\rest\DataStore\Memory',
        ],
        'testCsvBase' => [
            'class' => 'zaboy\rest\DataStore\CsvBase',
            'filename' => sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'testCsvBase.tmp',
            'delimiter' => ';',
        ],
        'testCsvIntId' => [
            'class' => 'zaboy\rest\DataStore\CsvIntId',
            'filename' => sys_get_temp_dir() . DIRECTORY_SEPARATOR . 'testCsvIntId.tmp',
            'delimiter' => ';',
        ],
        'testAspectAbstract' => [
            'class' => 'zaboy\rest\DataStore\Aspect\AspectAbstract',
            'dataStore' => 'testMemory',
        ],
        
        'testDataSourceDb' => [
            'class' => 'zaboy\rest\DataSource\DbTableDataSource',
            //'class' => 'zaboy\rest\DataStore\DbTable',
            'tableName' => 'test_res_http'
        ],
        
        'testCacheable' => [
            'class' => 'zaboy\rest\DataStore\Cacheable',
            'dataSource' => 'testDataSourceDb',
            'cacheable' => 'testDbTable'
        ]
    ],
    'middleware' => [
        'test_MiddlewareWithNameAsResourceName' => [
            'class' => 'zaboy\rest\Middleware\DataStoreRest',
            'dataStore' => 'test_StoreForMiddleware'
        ],
        'MiddlewareMemoryTest' => [
            'class' => 'zaboy\rest\Examples\Middleware\DataStoreMemory',
            'dataStore' => 'testMemory'
        ]
    ],
];

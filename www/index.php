<?php

// try http://__zaboy-rest/api/rest/index_StoreMiddleware?fNumberOfHours=8&fWeekday=Monday
// Change to the project root, to simplify resolving paths
chdir(dirname(__DIR__));
//test_res_http
// Setup autoloading
require 'vendor/autoload.php';

use zaboy\rest\Pipe\MiddlewarePipeOptions;
use Zend\Diactoros\Server;
use zaboy\rest\Pipe\Factory\RestRqlFactory;

//echo urlencode('.');
//echo '!*****************';

// Define application environment
if (getenv('APP_ENV') === 'dev') {
    error_reporting(E_ALL);
    ini_set('display_errors', 1);
}

/**
  use ReputationVIP\QueueClient\QueueClient;
  use ReputationVIP\QueueClient\Adapter\FileAdapter;

  $adapter = new FileAdapter('/tmp');
  $queueClient = new QueueClient($adapter);
  //$queueClient->createQueue('testQueue');
  $queueClient->addMessage('testQueue', 'testMessage');

  $messages = $queueClient->getMessages('testQueue');
  $message = $messages[0];
  $queueClient->deleteMessage('testQueue', $message);
  echo $message['Body'];
 */
$container = include 'config/container.php';
$tableName = 'test_res_http'; //'index_php_table';
//include 'createTable.php';

$app = new MiddlewarePipeOptions([]); //'env' => 'develop'

$RestRqlFactory = new RestRqlFactory();
$rest = $RestRqlFactory($container, '');
$app->pipe('/api/rest', $rest);

$server = Server::createServer($app, $_SERVER, $_GET, $_POST, $_COOKIE, $_FILES
);
$server->listen();

//$deleteStatementStr = "DROP TABLE IF EXISTS " .  $quoteTableName;
//$deleteStatement = $adapter->query($deleteStatementStr);
//$deleteStatement->execute();

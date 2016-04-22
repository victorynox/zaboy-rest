<?php

/**
 * Zaboy lib (http://zaboy.org/lib/)
 *
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\rest\Middleware\Factory;

//use Zend\ServiceManager\Factory\FactoryInterface;
//uncomment it ^^ for Zend\ServiceManager V3
use Zend\ServiceManager\FactoryInterface;
//comment it ^^ for Zend\ServiceManager V3
use Zend\ServiceManager\ServiceLocatorInterface;
use zaboy\rest\RestException;
use Interop\Container\ContainerInterface;
use zaboy\rest\Middleware;
use zaboy\rest\DataStore\DbTable;
use zaboy\rest\DataStore\Interfaces\DataStoresInterface;

/**
 *
 * @category   Rest
 * @package    Rest
 */
class StoreMiddlewareDirectFactory implements FactoryInterface
{

    /**
     * Create and return an instance of the PipeMiddleware for Rest.
     * <br>
     * If StoreMiddleware with same name as name of resource is discribed in config
     * in key 'middleware' - it will use
     * <br>
     * If DataStore with same name as name of resource is discribed in config
     * in key 'dataStore' - it will use for create StoreMiddleware
     * <br>
     * If table in DB with same name as name of resource is exist
     *  - it will use for create TableGateway for create DataStore for create StoreMiddleware
     * <br>
     * Add <br>
     * zaboy\rest\TableGateway\Factory\TableGatewayAbstractFactory <br>
     * zaboy\rest\DataStore\Factory\DbTableStoresAbstractFactory <br>
     * zaboy\rest\Middleware\Factory\MiddlewareStoreAbstractFactory <br>
     * to config<br>
     *
     * @param  Interop\Container\ContainerInterface $container
     * @param  string $requestedName
     * @param  array $options
     * @return MiddlewareInterface
     */
    public function __invoke(ContainerInterface $container, $requestedName, array $options = null)
    {
        $resourceName = $requestedName;
        if (!$container->has($resourceName)) {
            throw new RestException(
            'Can\'t make storeMiddleware for resource: ' . $resourceName
            );
        }
        $resourceObject = $container->get($resourceName);
        switch (true) {
            case is_a($resourceObject, 'Zend\Db\TableGateway\TableGateway'):
                $tableGateway = $resourceObject;
                $resourceObject = new DbTable($tableGateway);
            case ($resourceObject instanceof DataStoresInterface):
                $dataStore = $resourceObject;
                $resourceObject = new Middleware\StoreMiddleware($dataStore);
            case $resourceObject instanceof \Zend\Stratigility\MiddlewareInterface:
                $storeMiddleware = $resourceObject;
            default:
                if (!isset($storeMiddleware)) {
                    throw new RestException(
                    'Can\'t make StoreMiddleware'
                    . ' for resource: ' . $resourceName
                    );
                }
        }
        return $storeMiddleware;
    }

    /**
     * Create service
     *
     * @param ServiceLocatorInterface $serviceLocator
     * @return mixed
     */
    public function createService(ServiceLocatorInterface $serviceLocator)
    {
        throw new RestException(
        'Don\'t use it as factory in config. ' . PHP_EOL
        . 'Call __invoke directly with resource name as parameter'
        );
    }

}

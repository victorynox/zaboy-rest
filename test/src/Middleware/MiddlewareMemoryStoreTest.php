<?php

namespace zaboy\test\res\Middleware;

use zaboy\rest\Middleware\MiddlewareMemoryStore;
use Zend\Diactoros\Response;
use Zend\Diactoros\ServerRequest;

/**
 * Generated by PHPUnit_SkeletonGenerator on 2016-01-29 at 18:23:51.
 */
class MiddlewareMemoryStoreTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var Returner
     */
    protected $object;

    /*
     * @var Zend\Diactoros\Response
     */
    protected $response;

    /*
     * @var Zend\Diactoros\ServerRequest;
     */
    protected $request;

    /*
     * @var \Callable
     */
    protected $next;

    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        $this->object = new MiddlewareMemoryStore();
        $this->response = new Response();
        $this->request = new ServerRequest([], [], '/foo');
        $this->next = function ($req, $resp) {
            return $req;
        };
    }

    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {

    }

    public function testMiddlewareMemoryStore__invoke()
    {
        $returnedResponse = $this->object
                ->__invoke($this->request, $this->response, $this->next);
        $this->assertSame(
                get_class($returnedResponse->getAttribute('memoryStore')), 'zaboy\rest\DataStore\Memory'
        );
    }

}

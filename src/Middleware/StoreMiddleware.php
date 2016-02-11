<?php
/**
 * Zaboy lib (http://zaboy.org/lib/)
 * 
 * @see http://tools.ietf.org/html/rfc2616#page-122
 * @copyright  Zaboychenko Andrey
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 */

namespace zaboy\rest\Middleware;

use zaboy\res\Middlewares\StoreMiddlewareAbstract;
use zaboy\middleware\MiddlewaresException;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\ServerRequestInterface;
use Xiag\Rql\Parser\Query;
use Zend\Diactoros\Response\JsonResponse;

/**
 * 
 * @category   Rest
 * @package    Rest
 */
class StoreMiddleware extends StoreMiddlewareAbstract 
{
    /*
     * 
     */
    protected $request;
    
    /**                                                    204 No Content
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param callable|null $next
     * @return ResponseInterface
     */
    public function __invoke(ServerRequestInterface $request, ResponseInterface $response, callable $next = null)
    {
        $isPrimaryKeyValue = null !== $request->getAttribute('Primary-Key-Value');
        $httpMethod = $request->getMethod();
        try {        
            switch ($request->getMethod()) {
                case $httpMethod === 'GET' && $isPrimaryKeyValue:
                    $response = $this->methodGetWithId($request, $response);
                    break;
                case $httpMethod === 'GET' && !($isPrimaryKeyValue):
                    $response = $this->methodGetWithoutId($request, $response);    
                    break;
                case $httpMethod === 'PUT' && $isPrimaryKeyValue:
                    $response = $this->methodPutWithId($request, $response);
                    break;
                case $httpMethod === 'PUT' && !$isPrimaryKeyValue:
                    throw new \zaboy\rest\RestException('PUT without Primary Key');
                case $httpMethod === 'POST':
                    $response = $this->methodPutWithId($request, $response);
                    break;
                case $httpMethod === 'DELETE':
                    $response = $this->methodPutWithId($request, $response);
                    break;
                default :    
                    throw new \zaboy\rest\RestException(
                       'Method must be GET, PUT, POST or DELETE. '
                       . $request->getMethod() . ' given'
                    );
            }
        } catch (\zaboy\rest\RestException $ex) {
            return new JsonResponse([
                $ex->getMessage()
            ], 500);
        }

        if ($next) {
            return $next($this->request, $response);
        }
        return $response;
    }
    
    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param callable|null $next
     * @return ResponseInterface
     */
    public function methodGetWithId(ServerRequestInterface $request, ResponseInterface $response)
    {
        $primaryKeyValue = $request->getAttribute('Primary-Key-Value');
        $row = $this->dataStore->read($primaryKeyValue);
        $this->request = $request->withAttribute('Response-Body', $row);
        $rowCount = empty($request) ? 0 : 1;                
        $contentRange = 'items ' . $primaryKeyValue . '-' . $primaryKeyValue;
        $response = $response->withHeader('Content-Range', $contentRange);
        $response = $response->withStatus(200);
        return $response;
    }    
    
    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param callable|null $next
     * @return ResponseInterface
     */
    public function methodGetWithoutId(ServerRequestInterface $request, ResponseInterface $response)
    {
        $rqlQueryObject = $request->getAttribute('Rql-Query-Object');
        $rowset = $this->dataStore->query($rqlQueryObject);
        $this->request = $request->withAttribute('Response-Body', $rowset);
        $rowCount = count($rowset);                
        $limitObject = $rqlQueryObject->getLimit();
        $offset = !$limitObject ? 0 : $limitObject->getOffset(); 
        $contentRange = 'items ' . $offset . '-' . $offset + $rowCount-1 . '/' . $rowCount;
        $response = $response->withHeader('Content-Range', $contentRange);
        $response = $response->withStatus(200);
        return $response;
    }
    
    /**
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param callable|null $next
     * @return ResponseInterface
     */
    public function methodPutWithId(ServerRequestInterface $request, ResponseInterface $response)
    {
        $primaryKeyValue = $request->getAttribute('Primary-Key-Value');
        $primaryKeyIdentifier =  $this->dataStore->getIdentifier();
        $row = $request->getParsedBody();
        if (!(isset($row) && is_array($row))) {
            throw new \zaboy\rest\RestException('No body in PUT request');
        }
        $row[$primaryKeyIdentifier] = $primaryKeyValue;
        $overwriteMode = $request->getAttribute('Overwrite-Mode');
        $isIdExist = !empty($this->dataStore->read($primaryKeyValue));
        //Post only
        if ($overwriteMode && !$isIdExist) {
            $response = $response->withStatus(201);
        }else{
            $response = $response->withStatus(200);
        }
        $newRow = $this->dataStore->update($row, $overwriteMode);
        $this->request  = $request->withAttribute('Response-Body', $newRow);
        return $response;
    } 
    
    
    /**                                              Location: http://www.example.com/users/4/    
     * http://www.restapitutorial.com/lessons/httpmethods.html
     * 
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param callable|null $next
     * @return ResponseInterface
     */
    public function methodPost(ServerRequestInterface $request, ResponseInterface $response)
    {
        $primaryKeyValue = $request->getAttribute('Primary-Key-Value');
        $primaryKeyIdentifier =  $this->dataStore->getIdentifier();
        $row = $request->getParsedBody();
        if (!(isset($row) && is_array($row))) {
            throw new \zaboy\rest\RestException('No body in POST request');
        }
        $row[$primaryKeyIdentifier] = $primaryKeyValue;
        $overwriteMode = $request->getAttribute('Overwrite-Mode');
        $isIdExist = !empty($this->dataStore->read($primaryKeyValue));
        if ($overwriteMode && !$isIdExist) {
            $response = $response->withStatus(201);
        }else{
            $response = $response->withStatus(200);
        }
        
        $newRow = $this->dataStore->update($row, $overwriteMode);
        $request = $request->withAttribute('Response-Body', $newRow);
        return $response;
    } 
    
    /**                                              Location: http://www.example.com/users/4/    
     * http://www.restapitutorial.com/lessons/httpmethods.html
     * 
     * @param ServerRequestInterface $request
     * @param ResponseInterface $response
     * @param callable|null $next
     * @return ResponseInterface
     */
    public function methodDelete(ServerRequestInterface $request, ResponseInterface $response)
    {
        $primaryKeyValue = $request->getAttribute('Primary-Key-Value');
        $primaryKeyIdentifier =  $this->dataStore->getIdentifier();
        $row = $request->getParsedBody();
        if (!(isset($row) && is_array($row))) {
            throw new \zaboy\rest\RestException('No body in POST request');
        }
        $row[$primaryKeyIdentifier] = $primaryKeyValue;
        $overwriteMode = $request->getAttribute('Overwrite-Mode');
        $isIdExist = !empty($this->dataStore->read($primaryKeyValue));
        if ($overwriteMode && !$isIdExist) {
            $response = $response->withStatus(201);
        }else{
            $response = $response->withStatus(200);
        }
        
        $newRow = $this->dataStore->update($row, $overwriteMode);
        $request = $request->withAttribute('Response-Body', $newRow);
        return $response;
    } 
}
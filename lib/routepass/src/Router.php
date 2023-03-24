<?php

  require_once __DIR__ . "/PathNode.php";
  require_once __DIR__ . "/RouterLike.php";
  require_once __DIR__ . "/Parametric.php";

  class Router extends RouterLike implements Parametric {
    /** Try to avoid using as much as possible. May cause problems with `'internal param break character'` (characters that are not considered as valid param name. `/user/:user-id` interpreted as `/user/{space for 'user' parameter}-id`) */
    const REGEX_DEFAULT = "([^-.~]+)";
    const REGEX_ANY = "(.*)";
    const REGEX_NUMBER = "([0-9]+)";
    const REGEX_WORD = "([a-zA-Z]+)";
    const REGEX_WORD_UPPER = "([A-Z]+)";
    const REGEX_WORD_LOWER = "([a-z]+)";
    const REGEX_SENTENCE = "([a-zA-Z_]+)";
    const REGEX_SENTENCE_UPPER = "([A-Z_]+)";
    const REGEX_SENTENCE_LOWER = "([a-z_]+)";
    const REGEX_BASE64_URL_SAFE = "([a-zA-Z0-9_\\-]+)";
    
    public static function REGEX_ENUM (array $values) {
      return "(" . join("|", $values) . ")";
    }
  
    public PathNode $home;
    public function getHome(): PathNode {
      return $this->home;
    }
    public function setHome(PathNode $home) {
      $this->home = $home;
    }
  
    /**
     * @var Router[]
     */
    public array $domainDictionary = [];
    
    
    public function __construct (Node $parent = null) {
      $this->home = new PathNode("", $this);
      $this->parent = $parent;
    }
    protected function assign (string &$httpMethod, array &$uriParts, array &$callbacks, array &$paramCaptureGroupMap = []) {
      if (empty($uriParts)) {
        $this->home->handles[$httpMethod] = $callbacks;
        return;
      }

      $this->home->assign($httpMethod, $uriParts, $callbacks, $paramCaptureGroupMap);
    }
    protected function setMethod (string &$httpMethod, array &$callbacks) {
      $this->home->setMethod($httpMethod, $callbacks);
    }
    protected function execute (array &$uri, int $uriIndex, Request &$request, Response &$response) {
      $this->home->execute($uri, $uriIndex, $request, $response);
    }
    public function getEndpoints(): array {
      return $this->home->getEndpoints();
    }
    public function createPath (array $uriParts, array &$paramCaptureGroupMap = []): Node {
      return $this->home->createPath($uriParts, $paramCaptureGroupMap);
    }
  
  
    
    
    /**
     * @inheritDoc
     */
    public function use (string $uriPattern, RouterLike $router, array $paramCaptureGroupMap = []) {
      $parsedURI = self::filterEmpty(explode("/", $uriPattern));
      $lastNode = $this->createPath($parsedURI, $paramCaptureGroupMap);
      
      $part = $lastNode->getPathPart();
      $parent = $lastNode->getParent();
      $router->setPathPart($part);
      $router->setParent($parent);
    
      if ($lastNode instanceof ParametricPathNode) {
        $parent->parametric[array_search($lastNode, $parent->parametric)] = $router;
        if (!$router->getHome() instanceof ParametricPathNode) {
          $paramNode = new ParametricPathNode($part, $router);
          $router->setHome($paramNode->upgrade($router->getHome()));
        }
      
        $router->setIsParametric(true);
        $router->setRegex($lastNode->getRegex());
        $router->setParamDirectory($lastNode->getParamDirectory());
        return;
      }
    
      $parent->static[$part] = $router;
    }
    
    private $callbacks = [];
    public function implement (Closure ...$callbacks) {
      foreach ($callbacks as $callback) {
        $this->callbacks[] = $callback;
      }
    }
    
    public function getCallbacks(array $list = []): array {
      $merged = [];
      foreach ($this->callbacks as $callback) {
        $merged[] = $callback;
      }
      
      foreach ($list as $item) {
        $merged[] = $item;
      }
      
      if (!isset($this->parent)) {
        return $merged;
      }
      
      return $this->parent->getCallbacks($merged);
    }
  
  
    /**
     * For these HTTP Methods will be added assigned given callbacks.
     *
     * @param array $httpMethods
     * @param string $uriPattern
     * @param array $callbacks
     * @param array $paramCaptureGroupMap
     * @return void
     */
    public function for (array $httpMethods, string $uriPattern, array $callbacks, array $paramCaptureGroupMap = []) {
      $parsedURI = self::filterEmpty(explode("/", $uriPattern));
      $lastNode = $this->createPath($parsedURI, $paramCaptureGroupMap);
  
      foreach ($httpMethods as $method) {
        $m = strtoupper($method);
        $lastNode->setMethod($m, $callbacks);
      }
    }
  
  
    
    
    
    
    /**
     * For every HTTP Method will be assigned given callbacks.
     * @param string $uriPattern
     * @param array $callbacks
     * @param array $paramCaptureGroupMap
     * @return void
     */
    public function forAll (string $uriPattern, array $callbacks, array $paramCaptureGroupMap = []) {
      $parsedURI = self::filterEmpty(explode("/", $uriPattern));
      $lastNode = $this->createPath($parsedURI, $paramCaptureGroupMap);
      
      foreach (["GET", "HEAD", "POST", "PUT", "DELETE", "CONNECT", "OPTIONS", "TRACE", "PATCH"] as $method) {
        $lastNode->setMethod($method, $callbacks);
      }
    }
  
  
    
    
    
    
    /**
     * The GET method requests a representation of the specified resource. Requests using GET should only retrieve data.
     *
     * ### URI Pattern
     *
     * * static: /static/api/route
     * * parametric: /parametric/route/with/:id in parametric format
     *
     * ### Parametric format
     *
     * To declare new parameter use colon with name of the parameter afterwards (name may contain only letters, numbers and underscore /[0-9a-zA-Z_]+/) ":myParam"
     *
     * These characters will break the parameter name and will be added to the format -.~
     *
     * The backslash \ character is used to break from the name, but it will not be added to the format.
     *
     * `/books/:idbooks` will result to `/books/<anything>`
     *
     * `/books/:id\books` will result to `/books/<anything>books`
     *
     * Parameters are able to be bind to their specific regular expression using `Parameter Capture Group Map`
     *
     * This map has as a keys name of the parameter and as a value regex capture group.
     *
     * URI Pattern = `/books/:id`; map = `["id" => "([0-9]+)"]` or `["id" => Router::REGEX_NUMBER]` will match `/books/69` or `/books/105839` but will not match `/books/foo`
     * @param string $uriPattern
     * @param array $callbacks
     * @param array $paramCaptureGroupMap
     * @return void
     */
    public function get (string $uriPattern, array $callbacks, array $paramCaptureGroupMap = []) {
      $parsedURI = self::filterEmpty(explode("/", $uriPattern));
      $m = "GET";
      $this->assign($m, $parsedURI, $callbacks, $paramCaptureGroupMap);
    }
  
  
    
    
    
    
    /**
     * The HEAD method asks for a response identical to a GET request, but without the response body.
     *
     * ### URI Pattern
     *
     * * static: /static/api/route
     * * parametric: /parametric/route/with/:id in parametric format
     *
     * ### Parametric format
     *
     * To declare new parameter use colon with name of the parameter afterwards (name may contain only letters, numbers and underscore /[0-9a-zA-Z_]+/) ":myParam"
     *
     * These characters will break the parameter name and will be added to the format -.~
     *
     * The backslash \ character is used to break from the name, but it will not be added to the format.
     *
     * `/books/:idbooks` will result to `/books/<anything>`
     *
     * `/books/:id\books` will result to `/books/<anything>books`
     *
     * Parameters are able to be bind to their specific regular expression using `Parameter Capture Group Map`
     *
     * This map has as a keys name of the parameter and as a value regex capture group.
     *
     * URI Pattern = `/books/:id`; map = `["id" => "([0-9]+)"]` or `["id" => Router::REGEX_NUMBER]` will match `/books/69` or `/books/105839` but will not match `/books/foo`
     * @param string $uriPattern
     * @param array $callbacks
     * @param array $paramCaptureGroupMap
     * @return void
     */
    public function head (string $uriPattern, array $callbacks, array $paramCaptureGroupMap = []) {
      $parsedURI = self::filterEmpty(explode("/", $uriPattern));
      $m = "HEAD";
      $this->assign($m, $parsedURI, $callbacks, $paramCaptureGroupMap);
    }
  
  
    
    
    
    
    /**
     * The POST method submits an entity to the specified resource, often causing a change in state or side effects on the server.
     *
     * ### URI Pattern
     *
     * * static: /static/api/route
     * * parametric: /parametric/route/with/:id in parametric format
     *
     * ### Parametric format
     *
     * To declare new parameter use colon with name of the parameter afterwards (name may contain only letters, numbers and underscore /[0-9a-zA-Z_]+/) ":myParam"
     *
     * These characters will break the parameter name and will be added to the format -.~
     *
     * The backslash \ character is used to break from the name, but it will not be added to the format.
     *
     * `/books/:idbooks` will result to `/books/<anything>`
     *
     * `/books/:id\books` will result to `/books/<anything>books`
     *
     * Parameters are able to be bind to their specific regular expression using `Parameter Capture Group Map`
     *
     * This map has as a keys name of the parameter and as a value regex capture group.
     *
     * URI Pattern = `/books/:id`; map = `["id" => "([0-9]+)"]` or `["id" => Router::REGEX_NUMBER]` will match `/books/69` or `/books/105839` but will not match `/books/foo`
     * @param string $uriPattern
     * @param array $callbacks
     * @param array $paramCaptureGroupMap
     * @return void
     */
    public function post (string $uriPattern, array $callbacks, array $paramCaptureGroupMap = []) {
      $parsedURI = self::filterEmpty(explode("/", $uriPattern));
      $m = "POST";
      $this->assign($m, $parsedURI, $callbacks, $paramCaptureGroupMap);
    }
  
  
    
    
    
    
    /**
     * The PUT method replaces all current representations of the target resource with the request payload.
     *
     * ### URI Pattern
     *
     * * static: /static/api/route
     * * parametric: /parametric/route/with/:id in parametric format
     *
     * ### Parametric format
     *
     * To declare new parameter use colon with name of the parameter afterwards (name may contain only letters, numbers and underscore /[0-9a-zA-Z_]+/) ":myParam"
     *
     * These characters will break the parameter name and will be added to the format -.~
     *
     * The backslash \ character is used to break from the name, but it will not be added to the format.
     *
     * `/books/:idbooks` will result to `/books/<anything>`
     *
     * `/books/:id\books` will result to `/books/<anything>books`
     *
     * Parameters are able to be bind to their specific regular expression using `Parameter Capture Group Map`
     *
     * This map has as a keys name of the parameter and as a value regex capture group.
     *
     * URI Pattern = `/books/:id`; map = `["id" => "([0-9]+)"]` or `["id" => Router::REGEX_NUMBER]` will match `/books/69` or `/books/105839` but will not match `/books/foo`
     * @param string $uriPattern
     * @param array $callbacks
     * @param array $paramCaptureGroupMap
     * @return void
     */
    public function put (string $uriPattern, array $callbacks, array $paramCaptureGroupMap = []) {
      $parsedURI = self::filterEmpty(explode("/", $uriPattern));
      $m = "PUT";
      $this->assign($m, $parsedURI, $callbacks, $paramCaptureGroupMap);
    }
  
  
    
    
    
    
    /**
     * The DELETE method deletes the specified resource.
     *
     * ### URI Pattern
     *
     * * static: /static/api/route
     * * parametric: /parametric/route/with/:id in parametric format
     *
     * ### Parametric format
     *
     * To declare new parameter use colon with name of the parameter afterwards (name may contain only letters, numbers and underscore /[0-9a-zA-Z_]+/) ":myParam"
     *
     * These characters will break the parameter name and will be added to the format -.~
     *
     * The backslash \ character is used to break from the name, but it will not be added to the format.
     *
     * `/books/:idbooks` will result to `/books/<anything>`
     *
     * `/books/:id\books` will result to `/books/<anything>books`
     *
     * Parameters are able to be bind to their specific regular expression using `Parameter Capture Group Map`
     *
     * This map has as a keys name of the parameter and as a value regex capture group.
     *
     * URI Pattern = `/books/:id`; map = `["id" => "([0-9]+)"]` or `["id" => Router::REGEX_NUMBER]` will match `/books/69` or `/books/105839` but will not match `/books/foo`
     * @param string $uriPattern
     * @param array $callbacks
     * @param array $paramCaptureGroupMap
     * @return void
     */
    public function delete (string $uriPattern, array $callbacks, array $paramCaptureGroupMap = []) {
      $parsedURI = self::filterEmpty(explode("/", $uriPattern));
      $m = "DELETE";
      $this->assign($m, $parsedURI, $callbacks, $paramCaptureGroupMap);
    }
  
  
    
    
    
    
    /**
     * The CONNECT method establishes a tunnel to the server identified by the target resource.
     *
     * ### URI Pattern
     *
     * * static: /static/api/route
     * * parametric: /parametric/route/with/:id in parametric format
     *
     * ### Parametric format
     *
     * To declare new parameter use colon with name of the parameter afterwards (name may contain only letters, numbers and underscore /[0-9a-zA-Z_]+/) ":myParam"
     *
     * These characters will break the parameter name and will be added to the format -.~
     *
     * The backslash \ character is used to break from the name, but it will not be added to the format.
     *
     * `/books/:idbooks` will result to `/books/<anything>`
     *
     * `/books/:id\books` will result to `/books/<anything>books`
     *
     * Parameters are able to be bind to their specific regular expression using `Parameter Capture Group Map`
     *
     * This map has as a keys name of the parameter and as a value regex capture group.
     *
     * URI Pattern = `/books/:id`; map = `["id" => "([0-9]+)"]` or `["id" => Router::REGEX_NUMBER]` will match `/books/69` or `/books/105839` but will not match `/books/foo`
     * @param string $uriPattern
     * @param array $callbacks
     * @param array $paramCaptureGroupMap
     * @return void
     */
    public function connect (string $uriPattern, array $callbacks, array $paramCaptureGroupMap = []) {
      $parsedURI = self::filterEmpty(explode("/", $uriPattern));
      $m = "CONNECT";
      $this->assign($m, $parsedURI, $callbacks, $paramCaptureGroupMap);
    }
  
  
    
    
    
    
    /**
     * The OPTIONS method describes the communication options for the target resource.
     *
     * ### URI Pattern
     *
     * * static: /static/api/route
     * * parametric: /parametric/route/with/:id in parametric format
     *
     * ### Parametric format
     *
     * To declare new parameter use colon with name of the parameter afterwards (name may contain only letters, numbers and underscore /[0-9a-zA-Z_]+/) ":myParam"
     *
     * These characters will break the parameter name and will be added to the format -.~
     *
     * The backslash \ character is used to break from the name, but it will not be added to the format.
     *
     * `/books/:idbooks` will result to `/books/<anything>`
     *
     * `/books/:id\books` will result to `/books/<anything>books`
     *
     * Parameters are able to be bind to their specific regular expression using `Parameter Capture Group Map`
     *
     * This map has as a keys name of the parameter and as a value regex capture group.
     *
     * URI Pattern = `/books/:id`; map = `["id" => "([0-9]+)"]` or `["id" => Router::REGEX_NUMBER]` will match `/books/69` or `/books/105839` but will not match `/books/foo`
     * @param string $uriPattern
     * @param array $callbacks
     * @param array $paramCaptureGroupMap
     * @return void
     */
    public function options (string $uriPattern, array $callbacks, array $paramCaptureGroupMap = []) {
      $parsedURI = self::filterEmpty(explode("/", $uriPattern));
      $m = "OPTIONS";
      $this->assign($m, $parsedURI, $callbacks, $paramCaptureGroupMap);
    }
  
  
    
    
    
    
    /**
     * The TRACE method performs a message loop-back test along the path to the target resource.
     *
     * ### URI Pattern
     *
     * * static: /static/api/route
     * * parametric: /parametric/route/with/:id in parametric format
     *
     * ### Parametric format
     *
     * To declare new parameter use colon with name of the parameter afterwards (name may contain only letters, numbers and underscore /[0-9a-zA-Z_]+/) ":myParam"
     *
     * These characters will break the parameter name and will be added to the format -.~
     *
     * The backslash \ character is used to break from the name, but it will not be added to the format.
     *
     * `/books/:idbooks` will result to `/books/<anything>`
     *
     * `/books/:id\books` will result to `/books/<anything>books`
     *
     * Parameters are able to be bind to their specific regular expression using `Parameter Capture Group Map`
     *
     * This map has as a keys name of the parameter and as a value regex capture group.
     *
     * URI Pattern = `/books/:id`; map = `["id" => "([0-9]+)"]` or `["id" => Router::REGEX_NUMBER]` will match `/books/69` or `/books/105839` but will not match `/books/foo`
     * @param string $uriPattern
     * @param array $callbacks
     * @param array $paramCaptureGroupMap
     * @return void
     */
    public function trace (string $uriPattern, array $callbacks, array $paramCaptureGroupMap = []) {
      $parsedURI = self::filterEmpty(explode("/", $uriPattern));
      $m = "TRACE";
      $this->assign($m, $parsedURI, $callbacks, $paramCaptureGroupMap);
    }
  
  
    
    
    
    
    /**
     * The PATCH method applies partial modifications to a resource.
     *
     * ### URI Pattern
     *
     * * static: /static/api/route
     * * parametric: /parametric/route/with/:id in parametric format
     *
     * ### Parametric format
     *
     * To declare new parameter use colon with name of the parameter afterwards (name may contain only letters, numbers and underscore /[0-9a-zA-Z_]+/) ":myParam"
     *
     * These characters will break the parameter name and will be added to the format -.~
     *
     * The backslash \ character is used to break from the name, but it will not be added to the format.
     *
     * `/books/:idbooks` will result to `/books/<anything>`
     *
     * `/books/:id\books` will result to `/books/<anything>books`
     *
     * Parameters are able to be bind to their specific regular expression using `Parameter Capture Group Map`
     *
     * This map has as a keys name of the parameter and as a value regex capture group.
     *
     * URI Pattern = `/books/:id`; map = `["id" => "([0-9]+)"]` or `["id" => Router::REGEX_NUMBER]` will match `/books/69` or `/books/105839` but will not match `/books/foo`
     * @param string $uriPattern
     * @param array $callbacks
     * @param array $paramCaptureGroupMap
     * @return void
     */
    public function patch (string $uriPattern, array $callbacks, array $paramCaptureGroupMap = []) {
      $parsedURI = self::filterEmpty(explode("/", $uriPattern));
      $m = "PATCH";
      $this->assign($m, $parsedURI, $callbacks, $paramCaptureGroupMap);
    }
  
  
  
  
  
    private array $paramDictionary = [];
    private string $regex;
    private bool $isParametric = false;
  
    /**
     * @param bool $isParametric
     */
    public function setIsParametric(bool $isParametric): void {
      $this->isParametric = $isParametric;
    }
    
    function isParametric(): bool {
      return $this->isParametric;
    }
  
    function getRegex(): string {
      return $this->regex;
    }
  
    function getParamDirectory(): array {
      return $this->paramDictionary;
    }
  
    function setRegex(string $regex) {
      $this->regex = $regex;
    }
  
    function setParamDirectory(array $dictionary) {
      $this->paramDictionary = $dictionary;
    }
  }
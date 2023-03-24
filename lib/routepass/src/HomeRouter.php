<?php
  require_once __DIR__ . "/Router.php";
  require_once __DIR__ . "/Response.php";
  require_once __DIR__ . "/Request.php";
  require_once __DIR__ . "/RequestError.php";
  
  class HomeRouter extends Router {
    private static $instance;
    public static function getInstance (): HomeRouter {
      if (!isset(self::$instance)) {
        self::$instance = new HomeRouter();
      }
      
      return self::$instance;
    }
    
    
    
    /** @var Router[]  */
    protected $parametricDomains = [];
    /** @var Router[]  */
    protected $staticDomains = [];
    public function __construct () {
      parent::__construct();
      
      $this->onAnyErrorEvent(function (RequestError $requestError) {
        exit($requestError->message);
      });
      
      $this->setBodyParser(self::BODY_PARSER_URLENCODED());
    }
  
  
    /**
     * Set Router object to specific domain format.
     *
     * ### Domain format
     *
     * Static domains
     * - users.example.com
     *
     * Dynamic domains
     * - [mySubDomain].example.com
     * - mySubDomain is accessible within `$request->domain->get("mySubDomain")`
     * - Regular expression can be added to dynamic domain parameter by passing map of keys (names of domains) and values (regex capture group /(0-9)/)
     * @param string $domainPattern
     * @param Router $router
     * @param array $domainCaptureGroupMap
     * @return void
     */
    public function domain (string $domainPattern, RouterLike $router, array $domainCaptureGroupMap = []) {
      if (strpos($domainPattern, "[") === false) {
        // static domain
        $this->staticDomains[$domainPattern] = $router;
      } else {
        // parametric domain
        $dictI = 1;
        $dict = [];
  
        $domain = "";
        $format = "/^";
        $registerDomain = function () use (&$format, &$domain, &$domainCaptureGroupMap, &$dict, &$dictI) {
          if ($domain !== "") {
            $format .= $domainCaptureGroupMap[$domain] ?? "([^.]+)";
            $dict[$dictI++] = $domain;
            $domain = "";
          }
        };
  
        $doAppendToDomain = false;
        for ($i = 0; $i < strlen($domainPattern); $i++) {
          if ($domainPattern[$i] == "[") {
            $doAppendToDomain = true;
            continue;
          }
    
          if ($domainPattern[$i] == "]") {
            $registerDomain();
            $doAppendToDomain = false;
            continue;
          }
    
          ${$doAppendToDomain ? "domain" : "format"} .= $domainPattern[$i];
        }
  
        $registerDomain();
        $format .= "$/";
  
        $this->parametricDomains[$format] = $router;
        $router->domainDictionary = $dict;
      }
      
      $router->setParent($this);
    }
    public function static (string $urlPattern, string $absoluteDirectoryPath, array $paramCaptureGroupMap = []) {
      $realAbsoluteDirectoryPath = realpath($absoluteDirectoryPath);
      
      $staticRouter = new Router();
      $staticRouter->options("/*", [function (Request $request, Response $response) {
        $response->setHeader(Response::HEADER_CORS_METHODS, "GET");
        $response->setHeader(Response::HEADER_CORS_HEADERS, "access-control-allow-origin");
        $response->setHeader(Response::HEADER_CORS_CREDENTIALS, "true");
        $response->setHeader(Response::HEADER_CORS_ORIGIN, "*");
        $response->flush();
      }]);
      $staticRouter->get("/*", [function (Request $request, Response $response) use ($absoluteDirectoryPath, $realAbsoluteDirectoryPath) {
        $filePath = realpath("$absoluteDirectoryPath/$request->remainingURI");
        
        if (strpos($filePath, $realAbsoluteDirectoryPath) === false) {
          $request->homeRouter->dispatchError(
            self::ERROR_REQUEST_OUT_OF_STATIC_DIRECTORY,
            new RequestError("Request '$request->fullURI' is referencing outside of static folder: '$realAbsoluteDirectoryPath'", $request, $response)
          );
          return;
        }
        
        if (is_dir($filePath)) {
          $basenameMapper = function ($item) {
            return basename($item);
          };
          
          $files = glob("$filePath/*.*");
          $directories = glob("$filePath/*/");
          
          $path = explode("/", $request->remainingURI);
          
          $response->renderFile(__DIR__ . "/directory-walker.php", [
            "home" => $absoluteDirectoryPath,
            "path" => $path[0] !== "" ? $path : [],
            "files" => array_map($basenameMapper, $files),
            "directories" => array_map($basenameMapper, $directories)
          ]);
        }
        
        $response->setHeader(Response::HEADER_CORS_ORIGIN, "*");

        $type = Response::getMimeType($filePath)
          ->forwardFailure($response)
          ->getSuccess();
  
        if (preg_match("/^image\//", $type) !== false) {
          $response->sendOptimalImage(
            $filePath,
            $request
          );
        }
        
        $response->setHeader("Content-Type", $type);
        
        if (preg_match("/\.php$/", $filePath)) {
          $response->generateHeaders();
          $response->renderFile($filePath);
        }
        
        $response->readFile($filePath);
      }], ["filePath" => Router::REGEX_ANY]);
      
      parent::use($urlPattern, $staticRouter, $paramCaptureGroupMap);
    }
    public function serve () {
      if (preg_match("/[a-z]+:\/\/[a-zA-Z-_.]+(.*)/", $_SERVER["REQUEST_URI"], $groups)) {
        $_SERVER["REQUEST_URI"] = $groups[1];
      }
      
      $home = "";
      $dir = dirname($_SERVER["SCRIPT_FILENAME"]);
    
      for ($i = 0; $i < strlen($dir); $i++) {
        if (!(isset($_SERVER["DOCUMENT_ROOT"][$i]) && $_SERVER["DOCUMENT_ROOT"][$i] == $dir[$i])){
          $home .= $dir[$i];
        }
      }
      
      $_SERVER["HOME_DIR"] = $home;
      $_SERVER["HOME_DIR_PATH"] = $dir;
      
      $res = new Response();
      $httpCode = http_response_code();
      if ($httpCode === 404 || $httpCode === 405) {
        http_response_code(200);
//        $res->setStatusCode(Response::OK);
      }
      
      $req = new Request(
        $res,
        $this,
        $this->getFlag(self::FLAG_SESSION_AUTO_START),
        $this->getFlag(self::FLAG_SESSION_COOKIE_PARAMS)
      );
      
      
  
      if ($this->getFlag(HomeRouter::FLAG_MAIN_SERVER_HOST_NAME) !== null) {
        $_SERVER["SERVER_HOME"] = "$req->protocol://" . $this->getFlag(HomeRouter::FLAG_MAIN_SERVER_HOST_NAME) . "$_SERVER[HOME_DIR]";
      }
      
      $this->bodyParser->call($this, file_get_contents('php://input'), $req);
    
      $req->trimQueries();
      $uri = self::filterEmpty(explode("/", substr($_SERVER["REQUEST_PATH"], strlen($home))));
      
      if (isset($this->staticDomains[$_SERVER["HTTP_HOST"]])) {
        $this->staticDomains[$_SERVER["HTTP_HOST"]]->execute($uri, 0, $req, $res);
        exit;
      }
      
      foreach ($this->parametricDomains as $regex => $domainRouter) {
        if (preg_match($regex, $_SERVER["HTTP_HOST"], $matches)) {
          foreach ($domainRouter->domainDictionary as $key => $domain) {
            $req->domain->set($domain, $matches[$key]);
          }
    
          $domainRouter->execute($uri, 0, $req, $res);
          exit;
        }
      }
      
      $this->home->execute($uri, 0, $req, $res);
      
      switch ($req->getState()) {
        case self::ERROR_ENDPOINT_DOES_NOT_EXISTS: {
          $this->dispatchError(
            self::ERROR_ENDPOINT_DOES_NOT_EXISTS,
            new RequestError("Endpoint does not exist for '$req->fullURI'", $req, $res)
          );
          break;
        }
        case self::ERROR_HTTP_METHOD_NOT_IMPLEMENTED: {
          $req->homeRouter->dispatchError(
            HomeRouter::ERROR_HTTP_METHOD_NOT_IMPLEMENTED,
            new RequestError("HTTP method: '$_SERVER[REQUEST_METHOD]' is not implemented for '$req->fullURI'", $req, $res)
          );
          break;
        }
      }
    }
    private function displayTrace ($trace, $indent = "  ") {
      foreach ($trace as $key => $arrayOfEndpoints) {
        if (is_string($arrayOfEndpoints)) {
          echo " <span style='color: crimson'>$arrayOfEndpoints</span>";
          continue;
        }
        
        if (count($arrayOfEndpoints) !== 0) {
          foreach ($arrayOfEndpoints as $endpoint => $nodesTrace) {
            echo "<br>";
            if ($key === "static") {
              echo "$indent/$endpoint";
            } else {
              echo "$indent$endpoint:";
            }
            $this->displayTrace($nodesTrace, $indent . "  ");
          }
        }
      }
    }
    private function routerMapper (): Closure {
      return function (RouterLike $router) {
        return $router->getEndpoints();
      };
    }
    public function showTrace () {
      $home = "";
      $dir = dirname($_SERVER["SCRIPT_FILENAME"]);
  
      for ($i = 0; $i < strlen($dir); $i++) {
        if (!(isset($_SERVER["DOCUMENT_ROOT"][$i]) && $_SERVER["DOCUMENT_ROOT"][$i] == $dir[$i])){
          $home .= $dir[$i];
        }
      }
      
      echo "<pre>";
      echo "\n$home";
      $this->displayTrace($this->getEndpoints());
      echo "\nDomains:";
      $this->displayTrace([
        "static" => array_map($this->routerMapper(), $this->staticDomains),
        "parametric" => array_map($this->routerMapper(), $this->parametricDomains)
      ]);
      echo "</pre>";
    }
    
    
    const ERROR_HTTP_METHOD_NOT_IMPLEMENTED = "http-method-is-not-implemented";
    const ERROR_ENDPOINT_DOES_NOT_EXISTS = "endpoint-does-not-exists";
    const ERROR_PROPERTY_NOT_FOUND = "property-not-found";
    const ERROR_REQUEST_OUT_OF_STATIC_DIRECTORY = "request-out-of-static-directory";
    
    const REQUEST_SERVED = "request-served";
    
    /** @var Closure[] $errorHandlers */
    private $errorHandlers = [];
    
    public function dispatchError ($errorID, $errorEvent) {
      $this->errorHandlers[$errorID]->call($errorEvent, $errorEvent);
    }
    
    public function onErrorEvent ($errorID, Closure $handler) {
      $this->errorHandlers[$errorID] = $handler;
    }
    
    public function onAnyErrorEvent (Closure $handler) {
      $this->onErrorEvent(self::ERROR_HTTP_METHOD_NOT_IMPLEMENTED, $handler);
      $this->onErrorEvent(self::ERROR_ENDPOINT_DOES_NOT_EXISTS, $handler);
      $this->onErrorEvent(self::ERROR_PROPERTY_NOT_FOUND, $handler);
      $this->onErrorEvent(self::ERROR_REQUEST_OUT_OF_STATIC_DIRECTORY, $handler);
    }
    
    
    public function setViewDirectory ($directory) {
      $_SERVER["VIEW_DIR"] = $directory;
    }
    
    private $flags = [
      self::FLAG_RESPONSE_AUTO_FLUSH => true,
      self::FLAG_SESSION_COOKIE_PARAMS => [],
      self::FLAG_SESSION_AUTO_START => true
    ];
    
    
    /**
     * When callback is called and next function is not called the response will be automatically sent.
     *
     * Expected value type: boolean
     *
     * Type: bool
     *
     * Default: `true`
     */
    public const FLAG_RESPONSE_AUTO_FLUSH = "RESPONSE_AUTO_FLUSH";
  
    /**
     * On each request automatically starts session.
     *
     * Type: bool
     *
     * Default: `true`
     */
    public const FLAG_SESSION_AUTO_START = "SESSION_AUTO_START";
  
    /**
     * Sets parameters for session cookie in order of function `session_set_cookie_params()` arguments.
     *
     * Type: array,
     *
     * Default: `[]`
     */
    public const FLAG_SESSION_COOKIE_PARAMS = "SESSION_COOKIE_PARAMS";
  
    /**
     * Set host name for main server, which will be used to create `__SERVER_HOME__` for views
     *
     * example: (value set to "localhost")
     *
     * http://`localhost`/test-directory/index.php
     *
     * Type: string
     */
    public const FLAG_MAIN_SERVER_HOST_NAME = "MAIN_SERVER_HOST_NAME";
  
    /**
     * @param $flag
     * @param mixed|null $value requires not null value. If null is passed this method is skipped.
     * @return void
     */
    public function setFlag ($flag, $value = null) {
      if ($value === null) return;
      
      $this->flags[$flag] = $value;
    }
    public function getFlag ($flag) {
      return $this->flags[$flag] ?? null;
    }
  
    /**
     * @var Closure $bodyParser
     */
    private $bodyParser;
    public function setBodyParser (Closure $bodyParser) {
      $this->bodyParser = $bodyParser;
    }
  
    /**
     * Parses body as a json object {}, if the main object is array the body will be that array even if `$convertToRegistry` is set to true.
     *
     * RequestFile upload is only accessible with HTTP POST method and Content-Type: "multipart/form-data" thus when this header is set, the body will automatically become Register object with set values.
     *
     * If array is sent, it is stored under "array" property on body object, use this to access it: `$request->body->get("array")`
     * @return Closure
     */
    public static function BODY_PARSER_JSON (): Closure {
      return function ($bodyContents, Request $request) {
        $request->body = new RequestRegistry($request);

        if (!(strpos($request->getHeader("Content-Type"), "multipart/form-data") === false)) {
          $request->body->load($_POST);
          return;
        }
  
        $json = json_decode($bodyContents);
        if (is_array($json)) {
          $request->body->set("array", $json);
          return;
        }
        
        if ($json !== null) {
          foreach ($json as $key => $value) {
            $request->body->set($key, $value);
          }
        }
      };
    }
  
    /**
     * Parses body as text. Stored under "text" property on body object, use this to access it: `$request->body->get("text")`
     * @return Closure
     */
    public static function BODY_PARSER_TEXT (): Closure {
      return function ($bodyContents, Request $request) {
        $request->body = new RequestRegistry($request);
  
        if (!(strpos($request->getHeader("Content-Type"), "multipart/form-data") === false) && !empty($_POST)) {
          foreach ($_POST as $key => $value) {
            $bodyContents .= urlencode($key) . "=" . urlencode($value) . "&";
          }
          $bodyContents = substr($bodyContents, 0, -1);
        }
        
        $request->body->set("text", $bodyContents);
      };
    }
  
    /**
     * Parses body as urlencoded string and its entries are stored as properties on body object.
     *
     * If Content-Type header contains multipart/form-data (file upload) the remaining entries will be parsed as urlencoded string. You may use `Request::parseURLEncoded($request->body->get("text"), $request->body)` to populate the `$request->body` registry with key-value pairs.
     * @return Closure
     */
    public static function BODY_PARSER_URLENCODED (): Closure {
      return function ($bodyContents, Request $request) {
        $request->body = new RequestRegistry($request);
        
        if (!(strpos($request->getHeader("Content-Type"), "multipart/form-data") === false)) {
          $request->body->load($_POST);
          return;
        }
        
        Request::parseURLEncoded($bodyContents, $request->body);
      };
    }
  }
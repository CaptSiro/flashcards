<?php
  
  require_once __DIR__ . "/RouterLike.php";
  require_once __DIR__ . "/RouterPromise.php";
  
  class RouterPromise extends RouterLike implements Parametric {
    private string $fileName;
    private Router $router;
  
    /**
     * @throws Exception
     */
    private function getRouter (): RouterLike {
      if (!isset($this->router)) {
        $this->router = require $this->fileName;
      }
      
      if (!$this->router instanceof RouterLike) {
        $type = gettype($this->router);
        if ($type === "object") {
          $type = get_class($this->router);
        }
        
        throw new Exception("File must export RouterLike object got: $type");
      }
      
      return $this->router;
    }
  
    /**
     * @throws Exception
     */
    public function getHome(): PathNode {
      return $this->getRouter()->getHome();
    }
  
    /**
     * @throws Exception
     */
    public function setHome(PathNode $home) {
      $this->getRouter()->setHome($home);
    }
  
    public function __construct (string $fileName) {
      $this->fileName = $fileName;
    }
  
    /**
     * @inheritDoc
     * @throws Exception
     */
    public function createPath(array $uriParts, array &$paramCaptureGroupMap = []): Node {
      return $this->getRouter()->createPath($uriParts, $paramCaptureGroupMap);
    }
  
    /**
     * @inheritDoc
     * @throws Exception
     */
    protected function assign(string &$httpMethod, array &$uriParts, array &$callbacks, array &$paramCaptureGroupMap = []) {
      $this->getRouter()->assign($httpMethod, $uriParts, $callbacks, $paramCaptureGroupMap);
    }
  
    /**
     * @throws Exception
     */
    protected function setMethod(string &$httpMethod, array &$callbacks) {
      $this->getRouter()->setMethod($httpMethod, $callbacks);
    }
  
    /**
     * @throws Exception
     */
    protected function execute(array &$uri, int $uriIndex, Request &$request, Response &$response) {
      $this->getRouter()->execute($uri, $uriIndex, $request, $response);
    }
  
    /**
     * @throws Exception
     */
    public function getEndpoints(): array {
      return $this->getRouter()->getEndpoints();
    }
  
    /**
     * @throws Exception
     */
    public function getCallbacks(array $list = []): array {
      return $this->getRouter()->getCallbacks($list);
    }
  
    /**
     * @throws Exception
     */
    public function use(string $uriPattern, RouterLike $router, array $paramCaptureGroupMap = []) {
      $this->getRouter()->use($uriPattern, $router, $paramCaptureGroupMap);
    }
  
    /**
     * @throws Exception
     */
    public function implement(Closure ...$callbacks) {
      $this->getRouter()->implement(...$callbacks);
    }
  
    /**
     * @throws Exception
     */
    function isParametric(): bool {
      return $this->getRouter()->isParametric();
    }
  
    /**
     * @throws Exception
     */
    function setIsParametric($bool) {
      $this->getRouter()->setIsParametric($bool);
    }
  
    /**
     * @throws Exception
     */
    function getRegex(): string {
      return $this->getRouter()->getRegex();
    }
  
    /**
     * @throws Exception
     */
    function getParamDirectory(): array {
      return $this->getRouter()->getParamDirectory();
    }
  
    /**
     * @throws Exception
     */
    function setRegex(string $regex) {
      $this->getRouter()->setRegex($regex);
    }
  
    /**
     * @throws Exception
     */
    function setParamDirectory(array $dictionary) {
      $this->getRouter()->setParamDirectory($dictionary);
    }
  }
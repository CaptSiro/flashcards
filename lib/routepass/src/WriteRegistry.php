<?php
  
  require_once __DIR__ . "/RequestRegistry.php";
  
  class WriteRegistry extends RequestRegistry {
    private $writeFunction;
    
    public function __construct (Request $request, Closure $writeFunction) {
      parent::__construct($request);
      $this->writeFunction = $writeFunction;
    }
  
    protected function setValue($propertyName, $value) {
      return $this->writeFunction->call($this, $propertyName, $value);
    }
  }
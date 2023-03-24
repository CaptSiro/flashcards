<?php
  
  require_once __DIR__ . "/StrictRegistry.php";
  require_once __DIR__ . "/RequestError.php";
  
  class RequestRegistry extends StrictRegistry {
    private $request;
    
    public function __construct (Request $request) {
      $this->request = $request;
    }
  
    protected function propNotFound($propertyName) {
      $this->request->homeRouter->dispatchError(
        HomeRouter::ERROR_PROPERTY_NOT_FOUND,
        new RequestError("'$propertyName' is required for this operation.", $this->request, $this->request->response)
      );
    }
  
    protected function setValue($propertyName, $value) {
      return $value;
    }
  }
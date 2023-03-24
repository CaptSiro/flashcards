<?php
  
  require_once __DIR__ . "/Request.php";
  require_once __DIR__ . "/Response.php";
  
  class RequestError {
    public $message, $request, $response;
    
    public function __construct (string $message, Request $request, Response $response) {
      $this->message = $message;
      $this->request = $request;
      $this->response = $response;
    }
  }
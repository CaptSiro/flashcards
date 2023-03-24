<?php
  
  require_once __DIR__ . "/../../retval/retval.php";
  
  class Env {
    private $map = [];
    function __construct ($file) {
      $this->map["__ENV_FILE__"] = $file;
      
      $handle = fopen($file, "r");
      if ($handle) {
        while (($line = fgets($handle)) !== false) {
          if (preg_match("/^([a-zA-Z_]+[a-zA-Z0-9_]*)=([^#]*).*/", rtrim($line), $matches)) {
            $this->map[$matches[1]] = rtrim($matches[2]);
          }
        }
        
        fclose($handle);
      }
    }
    
    public function looselyGet (string $name) {
      return $this->map[$name] ?? null;
    }
    
    public function get ($name): Result {
      if (!isset($this->map[$name])) {
        return fail(new NotFoundExc("Could not find $name in " . $this->map["__ENV_FILE__"]));
      }
      
      return success($this->map[$name]);
    }
    
    function __get ($name) {
      if (!isset($this->map[$name])) {
        return null;
      }
      
      return $this->map[$name];
    }
  }
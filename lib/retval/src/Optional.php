<?php
  
  class Optional {
    /**
     * @var mixed $value
     */
    private $value;
    
    public function value () {
      return $this->value;
    }
    
    private bool $is_none;
    
    public function __construct ($value, bool $is_none) {
      $this->value = $value;
      $this->is_none = $is_none;
    }
    
    
    
    public function is_none () {
      return $this->is_none;
    }
    
    
    
    public function or ($default) {
      if ($this->is_none) {
        return $default;
      }
      
      return $this->value;
    }
    
    
    
    function to_result () {
      if ($this->is_none) {
        return fail(new NotFoundExc("Could not found resource."));
      }
      
      return success($this->value);
    }
  }
  
  function some ($value): Optional {
    return new Optional($value, false);
  }
  
  function none (): Optional {
    return new Optional(null, true);
  }
  
  function optional_row($database_row): Optional {
    if ($database_row === false || $database_row === null) {
      return none();
    }
    
    return some($database_row);
  }
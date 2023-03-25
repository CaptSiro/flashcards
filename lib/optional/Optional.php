<?php
  
  class Optional {
    /**
     * @var mixed $value
     */
    private $value;
    
    public function value () {
      return $this->value;
    }
    
    private bool $outcome;
    
    public function __construct ($value, bool $is_none) {
      $this->value = $value;
      $this->outcome = $is_none;
    }
    
    
    
    public function is_none () {
      return $this->outcome;
    }
    
    
    
    public function or ($default) {
      if ($this->outcome) {
        return $default;
      }
      
      return $this->value;
    }
  }
  
  function some ($value): Optional {
    return new Optional($value, false);
  }
  
  function none (): Optional {
    return new Optional(null, true);
  }
<?php
  
  abstract class StrictRegistry {
    private $map = [];
    public function getMap (): array {
      return $this->map;
    }
    
    
    protected $useSerializedValues = false;
    public function enableSerializedValues () {
      $this->useSerializedValues = true;
    }
    public function disableSerializedValues () {
      $this->useSerializedValues = false;
    }
  
    
    
    private function optionallySerializeValue ($value) {
      return $this->useSerializedValues
        ? serialize($value)
        : $value;
    }
    
    
    
    private function optionallyDeserializeValue ($value) {
      return $this->useSerializedValues
        ? unserialize($value)
        : $value;
    }
  
    
    
    abstract protected function propNotFound ($propertyName);
    abstract protected function setValue ($propertyName, $value);
    
    
  
    public function looselyGet ($propertyName, $default = null) {
      if (isset($this->map[$propertyName])) {
        return $this->optionallyDeserializeValue($this->map[$propertyName]);
      }
    
      return $default;
    }
    
    
    
    public function get ($propertyName) {
      if (!$this->isset($propertyName)) {
        $this->propNotFound($propertyName);
      }
  
      return $this->optionallyDeserializeValue($this->map[$propertyName]);
    }
    
    
    
    public function set ($propertyName, $value) {
      $modified = $this->setValue($propertyName, $value);
      
      if ($modified === null) return null;
  
      return $this->map[$propertyName] = $this->optionallySerializeValue($modified);
    }
    
    
    
    public function modify ($propertyName, Closure $modifier) {
      return $this->set($propertyName, $modifier($this->map[$propertyName]));
    }
    
    
    
    public function isset ($propertyName): bool {
      return isset($this->map[$propertyName]);
    }
    
    
    
    public function unset (string $propertyName) : void {
      $this->setValue($propertyName, null);
      unset($this->map[$propertyName]);
    }
  
    
    
    
    public function stringify (): string {
      $return = "{";
    
      foreach ($this->map as $key => $value) {
        $return .= "\n\t\"$key\": \"$value\",";
      }
    
      $return .= "\n}\n";
    
      return $return;
    }
    
    
    
    public function load (array ...$dictionaries) {
      foreach ($dictionaries as $dictionary) {
        foreach ($dictionary as $name => $value) {
          $this->map[$name] = $value;
        }
      }
    }
    
    public function discard () {
      $this->map = [];
    }
  }
<?php

  class Cookie {
    public $value, $expires, $path, $domain, $secure, $httpOnly;
  
    public function __construct (
      $value = "",
      int $expires = 0,
      string $path = "",
      string $domain = "",
      bool $secure = false,
      bool $httpOnly = false
    ) {
      $this->value = $value;
      $this->expires = $expires;
      $this->path = $path;
      $this->domain = $domain;
      $this->secure = $secure;
      $this->httpOnly = $httpOnly;
    }
  
    public function set ($name) {
      setcookie(
        $name,
        serialize($this->value),
        $this->expires,
        $this->path,
        $this->domain,
        $this->secure,
        $this->httpOnly
      );
    }
  }
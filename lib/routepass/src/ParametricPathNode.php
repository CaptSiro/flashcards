<?php

  require_once __DIR__ . "/PathNode.php";
  require_once __DIR__ . "/Parametric.php";

  class ParametricPathNode extends PathNode implements Parametric {
    public array $paramDictionary = [];
    public string $regex;
    
    public function upgrade (PathNode $pathNode): ParametricPathNode {
      $this->parent = $pathNode->parent;
      $this->pathPart = $pathNode->pathPart;
      $this->static = $pathNode->static;
      $this->parametric = $pathNode->parametric;
      $this->handles = $pathNode->handles;
      return $this;
    }
  
    function isParametric(): bool {
      return true;
    }
  
    function getRegex(): string {
      return $this->regex;
    }
  
    function getParamDirectory(): array {
      return $this->paramDictionary;
    }
  
    function setRegex(string $regex) {
      $this->regex = $regex;
    }
  
    function setParamDirectory(array $dictionary) {
      $this->paramDictionary = $dictionary;
    }
  }
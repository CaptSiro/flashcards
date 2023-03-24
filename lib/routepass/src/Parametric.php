<?php
  
  interface Parametric {
    function isParametric(): bool;
    
    function getRegex(): string;
    function getParamDirectory(): array;
    function setRegex(string $regex);
    function setParamDirectory(array $dictionary);
  }
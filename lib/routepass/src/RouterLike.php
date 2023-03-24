<?php
  
  require_once __DIR__ . "/Node.php";
  
  abstract class RouterLike extends Node {
    protected static function filterEmpty (array $toBeFiltered): array {
      $return = [];
      foreach ($toBeFiltered as $fragment) {
        if ($fragment != "") {
          $return[] = $fragment;
        }
      }
    
      return $return;
    }
  
    /**
     * Assign Router to URI Pattern.
     * @param string $uriPattern
     * @param Router $router
     * @param array $paramCaptureGroupMap
     * @return void
     */
    abstract public function use (string $uriPattern, RouterLike $router, array $paramCaptureGroupMap = []);
    abstract public function implement (Closure ...$callbacks);
    abstract public function getHome (): PathNode;
    abstract public function setHome (PathNode $home);
  }
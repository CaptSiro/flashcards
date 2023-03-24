<?php

  abstract class Node {
    /**
     * Returns last node responsible for handling requests.
     */
    abstract public function createPath (array $uriParts, array &$paramCaptureGroupMap = []): Node;
    /**
     * Assigns callbacks on last node of set path. 
     */
    abstract protected function assign (string &$httpMethod, array &$uriParts, array &$callbacks, array &$paramCaptureGroupMap = []);
    abstract protected function setMethod (string &$httpMethod, array &$callbacks);
    abstract protected function execute (array &$uri, int $uriIndex, Request &$request, Response &$response);
    abstract public function getEndpoints (): array;
    abstract public function getCallbacks (array $list = []): array;
  
    /**
     * @var Node
     */
    protected $parent;
    /**
     * @var string
     */
    protected $pathPart;
    
    public function getParent(): ?Node {
      return $this->parent;
    }
    public function setParent(?Node $parent) {
      $this->parent = $parent;
    }
  
    public function getPathPart(): string {
      return $this->pathPart;
    }
    public function setPathPart(string $part) {
      $this->pathPart = $part;
    }
    
    public function getRootParent (): Node {
      if (!isset($this->parent)) {
        return $this;
      }
  
      $curr = $this->parent;
      while ($curr->getParent() !== null) {
        $curr = $curr->getParent();
      }
  
      return $curr;
    }
  }
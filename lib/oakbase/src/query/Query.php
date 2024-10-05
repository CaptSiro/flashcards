<?php

namespace OakBase;

require_once __DIR__ . "/QueryBuilder.php";
require_once __DIR__ . "/../buffer/Buffer.php";



class Query {
    protected string $string;
    protected Buffer $params;

    public function __construct(string $string, Buffer $params) {
        $this->string = $string;
        $this->params = $params;
    }

    public static function build(): QueryBuilder {
        return new QueryBuilder();
    }

    public function string(): string {
        return $this->string;
    }

    public function params(): Buffer {
        return $this->params;
    }
}
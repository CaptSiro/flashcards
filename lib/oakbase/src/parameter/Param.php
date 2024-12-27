<?php

namespace OakBase;

interface Param {
    function __toString(): string;



    /**
     * `__toString` function is overloaded with custom behaviour that adds it current buffer. Use this function to print
     * contents of Param
     * @return string
     */
    function to_string(): string;



    /**
     * Return value to be used in database query
     *
     * @return mixed
     */
    function value();



    /**
     * Return any of PDO::PARAM_* constants
     *
     * @return int
     */
    function type(): int;



    /**
     * Return string that was returned by `__toString()` method or null to use indexed based binding (`"?"` was returned)
     */
    function name(): ?string;
}
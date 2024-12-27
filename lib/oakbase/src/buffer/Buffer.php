<?php

namespace OakBase;

interface Buffer {
    function add($value);



    function shift();



    function is_empty(): bool;



    /**
     * Get whole content of buffer
     * @return mixed
     */
    function dump();



    /**
     * Load all values into buffer. Whether the buffer is cleaned beforehand is implementation dependant
     * @param $values
     * @return mixed
     */
    function load($values);
}
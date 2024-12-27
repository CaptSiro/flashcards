<?php

require_once __DIR__ . "/../../retval/retval.php";



class Env {
    private array $map = [];

    /**
     * @param string $file Path to .env file
     */
    function __construct(string $file) {
        $this->map["__ENV_FILE__"] = $file;

        $handle = fopen($file, "r");
        if ($handle) {
            while (($line = fgets($handle)) !== false) {
                if (preg_match("/^([a-zA-Z_]+[a-zA-Z0-9_]*)=([^#]*).*/", rtrim($line), $matches)) {
                    $this->map[$matches[1]] = rtrim($matches[2]);
                }
            }

            fclose($handle);
        }
    }

    /**
     * If value does not exist null is returned
     * @param string $name
     * @return mixed|null
     */
    public function looselyGet(string $name) {
        return $this->map[$name] ?? null;
    }

    /**
     * If value does not exist failed result is returned
     * @param $name
     * @return Result
     */
    public function get($name): Result {
        if (!isset($this->map[$name])) {
            return fail(new NotFoundExc("Could not find $name in " . $this->map["__ENV_FILE__"]));
        }

        return success($this->map[$name]);
    }

    /**
     * If value does not exist the Application is crashed
     * @param $name
     * @return string
     */
    public function get_or_crash($name): string {
        if (!isset($this->map[$name])) {
            echo "Server: Environment variable '$name' must be set";
            exit();
        }

        return $this->map[$name];
    }

    function __get($name) {
        if (!isset($this->map[$name])) {
            return null;
        }

        return $this->map[$name];
    }
}
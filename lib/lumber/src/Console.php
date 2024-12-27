<?php



/**
 * Static class used for logging into file with simple formatting
 */
class Console {
    public static int $tabSize = 2;



    /**
     * Object is pretty printed into provided file
     * @param $object
     * @param string $f File name
     * @param string|null $dir If null then current directory of Console class is used
     * @return void
     */
    static function print($object, string $f = "log.txt", string $dir = null) {
        self::log(print_r($object, true), $f, $dir);
    }



    /**
     * Content is printed into provided file
     * @param $content
     * @param string $f File name
     * @param string|null $dir If null then current directory of Console class is used
     * @return void
     */
    static function log($content, string $f = "log.txt", string $dir = null) {
        $dir = ($dir == null)
            ? __DIR__
            : $dir;

        $path = $dir . "/" . $f;

        $temp = file_get_contents($path);
        file_put_contents($path, $content . "\n" . $temp);
    }



    /**
     * Header with content is printed into provided file with format `header: content`
     * @param $header
     * @param $content
     * @param string $f File name
     * @param string|null $dir If null then current directory of Console class is used
     * @return void
     */
    static function header($header, $content, string $f = "log.txt", string $dir = null) {
        self::log("$header: $content", $f, $dir);
    }



    /**
     * Content is logged with timestamp into provided file
     * @param $content
     * @param string $f File name
     * @param string|null $dir If null then current directory of Console class is used
     * @return void
     */
    static function date($content, string $f = "log.txt", string $dir = null) {
        self::log(date("Y-m-d H:i:s") . ": $content", $f, $dir);
    }



    /**
     * Last function call is printed into provided file
     * @param $content
     * @param string $f File name
     * @param string|null $dir If null then current directory of Console class is used
     * @return void
     */
    static function debug($content, string $f = "log.txt", string $dir = null) {
        $trace = debug_backtrace()[0];

        self::log($trace["file"] . "(" . $trace["line"] . "): $content\n", $f, $dir);
    }



    /**
     * Exc is logged into provided file
     * @param string $f File name
     * @param string|null $dir If null then current directory of Console class is used
     * @return void
     */
    static function exc(Exc $exc, string $f = "log.txt", string $dir = null) {
        $stackTrace = date("Y-m-d H:i:s") . " " . get_class($exc) . ": " . $exc->getMessage();
        $tab = str_repeat(" ", self::$tabSize);
        foreach ($exc->getTrace() as $trace) {
            $stackTrace .= "\n$tab$trace->file:$trace->line";
        }

        self::log($stackTrace, $f, $dir);
    }
}
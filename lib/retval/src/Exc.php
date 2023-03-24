<?php



  class Trace {
    public $file, $line;
    public function __construct ($f, $l) {
      $this->file = $f;
      $this->line = $l;
    }
  }

  class Exc implements JsonSerializable {
    protected $message;
    protected $trace = [];

    public function getMessage (): string {
      return $this->message;
    }

    public function getTrace (): array {
      return $this->trace;
    }

    public function __construct (string $msg) {
      $this->message = $msg;
      $backtrace = debug_backtrace();
      foreach ($backtrace as $t) {
        $this->trace[] = new Trace($t["file"], $t["line"]);
      }
    }

    public function bubbleUp () {
      $this->trace = [];
      $backtrace = debug_backtrace();
      foreach ($backtrace as $t) {
        $this->trace[] = new Trace($t["file"], $t["line"]);
      }
    }

    function jsonSerialize() {
      return (object)[
        "error" => $this->message,
        "trace" => $this->trace
      ];
    }
  }

  class TypeExc extends Exc {
    public function __construct ($msg) {
      parent::__construct($msg);
    }
  }

  class NotFoundExc extends Exc {
    public function __construct ($msg) {
      parent::__construct($msg);
    }
  }

  class NullPointerExc extends Exc {
    public function __construct ($msg) {
      parent::__construct($msg);
    }
  }

  class InvalidArgumentExc extends Exc {
    public function __construct ($msg) {
      parent::__construct($msg);
    }
  }

  class NotUniqueValueExc extends Exc {
    public function __construct ($msg) {
      parent::__construct($msg);
    }
  }
  
  class IllegalArgumentExc extends Exc {
    public function __construct ($msg) {
      parent::__construct($msg);
    }
  }
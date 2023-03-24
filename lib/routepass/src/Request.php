<?php
  
  require_once __DIR__ . "/WriteRegistry.php";
  require_once __DIR__ . "/Cookie.php";
  require_once __DIR__ . "/RequestFile.php";

  class Request {
    static function POST ($url, array $post = NULL, array $options = []) {
      $defaults = [
        CURLOPT_POST => 1,
        CURLOPT_HEADER => 0,
        CURLOPT_URL => $url,
        CURLOPT_FRESH_CONNECT => 1,
        CURLOPT_RETURNTRANSFER => 1,
        CURLOPT_FORBID_REUSE => 1,
        CURLOPT_TIMEOUT => 4,
        CURLOPT_POSTFIELDS => http_build_query($post)
      ];
    
      $chandler = curl_init();
      curl_setopt_array($chandler, ($options + $defaults));
      if (!$result = curl_exec($chandler)) {
        trigger_error(curl_error($chandler));
      }
      curl_close($chandler);
      return $result;
    }
    static function GET ($url, array $get = NULL, array $options = []) {
      $defaults = [
        CURLOPT_URL => $url . ((strpos($url, '?') === FALSE) ? '?' : '') . http_build_query($get),
        CURLOPT_HEADER => 0,
        CURLOPT_RETURNTRANSFER => TRUE,
        CURLOPT_TIMEOUT => 4
      ];
    
      $chandler = curl_init();
      curl_setopt_array($chandler, ($options + $defaults));
      if (!$result = curl_exec($chandler)){
        trigger_error(curl_error($chandler));
      }
      curl_close($chandler);
      return $result;
    }
  
    public static function parseURLEncoded (string $query, StrictRegistry &$registry) {
      $name = "";
      $value = "";
      $swap = false;
      
      for ($i = 0; $i < strlen($query); $i++) {
        if ($query[$i] == "=") {
          $swap = true;
          continue;
        }
      
        if ($query[$i] == "&") {
          $registry->set($name, urldecode($value));
          $name = "";
          $value = "";
          $swap = false;
          continue;
        }
      
        ${$swap ? "value" : "name"} .= $query[$i];
      }
    
      if ($name != "") {
        $registry->set($name, urldecode($value));
      }
    }
    
    public $httpMethod,
      $protocol,
      $host,
      $uri,
      $fullURI,
      /**
       * @var string $remainingURI
       */
      $remainingURI,
      $response,
      $homeRouter,
      $domain,
      /**
       * **Only accessible with POST HTTP method**
       * @var RequestRegistry $files
       * @var RequestRegistry $body
       */
      $files,
      $query,
      $param,
      $body,
      $session,
      $cookies, $trace;
      
    private $headers,
      $state = null;
  
    /**
     * @param mixed $state
     */
    public function setState($state): void {
      $this->state = $state;
    }
  
    /**
     * @return mixed
     */
    public function getState() {
      return $this->state;
    }
    
    public function getHeader ($header): string {
      return $this->headers[strtolower($header)] ?? "";
    }
    
    public static function getProtocol () {
      if (isset($_SERVER["HTTP_X_FORWARDED_PROTO"])) {
        return $_SERVER["HTTP_X_FORWARDED_PROTO"];
      }
      
      return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on')
        ? "https"
        : "http";
    }
    
    
    public function loadSession ($sessionID = null, array $cookieParams = []): bool {
      if (session_status() === PHP_SESSION_DISABLED) {
        return false;
      }
      
      if (session_status() === PHP_SESSION_ACTIVE) {
        session_commit();
      }
      
      if ($sessionID !== null) {
        session_id($sessionID);
      }
      
      if (!empty($cookieParams)) {
        session_set_cookie_params(...$cookieParams);
      }
      
      if (!session_start()) {
        return false;
      }
      
      $this->session->discard();
      $this->session->load($_SESSION);
      
      return true;
    }
    
    
    public function __construct (Response &$response, HomeRouter &$homeRouter, bool $doStartSession = true, array $sessionCookieParams = []) {
      $this->response = $response;
      $this->homeRouter = $homeRouter;
      $this->httpMethod = $_SERVER["REQUEST_METHOD"];
      $this->protocol = self::getProtocol();
      $this->host = $_SERVER["HTTP_HOST"];
      $this->uri = substr($_SERVER["REQUEST_URI"], strlen($_SERVER["HOME_DIR"]));
      $this->fullURI = "$this->protocol://$this->host$this->uri";
  
      $temp = apache_request_headers();
      array_walk($temp, function ($value, $key) {
        $this->headers[strtolower($key)] = $value;
      });
  

      
      $this->session = new WriteRegistry($this, function ($propertyName, $value) {
        $_SESSION[$propertyName] = $value;
        return $value;
      });
      if ($doStartSession) {
        $this->loadSession(null, $sessionCookieParams);
      }
      
      
      
      $this->cookies = new WriteRegistry($this, function ($propertyName, $value) {
        $cookie = $value;
  
        if (!$cookie instanceof Cookie) throw new Exception('Received value is not instance of Cookie.');
        
        $cookie->set($propertyName);
        return $cookie->value;
      });
      $this->cookies->enableSerializedValues();
      $this->cookies->load($_COOKIE);
      
      
      
      $this->files = new RequestRegistry($this);
      foreach ($_FILES as $key => $file) {
        if (is_array($file["error"])) {
          $fileArray = [];
          
          for ($i = 0; $i < count($file["error"]); $i++) {
            $fileArray[] = new RequestFile([
              "name" => $file["name"][$i],
              "type" => $file["type"][$i],
              "tmp_name" => $file["tmp_name"][$i],
              "error" => $file["error"][$i],
              "size" => $file["size"][$i],
            ]);
          }
          
          $this->files->set($key, $fileArray);
          continue;
        }
        
        $this->files->set($key, new RequestFile($file));
      }
      
      
      
      $this->param = new RequestRegistry($this);
      $this->domain = new RequestRegistry($this);
      $this->query = new RequestRegistry($this);
      $this->body = new RequestRegistry($this);
    }
  
    public function trimQueries () {
      $uri = $_SERVER["REQUEST_URI"];
      $_SERVER["REQUEST_PATH"] = $uri;
      
      $query = "";
      $path = "";
      $swap = true;
      for ($i = 0; $i < strlen($uri); $i++) {
        if ($uri[$i] == "?") {
          $swap = false;
          continue;
        }
        
        if ($swap) {
          $path .= $uri[$i];
        } else {
          $query .= $uri[$i];
        }
      }
  
      $_SERVER["REQUEST_PATH"] = $path;
      $_SERVER["QUERY_STRING"] = $query;
    
      self::parseURLEncoded($query, $this->query);
    }
  }
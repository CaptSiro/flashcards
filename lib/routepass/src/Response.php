<?php
  
  require_once __DIR__ . "/../../retval/retval.php";
  
  class Response {
    // Informational
      const CONTINUE = 100;
      const SWITCHING_PROTOCOLS = 101;
      const PROCESSING = 102;
  
    // Successful
      const OK = 200;
      const CREATED = 201;
      const ACCEPTED = 202;
      const NON_AUTHORITATIVE_INFORMATION = 203;
      const NO_CONTENT = 204;
      const RESET_CONTENT = 205;
      const PARTIAL_CONTENT = 206;
      const MULTI_STATUS = 207;
      const ALREADY_REPORTED = 208;
      const IM_USED = 226;
  
    // Redirection
      const MULTIPLE_CHOICES = 300;
      const MOVED_PERMANENTLY = 301;
      const FOUND = 302;
      const SEE_OTHER = 303;
      const NOT_MODIFIED = 304;
      const USE_PROXY = 305;
      const TEMPORARY_REDIRECT = 307;
      const PERMANENT_REDIRECT = 308;
  
    // Client Error
      const BAD_REQUEST = 400;
      const UNAUTHORIZED = 401;
      const PAYMENT_REQUIRED = 402;
      const FORBIDDEN = 403;
      const NOT_FOUND = 404;
      const METHOD_NOT_ALLOWED = 405;
      const NOT_ACCEPTABLE = 406;
      const PROXY_AUTHENTICATION_REQUIRED = 407;
      const REQUEST_TIMEOUT = 408;
      const CONFLICT = 409;
      const GONE = 410;
      const LENGTH_REQUIRED = 411;
      const PRECONDITION_FAILED = 412;
      const PAYLOAD_TOO_LARGE = 413;
      const URI_TOO_LONG = 414;
      const UNSUPPORTED_MEDIA_TYPE = 415;
      const REQUEST_RANGE_NOT_SATISFIABLE = 416;
      const EXPECTATION_FAILED = 417;
      const IM_A_TEAPOT = 418;
      const MISDIRECTED_REQUEST = 421;
      const UNPROCESSABLE_ENTITY = 422;
      const LOCKED = 423;
      const FAILED_DEPENDENCY = 424;
      const UPGRADE_REQUIRED = 426;
      const PRECONDITION_REQUIRED = 428;
      const TOO_MANY_REQUESTS = 429;
      const REQUEST_HEADERS_TOO_LARGE = 431;
      const CONNECTION_CLOSED_WITHOUT_RESPONSE = 444;
      const UNAVAILABLE_FOR_LEGAL_REASONS = 451;
      const CLIENT_CLOSED_REQUEST = 499;
  
    // Server Error
      const INTERNAL_SERVER_ERROR = 500;
      const NOT_IMPLEMENTED = 501;
      const BAD_GATEWAY = 502;
      const SERVICE_UNAVAILABLE = 503;
      const GATEWAY_TIMEOUT = 504;
      const HTTP_VERSION_NOT_SUPPORTED = 505;
      const VARIANT_ALSO_NEGOTIATES = 506;
      const INSUFFICIENT_STORAGE = 507;
      const LOOP_DETECTED = 508;
      const NOT_EXTENDED = 510;
      const NETWORD_AUTHENTICATION_REQUIRED = 510;
      const NETWORK_CONNECT_TIMEOUT_ERROR = 599;
  
    static function propNotFound () {
      return function (string $httpMethod, string $propertyName) {
        $response = new Response();
        $response->setStatusCode(Response::NOT_FOUND);
        $response->error("$propertyName is required for this operation. (method: $httpMethod)");
      };
    }
    
    
    private $headers = [];
    const HEADER_CORS_ORIGIN = "Access-Control-Allow-Origin";
    const HEADER_CORS_HEADERS = "Access-Control-Allow-Headers";
    const HEADER_CORS_METHODS = "Access-Control-Allow-Methods";
    const HEADER_CORS_CREDENTIALS = "Access-Control-Allow-Credentials";
  
    /**
     * @return array
     */
    public function getHeaders(): array {
      return $this->headers;
    }
    public function hasHeader (string $header) {
      return isset($this->headers[$header]);
    }
    public function setHeader (string $header, string $value) {
      $this->headers[$header] = $value;
    }
    public function setAllHeaders (array ...$headers) {
      foreach ($headers as $header) {
        $this->headers[$header[0]] = $header[1];
      }
    }
    public function setCORS (string $methods = "GET, HEAD, POST, PUT, PATCH, DELETE") {
      $this->setHeader(Response::HEADER_CORS_METHODS, $methods);
      $this->setHeader(Response::HEADER_CORS_HEADERS, "access-control-allow-origin");
      $this->setHeader(Response::HEADER_CORS_CREDENTIALS, "true");
    
      $origin = isset($_SERVER["HTTP_REFERER"])
        ? substr($_SERVER["HTTP_REFERER"], 0, strlen($_SERVER["HTTP_REFERER"]) - 1)
        : "*";
      $this->setHeader(Response::HEADER_CORS_ORIGIN, $origin);
    }
    public static function getMimeType (string $file): Result {
      if (!file_exists($file)) {
        return fail(new NotFoundExc("Could not find file: '$file'"));
      }
    
      $mimeType = mime_content_type($file);
    
      if (preg_match("/\.css$/", $file)) {
        $mimeType = "text/css";
      }
    
      if (preg_match("/\.js$/", $file)) {
        $mimeType = "text/javascript";
      }
    
      return success($mimeType);
    }
    public function removeHeader (string $header) {
      unset($this->headers[$header]);
    }
    public function removeAllHeaders () {
      $this->headers = [];
    }
    
    private $statusCode = 200;
  
    /**
     * @return int
     */
    public function getStatusCode(): int {
      return $this->statusCode;
    }
    public function setStatusCode (int $code) {
      $text = "";
      
      switch ($code) {
        case 100: $text = 'Continue'; break;
        case 101: $text = 'Switching Protocols'; break;
        case 102: $text = 'Processing'; break;
        
        case 200: $text = 'OK'; break;
        case 201: $text = 'Created'; break;
        case 202: $text = 'Accepted'; break;
        case 203: $text = 'Non-Authoritative Information'; break;
        case 204: $text = 'No Content'; break;
        case 205: $text = 'Reset Content'; break;
        case 206: $text = 'Partial Content'; break;
        case 207: $text = 'Multi-Status'; break;
        case 208: $text = 'Already Reported'; break;
        case 226: $text = 'IM Used'; break;
        
        case 300: $text = 'Multiple Choices'; break;
        case 301: $text = 'Moved Permanently'; break;
        case 302: $text = 'Moved Temporarily'; break;
        case 303: $text = 'See Other'; break;
        case 304: $text = 'Not Modified'; break;
        case 305: $text = 'Use Proxy'; break;
        case 307: $text = 'Temporary Redirect'; break;
        case 308: $text = 'Permanent Redirect'; break;
        
        case 400: $text = 'Bad Request'; break;
        case 401: $text = 'Unauthorized'; break;
        case 402: $text = 'Payment Required'; break;
        case 403: $text = 'Forbidden'; break;
        case 404: $text = 'Not Found'; break;
        case 405: $text = 'Method Not Allowed'; break;
        case 406: $text = 'Not Acceptable'; break;
        case 407: $text = 'Proxy Authentication Required'; break;
        case 408: $text = 'Request Time-out'; break;
        case 409: $text = 'Conflict'; break;
        case 410: $text = 'Gone'; break;
        case 411: $text = 'Length Required'; break;
        case 412: $text = 'Precondition Failed'; break;
        case 413: $text = 'Request Entity Too Large'; break;
        case 414: $text = 'Request-URI Too Large'; break;
        case 415: $text = 'Unsupported Media Type'; break;
        case 416: $text = 'Requested Range Not Satisfiable'; break;
        case 417: $text = 'Expectation Failed'; break;
        case 418: $text = 'I\'m a teapot'; break;
        case 421: $text = 'Misdirected Request'; break;
        case 422: $text = 'Unprocessable Entity'; break;
        case 423: $text = 'Locked'; break;
        case 424: $text = 'Failed Dependency'; break;
        case 426: $text = 'Upgrade Required'; break;
        case 428: $text = 'Precondition Required'; break;
        case 429: $text = 'Too Many Requests'; break;
        case 431: $text = 'Request Header Fields Too Large'; break;
        case 444: $text = 'Connection Closed Without Response'; break;
        case 451: $text = 'Unavailable For Legal Reasons'; break;
        case 499: $text = 'Client Closed Request'; break;
        
        case 500: $text = 'Internal Server Error'; break;
        case 501: $text = 'Not Implemented'; break;
        case 502: $text = 'Bad Gateway'; break;
        case 503: $text = 'Service Unavailable'; break;
        case 504: $text = 'Gateway Time-out'; break;
        case 505: $text = 'HTTP Version not supported'; break;
        case 506: $text = 'Variant Also Negotiates'; break;
        case 507: $text = 'Insufficient Storage'; break;
        case 508: $text = 'Loop Detected'; break;
        case 510: $text = 'Not Extended'; break;
        case 511: $text = 'Network Authentication Required'; break;
        case 599: $text = 'Network Connect Timeout Error'; break;
        
        default:
          $this->error('Unknown http status code "' . htmlentities($code) . '"', Response::INTERNAL_SERVER_ERROR);
          break;
      }
  
      $this->setHeader($_SERVER['SERVER_PROTOCOL'] ?? 'HTTP/1.0', "$code $text");
      $this->statusCode = $code;
    }
    
    private bool $alreadyGeneratedHeaders = false;
    public function generateHeaders () {
      if ($this->alreadyGeneratedHeaders) {
        return;
      }
      
      foreach ($this->headers as $header => $value) {
        if (preg_match("/^HTTP\/[0-9.]+$/", $header)) {
          header("$header $value");
          continue;
        }
        header("$header: $value");
      }
      
      $this->alreadyGeneratedHeaders = true;
    }
  
  
    /**
     * Exits the execution without sending any data but headers will be sent.
     */
    public function flush () {
      $this->generateHeaders();
      exit();
    }
    /**
     * Alias of Response::flush()
     */
    public function end () { $this->flush(); }
    /**
     * Exits the execution.
     *
     * Sends string data to user.
     */
    public function send ($text) {
      echo $text;
      $this->flush();
    }
    /**
     * Exits the execution.
     *
     * Parses object into JSON text representation and sends it to the user.
     */
    public function json ($jsonEncodeAble, $jsonEncodeFlags = 0, $jsonEncodeDepth = 512) {
      echo(json_encode($jsonEncodeAble, $jsonEncodeFlags, $jsonEncodeDepth));
      $this->flush();
    }
    /**
     * Exits the execution with error code and message.
     */
    public function error (string $message, int $httpStatusCode = -1) {
      if ($httpStatusCode !== -1) {
        $this->setStatusCode($httpStatusCode);
      }
  
      $this->send($message);
    }
    public function fail ($exc) {
      $this->json($exc);
    }
    private function fileExists ($file) {
      if (file_exists($file)) return;
      
      $this->error("RequestFile not found: $file", self::NOT_FOUND);
    }
    
    private function imageFromFile (string $fileName) {
      $this->fileExists($fileName);
  
      switch (strtolower(pathinfo($fileName, PATHINFO_EXTENSION))) {
        case 'jpg':
        case 'jpeg': return imagecreatefromjpeg($fileName);
        
        case 'png': return imagecreatefrompng($fileName);
        case 'gif': return imagecreatefromgif($fileName);
        default: return imagecreatefromstring(
          file_get_contents($fileName)
        );
      }
    }
    
    private function sendImageResource ($image, string $type) {
      imagesavealpha($image, true);
      
      if ($type !== "image/gif") {
        $this->setHeader("Content-Type", "image/png");
        imagepng($image, null, 0);
        return;
      }

      $this->setHeader("Content-Type", "image/gif");
      imagegif($image);
    }
  
    private const WIDTH = 0;
    private const HEIGHT = 1;
    public function sendScaledImage (string $filePath, int $width, int $height = -1, bool $doOverScaleImage = false) {
      $type = Response::getMimeType($filePath)
        ->forwardFailure($this)
        ->getSuccess();
  
      $size = getimagesize($filePath);
  
      if ($size[self::WIDTH] < $width && !$doOverScaleImage) {
        $this->setHeader("Content-Type", $type);
        $this->readFile($filePath);
      }
  
      $image = $this->imageFromFile($filePath);
  
      $scaledImage = imagescale($image, $width !== -1 ? $width : $size[self::WIDTH], $height);
  
      $this->sendImageResource($scaledImage, $type);
      imagedestroy($scaledImage);
      imagedestroy($image);
      
      $this->flush();
    }
    
    public function sendCroppedImage (string $filePath, int $width, int $height = -1) {
      $type = Response::getMimeType($filePath)
        ->forwardFailure($this)
        ->getSuccess();
  
      $size = getimagesize($filePath);
      
      $width = $width === -1
        ? $size[self::WIDTH]
        : min($size[self::WIDTH], $width);
      
      $height = $height === -1
        ? $size[self::HEIGHT]
        : min($size[self::HEIGHT], $height);
  
      if ($size[self::WIDTH] === $width && $size[self::HEIGHT] === $height) {
        $this->setHeader("Content-Type", $type);
        $this->readFile($filePath);
      }
      
      $x = ($size[self::WIDTH] - $width) / 2;
      $y = ($size[self::HEIGHT] - $height) / 2;
  
      $image = $this->imageFromFile($filePath);
  
      $croppedImage = imagecrop($image, [
        "x" => $x,
        "y" => $y,
        "width" => $width,
        "height" => $height,
      ]);
  
      $this->sendImageResource($croppedImage, $type);
      imagedestroy($croppedImage);
      imagedestroy($image);
      
      $this->flush();
    }
    
    public function sendCroppedAndScaledImage (string $filePath, int $width, int $height = -1) {
      require_once __DIR__ . "/../../lumber/lumber.php";
      
      $size = getimagesize($filePath);
      $cropWidth = $width === -1 ? $size[self::WIDTH] : $width;
      $cropHeight = $height === -1 ? $cropWidth : $height;
  
      if ($cropWidth < $cropHeight) {
        $scaledHeight = $cropHeight;
        $scaledWidth = ($size[self::WIDTH] / $size[self::HEIGHT]) * $cropHeight;
      } else {
        $scaledWidth = $cropWidth;
        $scaledHeight = ($size[self::HEIGHT] / $size[self::WIDTH]) * $cropWidth;
      }
      
      $image = $this->imageFromFile($filePath);
  
      $scaledImage = &$image;
  
      if ($size[self::WIDTH] < $scaledWidth) {
        $cropWidth = ($size[self::WIDTH] / $scaledWidth) * $cropWidth;
        $cropHeight = ($size[self::HEIGHT] / $scaledHeight) * $cropHeight;
        $scaledWidth = $size[self::WIDTH];
        $scaledHeight = $size[self::HEIGHT];
      } else {
        $scaledImage = imagescale($image, $scaledWidth, $scaledHeight);
      }
  
      $x = ($scaledWidth - $cropWidth) / 2;
      $y = ($scaledHeight - $cropHeight) / 2;
  
      $croppedImage = imagecrop($scaledImage, [
        "x" => $x,
        "y" => $y,
        "width" => $cropWidth,
        "height" => $cropHeight,
      ]);
  
      $this->sendImageResource(
        $croppedImage,
        Response::getMimeType($filePath)->forwardFailure($this)->getSuccess()
      );
      
      imagedestroy($croppedImage);
      imagedestroy($scaledImage);
      imagedestroy($image);
  
      $this->flush();
    }
  
    /**
     * Optimal image size depends on query parameters in request.
     *
     * <b>`width`<b> - in pixels
     *
     * <b>`height`<b> - in pixels (if left undefined, the image will be scaled by width, and it will retain same aspect ratio)
     *
     * <b>`crop=true`<b> - cropping will be used instead of default scaling
     *
     * <b>`cropAndScale=true`<b> - before cropping the image, the image is scaled down
     *
     * @param $filePath
     * @param Request $request
     * @return void
     */
    public function sendOptimalImage ($filePath, Request $request) {
      $this->fileExists($filePath);
      
      $areDimensionsSet = $request->query->isset("width") || $request->query->isset("height");
      if (!$areDimensionsSet) {
        $this->setHeader(
          "Content-Type",
          Response::getMimeType($filePath)
            ->forwardFailure($this)
            ->getSuccess()
        );
        $this->readFile($filePath);
      }
      
      if ($request->query->isset("cropAndScale")) {
        $this->sendCroppedAndScaledImage(
          $filePath,
          intval($request->query->looselyGet("width", -1)),
          intval($request->query->looselyGet("height", -1))
        );
      }
      
      if ($request->query->isset("crop")) {
        $this->sendCroppedImage(
          $filePath,
          intval($request->query->looselyGet("width", -1)),
          intval($request->query->looselyGet("height", -1))
        );
      }
      
      $this->sendScaledImage(
        $filePath,
        intval($request->query->looselyGet("width", -1)),
        intval($request->query->looselyGet("height", -1))
      );
    }
    
    /**
     * Reads file and sends it contents to the user.
     *
     * **This function does not download the file on user's end. It only sends file's contents.**
     */
    public function readFile (string $file, bool $doFlushResponse = true) {
      $this->fileExists($file);
      $this->generateHeaders();
      readfile($file);
      
      if ($doFlushResponse) {
        exit();
      }
    }
    
    public function readFileSafe (string $file, bool $doFlushResponse = true) {
      $this->fileExists($file);
      $this->generateHeaders();
      
      echo htmlspecialchars(file_get_contents($file));
  
      if ($doFlushResponse) {
        exit();
      }
    }
    /**
     * Exits the execution.
     *
     * Checks for valid file path and sets headers to download it.
     */
    public function download (string $file, string $downloadAs = null) {
      $this->fileExists($file);
      
      $this->setAllHeaders(
        ["Content-Description", "RequestFile Transfer"],
        ["Content-Type", 'application/octet-stream'],
        ["Content-Disposition", "attachment; filename=" . ($downloadAs ?? basename($file))],
        ["Pregma", "_public"],
        ["Content-Length", filesize($file)]
      );
      
      $this->generateHeaders();
      
      readfile($file);
    }
  
    /**
     * Wrapper for renderFile where file is path to the view. If view directory is not set projects directory will be used instead. Use whole path to the view file: "path/to/the/view" without extension.
     *
     * @param string $view
     * @param array $locals
     * @param string $extension
     * @param bool $doFlushResponse
     * @return void
     */
    public function render (string $view, array $locals = [], string $extension = "php", bool $doFlushResponse = true) {
      $this->renderFile(
        ($_SERVER["VIEW_DIR"] ?? $_SERVER["HOME_DIR_PATH"]) . "/$view.$extension",
        $locals,
        $doFlushResponse
      );
    }
  
    /**
     * Reads file and sets local variables to the file. Key values will be used as name of the variable.
     *
     * $locals = ["number" => 8]
     *   -> accessible with '$number' and $GLOBALS["number"]
     * @param string $filePath
     * @param array $locals
     * @param bool $doFlushResponse
     * @return void
     */
    public function renderFile (string $filePath, array $locals = [], bool $doFlushResponse = true) {
      if (!file_exists($filePath)) {
        $this->error("Could not find view: $filePath", self::NOT_FOUND);
      }
      
      $locals["__HOME__"] = $_SERVER["HOME_DIR"];
      if (isset($_SERVER["SERVER_HOME"])) {
        $locals["__SERVER_HOME__"] = $_SERVER["SERVER_HOME"];
      }
  
      $predefined = [];
      $predefinedGlobal = [];
      foreach ($locals as $_______name_prefix_will_be_never_used => $value) {
        if (isset($$_______name_prefix_will_be_never_used)) {
          $predefined[$_______name_prefix_will_be_never_used] = $value;
        }
    
        $$_______name_prefix_will_be_never_used = $value;
        
        if (isset($GLOBALS[$_______name_prefix_will_be_never_used])) {
          $predefinedGlobal[$_______name_prefix_will_be_never_used] = $value;
        }
        
        $GLOBALS[$_______name_prefix_will_be_never_used] = $value;
      }
  
      require $filePath;
  
      foreach ($locals as $_______name_prefix_will_be_never_used => $value) {
        unset($$_______name_prefix_will_be_never_used);
        unset($GLOBALS[$_______name_prefix_will_be_never_used]);
      }
  
      foreach ($predefined as $_______name_prefix_will_be_never_used => $value) {
        $$_______name_prefix_will_be_never_used = $value;
      }
  
      foreach ($predefinedGlobal as $_______name_prefix_will_be_never_used => $value) {
        $GLOBALS[$_______name_prefix_will_be_never_used] = $value;
      }
  
      if ($doFlushResponse) {
        $this->flush();
      }
    }
  
  
    /**
     * Redirects request to new URL.
     *
     * Do prepend home directory is used to dynamically prepend directory structure that is between server directory and this project's directory.
     *
     * www/ **my-project** /index.php -> localhost/ **my-project** / **'/my-project'** will be prepended
     *
     * `/api/user` -> `/my-project/api/user`
     * @param string $url accepts same URLs as Location header.
     * @param bool $doPrependHomeDirectory
     * @return void
     */
    public function redirect (string $url, bool $doPrependHomeDirectory = true) {
      $this->setHeader("Location", self::createRedirectURL($url, $doPrependHomeDirectory));
      $this->flush();
    }
  
    /**
     * Do prepend home directory is used to dynamically prepend directory structure that is between server directory and this project's directory.
     *
     * www/ **my-project** /index.php -> localhost/ **my-project** / **'/my-project'** will be prepended
     *
     * `/api/user` -> `/my-project/api/user`
     * @param string $url
     * @param bool $doPrependHomeDirectory
     * @return string
     */
    public static function createRedirectURL (string $url, bool $doPrependHomeDirectory = true): string {
      return ($doPrependHomeDirectory
        ? $_SERVER["SERVER_HOME"]
        : "") . $url;
    }
    
    public static function createRedirectURLDirPrefix (string $url): string {
      return "$_SERVER[HOME_DIR]$url";
    }
  
  
    /**
     * @param string $id must follow file name guidelines
     * @return void
     */
    public static function createEventStream (string $id) {
    
    }
  }
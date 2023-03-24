<?php
  
  
  
  class RequestFile {
    public $name, $ext, $fullName, $type, $temporaryName, $error, $size;
    
    public function __construct ($file) {
      $this->fullName = $file["name"];
      [$name, $ext] = self::getExtension($this->fullName);
      $this->name = $name;
      $this->ext = $ext;
      $this->type = $file["type"];
      $this->temporaryName = $file["tmp_name"];
      $this->error = $file["error"];
      $this->size = $file["size"];
    }
    
    public function moveTo (string $destination): Result {
      if ($this->error !== UPLOAD_ERR_OK) {
        return fail(new Exc("Error occurred when uploading file: '$this->fullName'. Code: '$this->error'"));
      }
      
      move_uploaded_file($this->temporaryName, $destination);
      
      return success(true);
    }
    
    const FILE_NAME = 0;
    const FILE_EXTENSION = 1;
  
    /**
     * @param $path
     * @return string[] [FILE_NAME, FILE_EXTENSION]
     */
    public static function getExtension ($path): array {
      $ext = "";
      $name = "";
      $switch = false;
      
      for ($i = (strlen($path) - 1); $i >= 0; $i--) {
        $var = &${$switch ? "name" : "ext"};
        $var = $path[$i] . $var;
        
        if ($path[$i] == "." && !$switch) {
          $switch = true;
        }
      }
  
      if ($name === "") {
        return [
          self::FILE_NAME => $ext,
          self::FILE_EXTENSION => ""
        ];
      }
  
      return [
        self::FILE_NAME => $name,
        self::FILE_EXTENSION => $ext
      ];
    }
  }
<?php
  
  use OakBase\Database;
  use OakBase\Param;
  
  class User {
    public string $password;
    public string $username;
    public int $id;
    
    
    
    function verify($not_hashed_password): Result {
      if (!password_verify($not_hashed_password, $this->password)) {
        return fail(new InvalidArgumentExc("Password does not match."));
      }
      
      return success(true);
    }
  
  
  
    static function by_id (Param $id): Result {
      $result = Database::get()->fetch(
        "SELECT id, username, password FROM users WHERE id = $id",
        self::class
      );
  
      if ($result === false || $result === null) {
        return fail(new NotFoundExc("Can not find user with id: ". $id->value()));
      }
      
      return success($result);
    }
    
    
    
    static function by_username (Param $username): Result {
      $result = Database::get()->fetch(
        "SELECT id, username, password FROM users WHERE username = $username",
        self::class
      );
      
      if ($result === false || $result === null) {
        return fail(new NotFoundExc("Can not find user with username: ". $username->value()));
      }
      
      return success($result);
    }
    
    
    
    static function insert (Param $username, Param $hashed_password): Result {
      return success(Database::get()->statement(
        "INSERT INTO users (username, password)
        VALUE ($username, $hashed_password)"
      ));
    }
  }
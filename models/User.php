<?php
  
  use OakBase\Database;
  use OakBase\Param;
  use OakBase\PrimitiveParam;
  
  class User {
    public string $email;
    public int $id;
  
  
  
    static function by_id (Param $id): Result {
      $result = Database::get()->fetch(
        "SELECT id, email FROM users WHERE id = $id",
        self::class
      );
  
      if ($result === false || $result === null) {
        return fail(new NotFoundExc("Can not find user with id: ". $id->value()));
      }
      
      return success($result);
    }
    
    
    
    static function by_email (Param $email): Result {
      $result = Database::get()->fetch(
        "SELECT id, email FROM users WHERE email = $email",
        self::class
      );
      
      if ($result === false || $result === null) {
        return fail(new NotFoundExc("Can not find user with email: ". $email->value()));
      }
      
      return success($result);
    }
    
    
    
    static function insert (Param $email): Result {
      return success(Database::get()->statement(
        "INSERT INTO users (email) VALUE ($email)"
      ));
    }
  }
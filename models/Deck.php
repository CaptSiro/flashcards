<?php
  
  use OakBase\Database;
  use OakBase\PrimitiveParam;
  
  require_once __DIR__ . "/Count.php";
  
  class Deck {
    public int $id;
    public string $name;
    public int $users_id;
    
    
    
    static function insert(string $name, int $user_id): Result {
      $name = new PrimitiveParam($name);
      $user_id = new PrimitiveParam($user_id);
      
      $is_unique = Database::get()->fetch(
        "SELECT COUNT(id) as amount
            FROM decks
            WHERE `name` = $name",
        Count::class
      )->amount === 0;
      
      if (!$is_unique) {
        return fail(new NotUniqueValueExc("Name must be unique."));
      }
      
      return success(Database::get()->statement(
        "INSERT INTO decks (name, users_id) VALUE ($name, $user_id)"
      ));
    }
    
    
    
    static function by_id(int $id): Result {
      $id = new PrimitiveParam($id);
      
      $deck = Database::get()->fetch(
        "SELECT id, name, users_id
            FROM decks
            WHERE id = $id",
        self::class
      );
      
      if ($deck === false) {
        return fail(new NotFoundExc("There are no decks for id: ". $id->value()));
      }
      
      return success($deck);
    }
    
    
    
    static function users(int $user_id): Result {
      $user_id = new PrimitiveParam($user_id);
  
      $deck = Database::get()->fetch_all(
        "SELECT id, name, users_id
            FROM decks
            WHERE users_id = $user_id",
        self::class
      );
  
      if ($deck === false) {
        return fail(new NotFoundExc("There are no decks for user with id: ". $user_id->value()));
      }
  
      return success($deck);
    }
  }
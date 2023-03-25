<?php
  
  use OakBase\Database;
  use OakBase\Param;
  
  require_once __DIR__ . "/Count.php";
  
  class Stack {
    public int $id;
    public string $name;
    public int $decks_id;
    
    
    
    static function insert(Param $name, Param $deck_id): Result {
      $is_unique = Database::get()->fetch(
          "SELECT COUNT(id) as amount
            FROM stacks
            WHERE `name` = $name",
          Count::class
        )->amount === 0;
  
      if (!$is_unique) {
        return fail(new NotUniqueValueExc("Name must be unique."));
      }
  
      return success(Database::get()->statement(
        "INSERT INTO stacks (name, decks_id) VALUE ($name, $deck_id)"
      ));
    }
    
    
    
    static function in_deck(Param $deck_id): Result {
      $stacks = Database::get()->fetch_all(
        "SELECT id, name, decks_id
            FROM stacks
            WHERE decks_id = $deck_id",
        self::class
      );
      
      if ($stacks === false) {
        return fail(new NotFoundExc("There are no stacks for deck with id: ". $deck_id->value()));
      }
      
      return success($stacks);
    }
  }
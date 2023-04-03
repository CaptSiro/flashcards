<?php
  
  use OakBase\Database;
  use OakBase\Param;
  use OakBase\PrimitiveParam;
  use OakBase\SideEffect;
  
  require_once __DIR__ . "/Count.php";
  
  class Stack {
    public int $id;
    public string $name;
    public int $decks_id;
    public int $rank;
    
  
  
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
    
    
    
    static function in_deck(Param $deck_id, Param $user_id): Result {
      $stacks = Database::get()->fetch_all(
        "SELECT stacks.id, name, p.`rank`, fractions.fraction
        FROM stacks
            JOIN privileges p on stacks.decks_id = p.decks_id
                AND p.users_id = $user_id
            LEFT JOIN (
                SELECT stacks_id, MAX(fraction) as fraction
                FROM results
                WHERE users_id = $user_id
                GROUP BY stacks_id
            ) as fractions ON fractions.stacks_id = stacks.id
        WHERE p.decks_id = $deck_id",
        self::class
      );
      
      if ($stacks === false) {
        return fail(new NotFoundExc("There are no stacks for deck with id: ". $deck_id->value()));
      }
      
      return success($stacks);
    }
    
    
    
    static function by_id(Param $id, Param $user_id): Result {
      $stack = Database::get()->fetch(
        "SELECT stacks.id, name, p.`rank`, p.decks_id
            FROM stacks
                JOIN privileges p on stacks.decks_id = p.decks_id
                    AND p.users_id = $user_id
            WHERE stacks.id = $id",
        self::class
      );
      
      if ($stack === false || $stack === null) {
        return fail(new NotFoundExc("There are no stacks for id: ". $id->value()));
      }
      
      return success($stack);
    }
  
  
  
    static function by_deck_id(Param $deck_id): Result {
      $stack = Database::get()->fetch_all(
        "SELECT stacks.id, name, p.`rank`, p.decks_id
            FROM stacks
                JOIN privileges p on stacks.decks_id = p.decks_id
                    AND p.decks_id = $deck_id",
        self::class
      );
    
      if ($stack === false) {
        return fail(new NotFoundExc("There are no stacks for deck with id: ". $deck_id->value()));
      }
    
      return success($stack);
    }
    
    
    
    static function delete(Param $id): Result {
      Database::get()->statement(
        "DELETE FROM cards_in_stacks WHERE stacks_id = $id"
      );
  
      Database::get()->statement(
        "DELETE c
            FROM cards c
            LEFT JOIN cards_in_stacks cis on c.id = cis.cards_id
            WHERE cis.stacks_id IS NULL"
      );
      
      Database::get()->statement(
        "DELETE FROM results WHERE stacks_id = $id"
      );
  
      $stack = Database::get()->statement(
        "DELETE FROM stacks WHERE id = $id"
      );
  
      if ($stack->row_count() === 0) {
        return fail(new Exc("Could not remove stack."));
      }
  
      return success($stack);
    }
    
    
    
    function delete_self(): Result {
      return self::delete(
        new PrimitiveParam($this->id)
      );
    }
    
    
    
    static function update(Param $id, Param $name): SideEffect {
      return Database::get()->statement(
        "UPDATE stacks SET name = $name WHERE id = $id"
      );
    }
  }
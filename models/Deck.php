<?php
  
  use OakBase\Database;
  use OakBase\Param;
  use function OakBase\param;
  
  require_once __DIR__ . "/Count.php";
  require_once __DIR__ . "/Privilege.php";
  
  class Deck {
    public int $id;
    public string $name;
    public int $rank;
    
    
    
    static function insert(Param $name, Param $user_id): Result {
      $is_unique = Database::get()->fetch(
        "SELECT COUNT(id) as amount
            FROM decks
            WHERE `name` = $name",
        Count::class
      )->amount === 0;
      
      if (!$is_unique) {
        return fail(new NotUniqueValueExc("Name must be unique."));
      }
      
      $side_effect = Database::get()->statement(
        "INSERT INTO decks (name) VALUE ($name)"
      );
      
      if ($side_effect->last_inserted_ID() === 0) {
        return fail(new NotUniqueValueExc("Could not create deck."));
      }
      
      Privilege::insert(
        param(0),
        param($side_effect->last_inserted_ID()),
        $user_id
      );
      
      return success($side_effect);
    }
    
    
    
    static function by_id(Param $id): Result {
      $deck = Database::get()->fetch(
        "SELECT decks.id, `name`, p.rank
            FROM decks
            JOIN privileges p ON decks.id = p.decks_id
                AND p.decks_id = $id",
        self::class
      );
      
      if ($deck === false || $deck === null) {
        return fail(new NotFoundExc("There are no decks for id: ". $id->value()));
      }
      
      return success($deck);
    }
    
    
    
    static function users(Param $user_id): Result {
      $deck = Database::get()->fetch_all(
        "SELECT decks.id, name, p.rank
            FROM decks
            JOIN privileges p ON decks.id = p.decks_id
                AND p.users_id = $user_id",
        self::class
      );
  
      if ($deck === false) {
        return fail(new NotFoundExc("There are no decks for user with id: ". $user_id->value()));
      }
  
      return success($deck);
    }
  }
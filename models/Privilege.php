<?php
  
  use OakBase\Database;
  use OakBase\Param;
  use function OakBase\param;
  
  require_once __DIR__ . "/Count.php";

  class Privilege {
    public int $id;
    public int $rank;
    public int $deks_id;
    public int $users_id;
    
    
    const PRIVILEGES = [0, 1, 2];
    static function insert(Param $rank, Param $deck_id, Param $user_id): Result {
      $result = Database::get()->statement(
        "INSERT INTO privileges (`rank`, decks_id, users_id)
            VALUE ($rank, $deck_id, $user_id)"
      );
      
      if ($result->last_inserted_ID() === 0) {
        return fail(new Exc("Could not insert privilege."));
      }
      
      return success($result);
    }
  }
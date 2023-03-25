<?php
  
  use OakBase\Database;
  use OakBase\Param;
  use function OakBase\param;
  
  require_once __DIR__ . "/Count.php";
  require_once __DIR__ . "/Stack.php";
  
  class Card {
    public int $id;
    public string $question;
    public string $answer;
    public int $decks_id;
    
    
    
    static function insert(Param $question, Param $answer, Param $stack_id) {
      $is_unique = Database::get()->fetch(
          "SELECT COUNT(id) as amount
            FROM cards
            WHERE question = $question
                AND answer = $answer",
          Count::class
        )->amount === 0;
  
      if (!$is_unique) {
        return fail(new NotUniqueValueExc("Card must be unique."));
      }
      
      $stack = Stack::by_id($stack_id);
      
      if ($stack->isFailure()) {
        return $stack;
      }
      
      $stack = $stack->getSuccess();
      /**
       * @var Stack $stack
       */
      
      $side_effect = Database::get()->statement(
        "INSERT INTO cards (question, answer, decks_id)
            VALUE ($question, $answer, ". param($stack->decks_id) .")",
      );
      
      $card_id = param($side_effect->last_inserted_ID());
      
      return success(Database::get()->statement(
        "INSERT INTO cards_in_stacks (cards_id, stacks_id)
            VALUE ($card_id, $stack_id)"
      ));
    }
    
    
    
    static function in_stack(Param $stack_id): Result {
      $cards = Database::get()->fetch_all(
        "SELECT id, question, answer, decks_id
            FROM cards_in_stacks
            JOIN cards c ON cards_in_stacks.cards_id = c.id
                AND stacks_id = $stack_id",
        self::class
      );
      
      if ($cards === false) {
        return fail(new NotFoundExc("There are no cards in stack with id: ". $stack_id->value()));
      }
      
      return success($cards);
    }
  }
<?php
  
  use OakBase\Database;
  use OakBase\Param;
  use OakBase\SideEffect;
  use function OakBase\param;
  
  require_once __DIR__ . "/Count.php";
  require_once __DIR__ . "/Stack.php";
  
  class Card {
    public int $id;
    public string $question;
    public string $answer;
    public int $decks_id;
    public int $rank;
    
    
    
    static function insert(Param $question, Param $answer, Param $stack_id, Param $user_id) {
      $stack = Stack::by_id($stack_id, $user_id);
      
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
  
      Database::get()->statement(
        "INSERT INTO cards_in_stacks (cards_id, stacks_id)
            VALUE ($card_id, $stack_id)"
      );
      
      return success($side_effect);
    }
    
    
    
    static function in_stack(Param $stack_id, Param $user_id): Result {
      $cards = Database::get()->fetch_all(
        "SELECT c.id, question, answer, p.`rank`, question_images.sources as question_images, answer_images.sources as answer_images
        FROM cards_in_stacks
            JOIN cards c ON cards_in_stacks.cards_id = c.id
                AND stacks_id = $stack_id
            JOIN privileges p on c.decks_id = p.decks_id
                AND p.users_id = $user_id
            LEFT JOIN (
                SELECT
                    ai.cards_id,
                    GROUP_CONCAT(CONCAT(i.src, i.ext) SEPARATOR '/') sources
                FROM images as i
                    LEFT JOIN answer_images ai ON i.src = ai.images_src
                GROUP BY ai.cards_id
            ) as answer_images ON answer_images.cards_id = c.id
            LEFT JOIN (
                SELECT
                    qi.cards_id,
                    GROUP_CONCAT(CONCAT(i.src, i.ext) SEPARATOR '/') sources
                FROM images as i
                    LEFT JOIN question_images qi ON i.src = qi.images_src
                GROUP BY qi.cards_id
            ) as question_images ON question_images.cards_id = c.id",
        self::class
      );
      
      if ($cards === false) {
        return fail(new NotFoundExc("There are no cards in stack with id: ". $stack_id->value()));
      }
      
      return success($cards);
    }
    
    
    
    // todo add privileges
    static function delete(Param $id): Result {
      Database::get()->statement(
        "DELETE FROM cards_in_stacks WHERE cards_id = $id"
      );
      
      $card = Database::get()->statement(
        "DELETE FROM cards WHERE id = $id LIMIT 1"
      );
      
      if ($card->row_count() === 0) {
        return fail(new Exc("Could not remove card."));
      }
      
      return success($card);
    }
    
    
    
    static function by_id(Param $id): Result {
      $card = Database::get()->fetch(
        "SELECT id, question, answer, decks_id
        FROM cards
        WHERE id = $id",
        Card::class
      );
      
      if ($card === null || $card === false) {
        return fail(new NotFoundExc("Could not find card with id: ". $id->value()));
      }
      
      return success($card);
    }
    
    
    
    static function update(Param $id, Param $question, Param $answer): SideEffect {
      return Database::get()->statement(
        "UPDATE cards SET question = $question, answer = $answer WHERE id = $id"
      );
    }
  }
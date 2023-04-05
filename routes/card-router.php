<?php
  
  use OakBase\SideEffect;
  use function OakBase\param;
  
  require_once __DIR__ . "/../lib/routepass/routers.php";
  require_once __DIR__ . "/../lib/path.php";
  
  require_once __DIR__ . "/../models/Card.php";
  require_once __DIR__ . "/../models/Stack.php";
  require_once __DIR__ . "/../models/Privilege.php";
  require_once __DIR__ . "/../models/Image.php";
  
  require_once __DIR__ . "/Middleware.php";
  
  
  
  $card_router = new Router();
  
  
  
  $card_router->post("/", [
    Middleware::requireToBeLoggedIn(),
    function (Request $request, Response $response) {
      $user_id = param($request->session->get("user")->id);
      $stack_id = param($request->body->get("stack_id"));

      $stack = Stack::by_id(
        $stack_id,
        $user_id
      )
        ->forwardFailure($response)
        ->getSuccess();

      Privilege::check(
        $user_id,
        param($stack->decks_id),
        [Privilege::RANK_CREATOR, Privilege::RANK_EDITOR]
      )
        ->forwardFailure($response);
      
      $valid_question_files = [];
      $question_count = 0;
      foreach ($request->files->looselyGet("question_images", []) as $file) {
        /**
         * @var RequestFile $file
         */
        if ($question_count === 5) {
          break;
        }
        
        if (strpos($file->type, "image/") === false) {
          continue;
        }
        
        $valid_question_files[] = $file;
        $question_count++;
      }
  
      $valid_answer_files = [];
      $answer_count = 0;
      foreach ($request->files->looselyGet("answer_images", []) as $file) {
        /**
         * @var RequestFile $file
         */
        if ($answer_count === 5) {
          break;
        }
        
        if (strpos($file->type, "image/") === false) {
          continue;
        }
    
        $valid_answer_files[] = $file;
        $answer_count++;
      }
      
      
      
      if (!$request->body->isset("question") && $question_count === 0) {
        $response->fail(new InvalidArgumentExc("You must ask a question. Either type it in or append image with the question."));
      }
  
      if (!$request->body->isset("answer") && $answer_count === 0) {
        $response->fail(new InvalidArgumentExc("You must answer the question. Either type it in or append image with the answer."));
      }
  
      
      /**
       * @var SideEffect $side_effect
       */
      $side_effect = Card::insert(
        param($request->body->looselyGet("question")),
        param($request->body->looselyGet("answer")),
        $stack_id,
        $user_id
      )
        ->forwardFailure($response)
        ->getSuccess();
      
      if ($side_effect->last_inserted_ID() === 0) {
        $response->fail(new Exc("Could not add card to database."));
      }
      
      $card_id = param($side_effect->last_inserted_ID());
      
      
      foreach ($valid_question_files as $file) {
        /**
         * @var RequestFile $file
         */
        $src = Image::src_gen()
          ->forwardFailure($response)
          ->getSuccess();
        
        $file->moveTo(FILES_DIR . "/$src$file->ext");
        
        Image::insert(param($src), param($file->ext));
        Image::insert_question(param($src), $card_id);
      }
  
      foreach ($valid_answer_files as $file) {
        /**
         * @var RequestFile $file
         */
        $src = Image::src_gen()
          ->forwardFailure($response)
          ->getSuccess();
    
        $file->moveTo(FILES_DIR . "/$src$file->ext");
    
        Image::insert(param($src), param($file->ext));
        Image::insert_answer(param($src), $card_id);
      }
      
      
      $response->json($side_effect);
    }
  ]);
  
  
  
  $card_router->delete("/:id", [
    Middleware::requireToBeLoggedIn(),
    function (Request $request, Response $response) {
      $card_id = param($request->param->get("id"));
    
      $card = Card::by_id($card_id)
        ->forwardFailure($response)
        ->getSuccess();
  
      Privilege::check(
        param($request->session->get("user")->id),
        param($card->decks_id),
        [Privilege::RANK_CREATOR, Privilege::RANK_EDITOR]
      )
        ->forwardFailure($response);
      
      $response->json(Card::delete(
        $card_id
      ));
    }
  ], ["id" => Router::REGEX_NUMBER]);
  
  
  
  $card_router->get("/in-stack/:id", [
    Middleware::requireToBeLoggedIn(),
    function (Request $request, Response $response) {
      $stack_id = param($request->param->get("id"));
      $user_id = param($request->session->get("user")->id);
  
      $stack = Stack::by_id($stack_id, $user_id)
        ->forwardFailure($response)
        ->getSuccess();
      
      Privilege::check(
        $user_id,
        param($stack->decks_id),
        [Privilege::RANK_CREATOR, Privilege::RANK_EDITOR, Privilege::RANK_QUEST]
      )
        ->forwardFailure($response);
    
      $response->json(Card::in_stack(
        $stack_id,
        $user_id,
      ));
    }
  ], ["id" => Router::REGEX_NUMBER]);
  
  
  
  $card_router->put("/:id", [
    Middleware::requireToBeLoggedIn(),
    function (Request $request, Response $response) {
      $card_id = param(intval($request->param->get("id")));
      
      /**
       * @var Card $card
       */
      $card = Card::by_id($card_id)
        ->forwardFailure($response)
        ->getSuccess();
      
      Privilege::check(
        param($request->session->get("user")->id),
        param($card->decks_id),
        [Privilege::RANK_CREATOR, Privilege::RANK_EDITOR]
      )
        ->forwardFailure($response);
      
      $response->json(Card::update(
        $card_id,
        param($request->body->get("question")),
        param($request->body->get("answer"))
      ));
    }
  ]);
  
  
  
  return $card_router;
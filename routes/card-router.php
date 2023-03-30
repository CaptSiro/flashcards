<?php
  
  use function OakBase\param;
  
  require_once __DIR__ . "/../lib/routepass/routers.php";
  
  require_once __DIR__ . "/../models/Card.php";
  require_once __DIR__ . "/../models/Stack.php";
  require_once __DIR__ . "/../models/Privilege.php";
  
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
    
      $response->json(
        Card::insert(
          param($request->body->get("question")),
          param($request->body->get("answer")),
          $stack_id,
          $user_id
        )
          ->forwardFailure($response)
          ->getSuccess()
      );
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
    
      $response->json(
        Card::in_stack(
          $stack_id,
          $user_id,
        )
          ->forwardFailure($response)
          ->getSuccess()
      );
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
      
      $response->json(
        Card::update(
          $card_id,
          param($request->body->get("question")),
          param($request->body->get("answer"))
        )
      );
    }
  ]);
  
  
  
  return $card_router;
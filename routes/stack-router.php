<?php
  
  use function OakBase\param;
  
  require_once __DIR__ . "/../lib/routepass/routers.php";
  
  require_once __DIR__ . "/../models/Stack.php";
  require_once __DIR__ . "/../models/Privilege.php";
  
  require_once __DIR__ . "/Middleware.php";
  
  
  
  $stack_router = new Router();
  
  
  
  $stack_router->post("/", [
    Middleware::requireToBeLoggedIn(),
    function (Request $request, Response $response) {
      $deck_id = param($request->body->get("deck_id"));
    
      Privilege::check(
        param($request->session->get("user")->id),
        $deck_id,
        [Privilege::RANK_CREATOR, Privilege::RANK_EDITOR]
      )
        ->forwardFailure($response)
        ->getSuccess();
      
      $response->json(
        Stack::insert(
          param($request->body->get("name")),
          $deck_id
        )
          ->forwardFailure($response)
          ->getSuccess()
      );
    }
  ]);
  
  
  
  $stack_router->delete("/:id", [
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
        [Privilege::RANK_CREATOR, Privilege::RANK_EDITOR]
      )
        ->forwardFailure($response)
        ->getSuccess();
    
      $response->json(Stack::delete(
        $stack_id
      ));
    }
  ], ["id" => Router::REGEX_NUMBER]);
  
  
  
  $stack_router->get("/in-deck/:id", [
    Middleware::requireToBeLoggedIn(),
    function (Request $request, Response $response) {
      $deck_id = param($request->param->get("id"));
  
      // todo possibility to create privilege provider endpoint:
      // /privilege/deck/:deck_id - when showing stacks in deck
      // /privilege/stack/:stack_id - when showing cards in stack

      Privilege::check(
        param($request->session->get("user")->id),
        $deck_id,
        [Privilege::RANK_CREATOR, Privilege::RANK_EDITOR, Privilege::RANK_QUEST]
      )
        ->forwardFailure($response)
        ->getSuccess();
    
      // todo change from: `[]` to: `{stacks: [], privilege: Rank}`
      $response->json(
        Stack::in_deck(
          $deck_id,
          param($request->session->get("user")->id)
        )
          ->forwardFailure($response)
          ->getSuccess()
      );
    }
  ], ["id" => Router::REGEX_NUMBER]);
  
  
  
  $stack_router->put("/:id", [
    Middleware::requireToBeLoggedIn(),
    function (Request $request, Response $response) {
      $stack_id = param(intval($request->param->get("id")));
  
      /**
       * @var Stack $stack
       */
      $stack = Stack::by_id(
        $stack_id,
        param($request->session->get("user")->id)
      )
        ->forwardFailure($response)
        ->getSuccess();
      
      Privilege::check(
        param($request->session->get("user")->id),
        param($stack->decks_id),
        [Privilege::RANK_CREATOR, Privilege::RANK_EDITOR]
      )
        ->forwardFailure($response);
      
      $response->json(
        Stack::update($stack_id, param($request->body->get("name")))
      );
    }
  ]);
  
  
  
  return $stack_router;
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
        $request->session->get("user")->id,
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
      $deck_id = param($request->param->get("id"));
    
      Privilege::check(
        $request->session->get("user")->id,
        $deck_id,
        [Privilege::RANK_CREATOR, Privilege::RANK_EDITOR]
      )
        ->forwardFailure($response)
        ->getSuccess();
    
      $response->json(Stack::delete(
        $deck_id
      ));
    }
  ], ["id" => Router::REGEX_NUMBER]);
  
  
  
  $stack_router->get("/in-deck/:id", [
    Middleware::requireToBeLoggedIn(),
    function (Request $request, Response $response) {
      $deck_id = param($request->param->get("id"));
      $user_id = param($request->session->get("user")->id);
  
      $privilege = Privilege::check(
        $user_id,
        $deck_id,
        [Privilege::RANK_CREATOR, Privilege::RANK_EDITOR, Privilege::RANK_QUEST]
      )
        ->forwardFailure($response)
        ->getSuccess();
    
      // todo change from: `[]` to: `{stacks: [], privilege: Rank}`
      $response->json(
        Stack::in_deck(
          $deck_id,
          $user_id
        )
          ->forwardFailure($response)
          ->getSuccess()
      );
    }
  ], ["id" => Router::REGEX_NUMBER]);
  
  
  
  return $stack_router;
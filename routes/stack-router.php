<?php
  
  use function OakBase\param;
  
  require_once __DIR__ . "/../lib/routepass/routers.php";
  
  require_once __DIR__ . "/../models/Stack.php";
  
  require_once __DIR__ . "/Middleware.php";
  
  
  
  $stack_router = new Router();
  
  
  
  $stack_router->post("/", [
    Middleware::requireToBeLoggedIn(),
    function (Request $request, Response $response) {
      $response->json(
        Stack::insert(
          param($request->body->get("name")),
          param($request->body->get("deck_id"))
        )
          ->forwardFailure($response)
          ->getSuccess()
      );
    }
  ]);
  
  
  
  $stack_router->delete("/:id", [
    Middleware::requireToBeLoggedIn(),
    function (Request $request, Response $response) {
      $response->json(Stack::delete(
        param($request->param->get("id"))
      ));
    }
  ], ["id" => Router::REGEX_NUMBER]);
  
  
  
  $stack_router->get("/in-deck/:id", [
    Middleware::requireToBeLoggedIn(),
    function (Request $request, Response $response) {
      $response->json(
        Stack::in_deck(
          param($request->param->get("id")),
          param($request->session->get("user")->id)
        )
          ->forwardFailure($response)
          ->getSuccess()
      );
    }
  ], ["id" => Router::REGEX_NUMBER]);
  
  
  
  return $stack_router;
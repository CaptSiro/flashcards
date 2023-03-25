<?php
  
  use function OakBase\param;
  
  require_once __DIR__ . "/../lib/routepass/routers.php";
  
  require_once __DIR__ . "/../models/Card.php";
  
  require_once __DIR__ . "/Middleware.php";
  
  
  
  $card_router = new Router();
  
  
  
  $card_router->post("/", [
    Middleware::requireToBeLoggedIn(),
    function (Request $request, Response $response) {
      $response->json(
        Card::insert(
          param($request->body->get("question")),
          param($request->body->get("answer")),
          param($request->body->get("stack_id")),
        )
          ->forwardFailure($response)
          ->getSuccess()
      );
    }
  ]);
  
  
  
  $card_router->get("/in-stack/:id", [
    Middleware::requireToBeLoggedIn(),
    function (Request $request, Response $response) {
      $response->json(
        Card::in_stack(
          param($request->param->get("id"))
        )
          ->forwardFailure($response)
          ->getSuccess()
      );
    }
  ], ["id" => Router::REGEX_NUMBER]);
  
  
  
  return $card_router;
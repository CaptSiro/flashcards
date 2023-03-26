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
          param($request->session->get("user")->id)
        )
          ->forwardFailure($response)
          ->getSuccess()
      );
    }
  ]);
  
  
  
  $card_router->delete("/:id", [
    Middleware::requireToBeLoggedIn(),
    function (Request $request, Response $response) {
      $response->json(Card::delete(
        param($request->param->get("id"))
      ));
    }
  ], ["id" => Router::REGEX_NUMBER]);
  
  
  
  $card_router->get("/in-stack/:id", [
    Middleware::requireToBeLoggedIn(),
    function (Request $request, Response $response) {
      $response->json(
        Card::in_stack(
          param($request->param->get("id")),
          param($request->session->get("user")->id),
        )
          ->forwardFailure($response)
          ->getSuccess()
      );
    }
  ], ["id" => Router::REGEX_NUMBER]);
  
  
  
  return $card_router;
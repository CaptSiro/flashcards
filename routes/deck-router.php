<?php
  
  use function OakBase\param;
  
  require_once __DIR__ . "/../lib/routepass/routers.php";
  
  require_once __DIR__ . "/../models/Deck.php";
  
  require_once __DIR__ . "/Middleware.php";
  
  
  
  $deck_router = new Router();
  
  
  
  $deck_router->post("/", [
    Middleware::requireToBeLoggedIn(),
    function (Request $request, Response $response) {
      $response->json(
        Deck::insert(
          param($request->body->get("name")),
          param($request->session->get("user")->id)
        )
          ->forwardFailure($response)
          ->getSuccess()
      );
    }
  ]);
  
  
  
  $deck_router->get("/users/", [
    Middleware::requireToBeLoggedIn(),
    function (Request $request, Response $response) {
      $response->json(
        Deck::users(
          param($request->session->get("user")->id)
        )
          ->forwardFailure($response)
          ->getSuccess()
      );
    }
  ]);
  
  
  
  return $deck_router;
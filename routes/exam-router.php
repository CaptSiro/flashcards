<?php
  
  use function OakBase\param;
  
  require_once __DIR__ . "/../lib/routepass/routers.php";
  
  require_once __DIR__ . "/../models/Card.php";
  require_once __DIR__ . "/../models/Stack.php";
  
  require_once __DIR__ . "/Middleware.php";
  
  
  
  $exam_router = new Router();
  
  
  
  $exam_router->get("/", [
    Middleware::requireToBeLoggedIn(Middleware::RESPONSE_REDIRECT),
    function (Request $request, Response $response) {
      $stack_id = param($request->query->get("stack"));
    
      $cards = Card::in_stack($stack_id);
      
      if ($cards->isFailure()) {
        $response->render("error", ["message" => $cards->getFailure()->getMessage()]);
      }
      
      $stack = Stack::by_id($stack_id);
  
      if ($stack->isFailure()) {
        $response->render("error", ["message" => $stack->getFailure()->getMessage()]);
      }
      
      $response->render("exam", [
        "cards" => $cards->getSuccess(),
        "stack_name" => $stack->getSuccess()->name
      ]);
    }
  ]);
  
  
  
  
  
  
  
  return $exam_router;
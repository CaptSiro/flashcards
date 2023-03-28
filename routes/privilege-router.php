<?php
  
  use function OakBase\param;
  
  require_once __DIR__ . "/../lib/routepass/routers.php";
  
  require_once __DIR__ . "/../models/Privilege.php";
  require_once __DIR__ . "/../models/Deck.php";
  require_once __DIR__ . "/../models/Stack.php";
  
  require_once __DIR__ . "/Middleware.php";
  
  
  
  $privilege_router = new Router();
  
  
  
  $privilege_router->post("/", [
    Middleware::requireToBeLoggedIn(),
    function (Request $request, Response $response) {
      $email = $request->body->get("email");
      $user = $request->session->get("user");
      if ($email === $user->email) {
        $response->fail(new IllegalArgumentExc("Cannot share with your-self."));
      }
      
      $share_to = User::by_email(param($email))
        ->forwardFailure($response)
        ->getSuccess();
      
      $rank = $request->body->get("rank");
      if (!(in_array($rank, Privilege::PRIVILEGES) && $rank !== 0)) {
        $response->fail(new InvalidArgumentExc("Invalid privilege rank."));
      }
      
      $deck = Deck::by_id(param($request->body->get("deck_id")))
        ->forwardFailure($response)
        ->getSuccess();
      
      if ($deck->rank !== 0) {
        $response->fail(new IllegalArgumentExc("You do not have the privileges to share this deck."));
      }
      
      $response->json(Privilege::insert(
        param($rank),
        param($deck->id),
        param($share_to->id),
      ));
    }
  ]);
  
  
  
  $privilege_router->get("/:table/:id", [
    Middleware::requireToBeLoggedIn(),
    function (Request $request, Response $response) {
      $user_id = param($request->session->get("user")->id);
      $id = param($request->param->get("id"));
      
      if ($request->param->get("table") === "deck") {
        $response->json(
          Privilege::for_user($user_id, $id)
            ->forwardFailure($response)
            ->getSuccess()
        );
      }
      
      $stack = Stack::by_id(
        $id,
        $user_id
      )
        ->forwardFailure($response)
        ->getSuccess();
      
      $response->json(
        Privilege::for_user(
          $user_id,
          param($stack->decks_id)
        )
          ->forwardFailure($response)
          ->getSuccess()
      );
    }
  ], [
    "id" => Router::REGEX_NUMBER,
    "table" => Router::REGEX_ENUM(["stack", "deck"])
  ]);
  
  
  
  return $privilege_router;
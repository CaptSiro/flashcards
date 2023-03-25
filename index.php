<?php
  
  
  require_once __DIR__ . "/lib/dotenv/dotenv.php";
  $env = new Env(__DIR__ . "/.env");
  
  require_once __DIR__ . "/models/User.php";
  
  require_once __DIR__ . "/lib/oakbase/oakbase.php";
  use OakBase\Database;
  use OakBase\BasicConfig;
  
  Database::configure(new BasicConfig(
    $env->get_or_crash("DB_HOST"),
    $env->get_or_crash("DB_NAME"),
    $env->get_or_crash("DB_USER"),
    $env->get_or_crash("DB_PASSWORD"),
    $env->get_or_crash("DB_PORT")
  ));
  
  
  
  /** @var HomeRouter $router */
  $router = require_once __DIR__ . "/lib/routepass/routepass.php";
  
  
  
  $router->setBodyParser(HomeRouter::BODY_PARSER_JSON());
  $router->setViewDirectory(__DIR__ . "/views");
  $router->setFlag(HomeRouter::FLAG_MAIN_SERVER_HOST_NAME, $env->get_or_crash("DOMAIN"));
  
  $router->static("/public", __DIR__ . "/public");
  
  $router->onAnyErrorEvent(function (RequestError $requestError) {
    $requestError->response->render("error", ["message" => htmlspecialchars($requestError->message)]);
  });
  
  
  
  $router->get("/", [
    function (Request $request, Response $response) {
      if (!$request->session->isset("user")) {
        $response->setHeader("Location", Response::createRedirectURL("/auth"));
        $response->flush();
      }
    
      $response->render("flashcards");
    }
  ]);
  
  
  
  
  
  $router->use("/auth", new RouterPromise(__DIR__ . "/routes/auth-router.php"));
  $router->use("/deck", new RouterPromise(__DIR__ . "/routes/deck-router.php"));
  $router->use("/stack", new RouterPromise(__DIR__ . "/routes/stack-router.php"));
  $router->use("/card", new RouterPromise(__DIR__ . "/routes/card-router.php"));
  
  
  
  
  
  $router->get("/robots.txt", [function (Request $request, Response $response) {
    $response->setHeader("Content-Type", "text/plain");
    $response->send("User-agent: *\nAllow: /auth/");
  }]);
  
  $router->get("/routes", [function (Request $request, Response $response) use ($router) {
    $router->showTrace();
    $response->flush();
  }]);
  
  $router->serve();
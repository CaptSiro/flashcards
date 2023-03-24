<?php

  require_once __DIR__ . "/lib/dotenv/dotenv.php";
  $env = new Env(__DIR__ . "/.env");
  
  
  /** @var HomeRouter $router */
  $router = require_once __DIR__ . "/lib/routepass/routepass.php";
  
  
  
  $router->setBodyParser(HomeRouter::BODY_PARSER_JSON());
  $router->setViewDirectory(__DIR__ . "/views");
  
  $router->static("/public", __DIR__ . "/public");
  
  $router->onAnyErrorEvent(function (RequestError $requestError) {
    $requestError->response->render("error", ["message" => htmlspecialchars($requestError->message)]);
  });
  
  
  
  $router->get("/", [
    function (Request $request, Response $response) use ($env) {
      if (!$request->session->isset("user")) {
        $response->setHeader("Location", $env->get("HOME")->forwardFailure($response)->getSuccess() ."/auth");
        $response->flush();
      }
    
      $response->render("index");
    }
  ]);
  
  
  
  
  
  $router->use("/auth", new RouterPromise(__DIR__ . "/routes/auth-router.php"));
  
  
  
  
  
  $router->get("/robots.txt", [function (Request $request, Response $response) {
    $response->setHeader("Content-Type", "text/plain");
    $response->send("User-agent: *\nAllow: /auth/");
  }]);
  
  $router->get("/routes", [function (Request $request, Response $response) use ($router) {
    $router->showTrace();
    $response->flush();
  }]);
  
  $router->serve();
<?php

use OakBase\SideEffect;
use function OakBase\param;

require_once __DIR__ . "/../lib/routepass/routers.php";
require_once __DIR__ . "/../lib/susmail/susmail.php";
require_once __DIR__ . "/../lib/phpmailer/phpmailer.php";
require_once __DIR__ . "/../lib/regexes.php";

require_once __DIR__ . "/../models/User.php";
require_once __DIR__ . "/../models/InvitationLink.php";

require_once __DIR__ . "/Middleware.php";



$auth_router = new Router();



$auth_router->get("/", [
    Middleware::requireToBeLoggedOut(Middleware::RESPONSE_REDIRECT, Response::createRedirectURL("/")),
    function (Request $request, Response $response) {
        $response->render("auth");
    },
]);



$auth_router->post("/login", [
    Middleware::requireToBeLoggedOut(Middleware::RESPONSE_JSON),
    function (Request $request, Response $response) {
        $username = param($request->body->get("username"));
        $password = $request->body->get("password");

        /**
         * @var User $user
         */
        $user = User::by_username($username)
            ->forwardFailure($response)
            ->getSuccess();

        $user->verify($password)
            ->forwardFailure($response);

        $request->session->set(
            "user", User::by_id(param($user->id))
            ->forwardFailure($response)
            ->getSuccess()
        );

        $response->json([
            "next" => Response::createRedirectURL("/"),
        ]);
    },
]);



$auth_router->post("/register", [
    Middleware::requireToBeLoggedOut(Middleware::RESPONSE_JSON),
    function (Request $request, Response $response) {
        $username = param($request->body->get("username"));
        $password = param(password_hash($request->body->get("password"), PASSWORD_DEFAULT));

        User::by_username($username)
            ->succeeded(function () use ($response) {
                $response->fail(new InvalidArgumentExc("User exists with such username."));
            });

        /**
         * @var SideEffect $side_effect
         */
        $side_effect = User::insert($username, $password)
            ->forwardFailure($response)
            ->getSuccess();

        if ($side_effect->row_count() === 1) {
            $request->session->set(
                "user", User::by_id(param($side_effect->last_inserted_ID()))
                ->forwardFailure($response)
                ->getSuccess()
            );
        }

        $response->json([
            "next" => Response::createRedirectURL("/"),
        ]);
    },
]);



$auth_router->delete("/", [
    Middleware::requireToBeLoggedIn(),
    function (Request $request, Response $response) {
        $request->session->unset("user");
        $response->json(["next" => Response::createRedirectURL("/auth")]);
    },
]);



//  $auth_router->get("/login", [function (Request $request, Response $response) {
//    $link = InvitationLink::by_arg(
//      param($request->query->get("arg"))
//    )
//      ->forwardFailure($response)
//      ->getSuccess();
//
//    if ($link === false) {
//      $response->render("error", ["message" => "Link has been used or it has expired. (Expiration time: 5 minutes)"]);
//    }
//
//    $user = User::by_id(param($link->users_id))
//      ->forwardFailure($response)
//      ->getSuccess();
//
//    if ($user === false) {
//      $response->render("error", ["message" => "Lost link... No account found for this link."]);
//    }
//
//
//
//    InvitationLink::delete_for_user(param($user->id));
//
//    $response->redirect("/");
//  }]);



$auth_router->get("/stay-logged-in", [function (Request $request, Response $response) {
    setcookie("PHPSESSID", $request->cookies->get("PHPSESSID"), time() + 86_400 * 30, "/");
    $response->json(["message" => "Done"]);
}]);



return $auth_router;
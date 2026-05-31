<?php

use function OakBase\param;

require_once __DIR__ . "/../lib/routepass/routers.php";

require_once __DIR__ . "/../models/Deck.php";

require_once __DIR__ . "/Middleware.php";



$deck_router = new Router();



$deck_router->post("/", [
    Middleware::requireToBeLoggedIn(),
    Middleware::stringLengthGuard("name", 128, "Deck's name"),
    function (Request $request, Response $response) {
        $response->json(
            Deck::insert(
                param($request->body->get("name")),
                param($request->session->get("user")->id)
            )
        );
    },
]);



$deck_router->get("/:id", [
    Middleware::requireToBeLoggedIn(),
    function (Request $request, Response $response) {
        $user_id = param($request->session->get("user")->id);
        $deck_id = param($request->param->get("id"));

        Privilege::check($user_id, $deck_id, [Privilege::RANK_CREATOR, Privilege::RANK_EDITOR, Privilege::RANK_GUEST])
            ->forwardFailure($response);

        $response->json(Deck::by_id($deck_id));
    },
]);



$deck_router->delete("/:id", [
    Middleware::requireToBeLoggedIn(),
    function (Request $request, Response $response) {
        $user_id = param($request->session->get("user")->id);
        $deck_id = param($request->param->get("id"));

        Privilege::check($user_id, $deck_id, [Privilege::RANK_CREATOR])
            ->forwardFailure($response);

        $response->json(Deck::delete($deck_id));
    },
]);



$deck_router->put("/:id", [
    Middleware::requireToBeLoggedIn(),
    Middleware::stringLengthGuard("name", 128, "Deck's name"),
    function (Request $request, Response $response) {
        $deck_id = param(intval($request->param->get("id")));

        Privilege::check(
            param($request->session->get("user")->id),
            $deck_id,
            [Privilege::RANK_CREATOR, Privilege::RANK_EDITOR]
        )
            ->forwardFailure($response);

        $response->json(
            Deck::update($deck_id, param($request->body->get("name")))
        );
    },
]);



$deck_router->get("/users/", [
    Middleware::requireToBeLoggedIn(),
    function (Request $request, Response $response) {
        $response->json(
            Deck::users(
                param($request->session->get("user")->id)
            )
        );
    },
]);



$deck_router->get('/random', [
    function (Request $request, Response $response) {
        $ids = explode(',', $request->query->get('ids'));
        $cards = [];

        foreach ($ids as $id) {
            $result = Deck::by_id(param(intval($id)));
            if ($result->isFailure()) {
                continue;
            }

            $deck = $result->getSuccess();
            $c = Card::random(param($deck->id), param(1));
            if ($c->isFailure()) {
                continue;
            }

            $cards = array_merge($cards, $c->getSuccess());
        }

        $response->json(
            $cards[array_rand($cards)]
        );
    },
]);



return $deck_router;
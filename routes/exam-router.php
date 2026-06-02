<?php

use function OakBase\param;

require_once __DIR__ . "/../lib/routepass/routers.php";

require_once __DIR__ . "/../models/Deck.php";
require_once __DIR__ . "/../models/Card.php";
require_once __DIR__ . "/../models/Stack.php";
require_once __DIR__ . "/../models/ExamResult.php";

require_once __DIR__ . "/Middleware.php";



$exam_router = new Router();



$exam_router->get("/", [
    Middleware::requireToBeLoggedIn(Middleware::RESPONSE_REDIRECT),
    function (Request $request, Response $response) {
        $user_id = param($request->session->get("user")->id);

        if ($request->query->isset("deck")) {
            $deck_id = param($request->query->get("deck"));

            $deck = Deck::by_id($deck_id);
            if ($deck->isFailure()) {
                $response->render("error", ["message" => $deck->getFailure()->getMessage()]);
            }

            $stacks = Stack::in_deck($deck_id, $user_id);
            if ($stacks->isFailure()) {
                $response->render("error", ["message" => $stacks->getFailure()->getMessage()]);
            }

            $cards = [];
            foreach ($stacks->getSuccess() as $s) {
                $c = Card::in_stack(param($s->id));
                if ($c->isFailure()) {
                    $response->render("error", ["message" => $c->getFailure()->getMessage()]);
                }

                $cards = array_merge($cards, $c->getSuccess());
            }

            $response->render("exam", [
                "cards" => $cards,
                "stack_name" => null,
                "stack_id" => null,
                "deck_name" => $deck->getSuccess()->name,
                "deck_id" => $deck_id,
            ]);
        }

        $stack_id = param($request->query->looselyGet("stack"));

        $cards = Card::in_stack($stack_id);
        if ($cards->isFailure()) {
            $response->render("error", ["message" => $cards->getFailure()->getMessage()]);
        }

        $stack = Stack::by_id($stack_id, $user_id);
        if ($stack->isFailure()) {
            $response->render("error", ["message" => $stack->getFailure()->getMessage()]);
        }

        $response->render("exam", [
            "cards" => $cards->getSuccess(),
            "stack_name" => $stack->getSuccess()->name,
            "stack_id" => $stack->getSuccess()->id,
            "deck_name" => null,
            "deck_id" => null,
        ]);
    },
]);



$exam_router->post("/result", [
    Middleware::requireToBeLoggedIn(),
    function (Request $request, Response $response) {
        $response->json(
            ExamResult::insert(
                param($request->body->get("fraction")),
                param($request->session->get("user")->id),
                param($request->body->get("stack_id")),
            )
        );
    },
]);



$exam_router->get("/results/:stack_id", [
    Middleware::requireToBeLoggedIn(),
    function (Request $request, Response $response) {
        $response->json(
            ExamResult::in_stack(
                param($request->param->get("stack_id")),
                param($request->session->get("user")->id)
            )
        );
    },
]);



return $exam_router;
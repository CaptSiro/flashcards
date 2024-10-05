<?php

use OakBase\Database;
use OakBase\Param;
use OakBase\SideEffect;
use function OakBase\param;

require_once __DIR__ . "/Count.php";
require_once __DIR__ . "/Privilege.php";
require_once __DIR__ . "/Stack.php";



class Deck {
    public int $id;
    public string $name;
    public int $rank;



    static function insert(Param $name, Param $user_id): Result {
        $is_unique = Database::get()->fetch(
                "SELECT COUNT(id) as amount
        FROM decks
        WHERE `name` = $name",
                Count::class
            )->amount === 0;

        if (!$is_unique) {
            return fail(new NotUniqueValueExc("Name must be unique."));
        }

        $side_effect = Database::get()->statement(
            "INSERT INTO decks (name) VALUE ($name)"
        );

        if ($side_effect->last_inserted_ID() === 0) {
            return fail(new NotUniqueValueExc("Could not create deck."));
        }

        Privilege::insert(
            param(0),
            param($side_effect->last_inserted_ID()),
            $user_id
        );

        return success($side_effect);
    }



    static function by_id(Param $id): Result {
        $deck = Database::get()->fetch(
            "SELECT decks.id, `name`, p.rank
        FROM decks
            JOIN privileges p ON decks.id = p.decks_id
                AND p.decks_id = $id",
            self::class
        );

        if ($deck === false || $deck === null) {
            return fail(new NotFoundExc("There are no decks for id: " . $id->value()));
        }

        return success($deck);
    }



    static function users(Param $user_id): Result {
        $deck = Database::get()->fetch_all(
            "SELECT decks.id, `name`, p.rank
        FROM decks
            JOIN privileges p ON decks.id = p.decks_id
                AND p.users_id = $user_id
        ORDER BY `name`",
            self::class
        );

        if ($deck === false) {
            return fail(new NotFoundExc("There are no decks for user with id: " . $user_id->value()));
        }

        return success($deck);
    }



    static function update(Param $id, Param $name): SideEffect {
        return Database::get()->statement(
            "UPDATE decks SET `name` = $name WHERE id = $id"
        );
    }



    static function delete(Param $deck_id): Result {
        $stacks = Stack::by_deck_id($deck_id);

        if ($stacks->isFailure()) {
            return $stacks;
        }

        /**
         * @var Stack $stack
         */
        foreach ($stacks->getSuccess() as $stack) {
            Stack::delete(param($stack->id));
        }

        Privilege::delete_for_deck($deck_id);

        return success(
            Database::get()->statement(
                "DELETE FROM decks
        WHERE id = $deck_id
        LIMIT 1"
            )
        );
    }
}
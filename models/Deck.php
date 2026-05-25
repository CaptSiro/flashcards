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
    public string $creator;
    public int $rank;



    static function insert(Param $name, Param $user_id): Result {
        $creator = param(Privilege::RANK_CREATOR);
        $is_unique = Database::get()->fetch(
                "SELECT COUNT(decks.id) as amount
        FROM decks
        JOIN flashcards.privileges p on decks.id = p.decks_id
            AND p.`rank` = $creator
            AND p.users_id = $user_id
        WHERE decks.`name` = $name",
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
            param(Privilege::RANK_CREATOR),
            param($side_effect->last_inserted_ID()),
            $user_id
        );

        return success($side_effect);
    }



    static function by_id(Param $id): Result {
        $deck = Database::get()->fetch(
            "SELECT decks.id, name, u.username as creator, p.rank
        FROM decks
            JOIN privileges p ON decks.id = p.decks_id
                AND p.decks_id = $id
            JOIN flashcards.users u on p.users_id = u.id",
            self::class
        );

        if ($deck === false || $deck === null) {
            return fail(new NotFoundExc("There are no decks for id: " . $id->value()));
        }

        return success($deck);
    }



    /**
     * Get all decks that user may access
     * @param Param $user_id
     * @return Result
     * @throws \OakBase\MixedIndexingException
     */
    static function users(Param $user_id): Result {
        $creator = param(Privilege::RANK_CREATOR);
        $deck = Database::get()->fetch_all(
            "SELECT decks.id, decks.name as name, u.username as creator, p.rank
        FROM decks
            JOIN privileges p ON decks.id = p.decks_id
                AND p.users_id = $user_id
            JOIN privileges creator ON decks.id = creator.decks_id
                AND creator.users_id = $user_id
                AND p.`rank` = $creator
            JOIN flashcards.users u on u.id = creator.users_id
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
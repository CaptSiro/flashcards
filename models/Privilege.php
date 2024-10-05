<?php

use OakBase\Database;
use OakBase\Param;
use OakBase\SideEffect;
use function OakBase\param;

require_once __DIR__ . "/Count.php";



class Privilege {
    const RANK_CREATOR = 0;

    const RANK_EDITOR = 1;

    const RANK_QUEST = 2;
    const PRIVILEGES = [0, 1, 2];
    public int $id;
    public int $rank;
    public int $decks_id;
    public int $users_id;

    static function insert(Param $rank, Param $deck_id, Param $user_id): Result {
        $result = Database::get()->statement(
            "INSERT INTO privileges (`rank`, decks_id, users_id)
            VALUE ($rank, $deck_id, $user_id)"
        );

        if ($result->last_inserted_ID() === 0) {
            return fail(new Exc("Could not insert privilege."));
        }

        return success($result);
    }



    static function update(Param $id, Param $rank): SideEffect {
        return Database::get()->statement(
            "UPDATE privileges SET `rank` = $rank WHERE id = $id"
        );
    }



    static function delete(Param $id): SideEffect {
        return Database::get()->statement(
            "DELETE FROM privileges WHERE id = $id"
        );
    }



    static function delete_for_deck(Param $deck_id): SideEffect {
        return Database::get()->statement(
            "DELETE FROM privileges WHERE decks_id = $deck_id"
        );
    }

    static function team(Param $deck_id): Result {
        $creator = param(self::RANK_CREATOR);

        $team_members = Database::get()->fetch_all(
            "SELECT `privileges`.id, `rank`, decks_id, users_id, username
        FROM privileges
            JOIN users u ON u.id = `privileges`.users_id
                AND `rank` != $creator
                AND decks_id = $deck_id",
            self::class
        );

        if ($team_members === false) {
            return fail(new Exc("Could not retrieve data about team."));
        }

        return success($team_members);
    }

    static function check(Param $user_id, Param $deck_id, array $ranks): Result {
        $result = self::for_user($user_id, $deck_id);

        if ($result->isFailure()) {
            return $result;
        }

        if (!in_array($result->getSuccess()->rank, $ranks)) {
            return fail(new IllegalArgumentExc("You do not have the required privileges to access this resource."));
        }

        return $result;
    }

    static function for_user(Param $user_id, Param $deck_id): Result {
        $result = Database::get()->fetch(
            "SELECT id, `rank`, decks_id, users_id
        FROM privileges
        WHERE users_id = $user_id
            AND decks_id = $deck_id",
            self::class
        );

        if ($result === null || $result === false) {
            return fail(new NotFoundExc("Could not find privilege rule."));
        }

        return success($result);
    }
}
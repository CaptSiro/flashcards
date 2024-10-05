<?php

use OakBase\Database;
use OakBase\Param;

require_once __DIR__ . "/Count.php";



class ExamResult {
    public int $id;
    public float $fraction;
    public int $users_id;
    public int $stacks_id;

    static function insert(Param $fraction, Param $user_id, Param $stack_id): Result {
        $side_effect = Database::get()->statement(
            "INSERT INTO results (fraction, users_id, stacks_id)
            VALUE ($fraction, $user_id, $stack_id)"
        );

        if ($side_effect->row_count() === 0) {
            return fail(new Exc("Could not insert into results table."));
        }

        return success($side_effect);
    }



    static function in_stack(Param $stack_id, Param $user_id): Result {
        $results = Database::get()->fetch_all(
            "SELECT id, fraction, users_id, stacks_id FROM results
        WHERE users_id = $user_id
            AND stacks_id = $stack_id",
            self::class
        );

        if ($results === false) {
            return fail(new Exc("Could not find results in stack with id: " . $stack_id->value()));
        }

        return success($results);
    }
}
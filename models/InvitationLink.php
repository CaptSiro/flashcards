<?php

use OakBase\Database;
use OakBase\Param;
use OakBase\PrimitiveParam;

class InvitationLink {
    const ARG_GEN_CHARSET = "qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM0123456789_-";
    const ARG_GEN_MAX_TRIES = 4_096;
    public int $id;
    public string $arg;
    public int $users_id;
    public int $expires;

    static function insert(Param $arg, Param $user_id): Result {
        $expires = new PrimitiveParam(time() + 60 * 5);

        return success(
            Database::get()->statement(
                "INSERT INTO invitation_links (arg, users_id, expires)
            VALUE ($arg, $user_id, $expires)"
            )
        );
    }

    static function delete_for_user(Param $user_id): Result {
        self::purge_old();

        return success(
            Database::get()->statement(
                "DELETE FROM invitation_links
            WHERE users_id = $user_id"
            )
        );
    }

    static function purge_old() {
        Database::get()->statement("DELETE FROM invitation_links WHERE expires < UNIX_TIMESTAMP()");
    }

    static function arg_gen(): Result {
        $arg = "";
        $try = 0;

        do {
            $try++;

            for ($i = 0; $i < 64; $i++) {
                $arg .= self::ARG_GEN_CHARSET[random_int(0, 63)];
            }

            $existing_arg = self::by_arg(new PrimitiveParam($arg));
            if ($existing_arg->isSuccess() && $existing_arg->getSuccess() === false) {
                return success($arg);
            }
        } while ($try < self::ARG_GEN_MAX_TRIES);

        return fail(new Exc("Argument generation timeout."));
    }

    static function by_arg(Param $arg): Result {
        self::purge_old();

        return success(
            Database::get()->fetch(
                "SELECT id, arg, expires, users_id
        FROM invitation_links
        WHERE arg = $arg
            AND expires > UNIX_TIMESTAMP()"
            )
        );
    }
}
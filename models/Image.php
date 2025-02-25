<?php

use OakBase\Database;
use OakBase\Param;
use OakBase\PrimitiveParam;

require_once __DIR__ . "/../lib/path.php";



class Image {
    const SRC_GEN_CHARSET = "qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM0123456789_-";
    const SRC_GEN_MAX_TRIES = 4_096;
    const SRC_LENGTH = 10;
    public string $src;
    public string $ext;
    public string $images_src;
    public int $cards_id;

    static function insert(Param $src, Param $ext) {
        return Database::get()->statement(
            "INSERT INTO images (src, ext)
            VALUE ($src, $ext)"
        );
    }

    static function insert_question(Param $image_src, Param $card_id) {
        return Database::get()->statement(
            "INSERT INTO question_images (cards_id, images_src)
            VALUE ($card_id, $image_src)"
        );
    }

    static function insert_answer(Param $image_src, Param $card_id) {
        return Database::get()->statement(
            "INSERT INTO answer_images (cards_id, images_src)
            VALUE ($card_id, $image_src)"
        );
    }

    /**
     * Generate unique source for image
     * @return Result
     * @throws \Random\RandomException
     */
    static function src_gen(): Result {
        $src = "";
        $try = 0;

        do {
            $try++;

            for ($i = 0; $i < self::SRC_LENGTH; $i++) {
                $src .= self::SRC_GEN_CHARSET[random_int(0, 63)];
            }

            $existing_image = self::by_src(new PrimitiveParam($src));
            if ($existing_image->isFailure()) {
                return success($src);
            }
        } while ($try < self::SRC_GEN_MAX_TRIES);

        return fail(new Exc("Source generation timeout."));
    }

    static function by_src(Param $src) {
        $image = Database::get()->fetch(
            "SELECT src, ext
        FROM images
        WHERE src = $src",
            self::class
        );

        if ($image === null || $image === false) {
            return fail(new NotFoundExc("Could not find image with src: " . $src->value()));
        }

        return success($image);
    }

    static function delete_for_card(Param $card_id) {
        Database::get()->statement(
            "DELETE FROM question_images WHERE cards_id = $card_id"
        );

        Database::get()->statement(
            "DELETE FROM answer_images WHERE cards_id = $card_id"
        );

        $unused_images = Database::get()->fetch_all(
            "SELECT i.src, i.ext
        FROM images as i
            LEFT JOIN answer_images ai ON i.src = ai.images_src
            LEFT JOIN question_images qi ON i.src = qi.images_src
        WHERE ai.id IS NULL AND qi.id IS NULL",
            self::class
        );

        foreach ($unused_images as $image) {
            unlink(FILES_DIR . "/$image->src$image->ext");
        }

        success(
            Database::get()->statement(
                "DELETE i
        FROM images as i
            LEFT JOIN answer_images ai ON i.src = ai.images_src
            LEFT JOIN question_images qi ON i.src = qi.images_src
        WHERE ai.id IS NULL AND qi.id IS NULL"
            )
        );
    }
}
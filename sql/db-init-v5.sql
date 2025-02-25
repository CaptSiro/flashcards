-- MySQL Script generated by MySQL Workbench
-- Wed Apr  5 17:01:39 2023
-- Model: New Model    Version: 1.0
-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS = @@UNIQUE_CHECKS, UNIQUE_CHECKS = 0;
SET @OLD_FOREIGN_KEY_CHECKS = @@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS = 0;
SET @OLD_SQL_MODE = @@SQL_MODE, SQL_MODE =
        'ONLY_FULL_GROUP_BY,STRICT_TRANS_TABLES,NO_ZERO_IN_DATE,NO_ZERO_DATE,ERROR_FOR_DIVISION_BY_ZERO,NO_ENGINE_SUBSTITUTION';

-- -----------------------------------------------------
-- Schema flashcards
-- -----------------------------------------------------
DROP SCHEMA IF EXISTS `flashcards`;

-- -----------------------------------------------------
-- Schema flashcards
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `flashcards` DEFAULT CHARACTER SET utf8;
USE `flashcards`;

-- -----------------------------------------------------
-- Table `flashcards`.`users`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `flashcards`.`users`;

CREATE TABLE IF NOT EXISTS `flashcards`.`users`
(
    `id`       INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `username` VARCHAR(64)  NOT NULL,
    `password` VARCHAR(255) NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `username_UNIQUE` (`username` ASC) VISIBLE
)
    ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `flashcards`.`decks`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `flashcards`.`decks`;

CREATE TABLE IF NOT EXISTS `flashcards`.`decks`
(
    `id`   INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name` VARCHAR(256) NOT NULL,
    PRIMARY KEY (`id`)
)
    ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `flashcards`.`stacks`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `flashcards`.`stacks`;

CREATE TABLE IF NOT EXISTS `flashcards`.`stacks`
(
    `id`       INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`     VARCHAR(256) NOT NULL,
    `decks_id` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`id`),
    INDEX `fk_stacks_decks1_idx` (`decks_id` ASC) VISIBLE,
    CONSTRAINT `fk_stacks_decks1`
        FOREIGN KEY (`decks_id`)
            REFERENCES `flashcards`.`decks` (`id`)
            ON DELETE NO ACTION
            ON UPDATE NO ACTION
)
    ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `flashcards`.`cards`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `flashcards`.`cards`;

CREATE TABLE IF NOT EXISTS `flashcards`.`cards`
(
    `id`       BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
    `question` VARCHAR(128)    NULL,
    `answer`   VARCHAR(1024)   NULL,
    `decks_id` INT UNSIGNED    NOT NULL,
    PRIMARY KEY (`id`),
    INDEX `fk_cards_decks1_idx` (`decks_id` ASC) VISIBLE,
    CONSTRAINT `fk_cards_decks1`
        FOREIGN KEY (`decks_id`)
            REFERENCES `flashcards`.`decks` (`id`)
            ON DELETE NO ACTION
            ON UPDATE NO ACTION
)
    ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `flashcards`.`cards_in_stacks`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `flashcards`.`cards_in_stacks`;

CREATE TABLE IF NOT EXISTS `flashcards`.`cards_in_stacks`
(
    `cards_id`  BIGINT UNSIGNED NOT NULL,
    `stacks_id` INT UNSIGNED    NOT NULL,
    PRIMARY KEY (`cards_id`, `stacks_id`),
    INDEX `fk_cards_in_stacks_stacks1_idx` (`stacks_id` ASC) VISIBLE,
    CONSTRAINT `fk_cards_in_stacks_cards1`
        FOREIGN KEY (`cards_id`)
            REFERENCES `flashcards`.`cards` (`id`)
            ON DELETE NO ACTION
            ON UPDATE NO ACTION,
    CONSTRAINT `fk_cards_in_stacks_stacks1`
        FOREIGN KEY (`stacks_id`)
            REFERENCES `flashcards`.`stacks` (`id`)
            ON DELETE NO ACTION
            ON UPDATE NO ACTION
)
    ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `flashcards`.`invitation_links`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `flashcards`.`invitation_links`;

CREATE TABLE IF NOT EXISTS `flashcards`.`invitation_links`
(
    `id`       INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `arg`      CHAR(64)     NOT NULL,
    `expires`  INT UNSIGNED NOT NULL,
    `users_id` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`id`),
    INDEX `fk_invitation_links_users1_idx` (`users_id` ASC) VISIBLE,
    CONSTRAINT `fk_invitation_links_users1`
        FOREIGN KEY (`users_id`)
            REFERENCES `flashcards`.`users` (`id`)
            ON DELETE NO ACTION
            ON UPDATE NO ACTION
)
    ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `flashcards`.`results`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `flashcards`.`results`;

CREATE TABLE IF NOT EXISTS `flashcards`.`results`
(
    `id`        INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `fraction`  DOUBLE       NOT NULL,
    `date`      DATETIME     NULL DEFAULT NOW(),
    `users_id`  INT UNSIGNED NOT NULL,
    `stacks_id` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`id`),
    INDEX `fk_results_users1_idx` (`users_id` ASC) VISIBLE,
    INDEX `fk_results_stacks1_idx` (`stacks_id` ASC) VISIBLE,
    CONSTRAINT `fk_results_users1`
        FOREIGN KEY (`users_id`)
            REFERENCES `flashcards`.`users` (`id`)
            ON DELETE NO ACTION
            ON UPDATE NO ACTION,
    CONSTRAINT `fk_results_stacks1`
        FOREIGN KEY (`stacks_id`)
            REFERENCES `flashcards`.`stacks` (`id`)
            ON DELETE NO ACTION
            ON UPDATE NO ACTION
)
    ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `flashcards`.`privileges`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `flashcards`.`privileges`;

CREATE TABLE IF NOT EXISTS `flashcards`.`privileges`
(
    `id`       INT UNSIGNED        NOT NULL AUTO_INCREMENT,
    `rank`     TINYINT(1) UNSIGNED NOT NULL,
    `decks_id` INT UNSIGNED        NOT NULL,
    `users_id` INT UNSIGNED        NOT NULL,
    PRIMARY KEY (`id`),
    INDEX `fk_privileges_decks1_idx` (`decks_id` ASC) VISIBLE,
    INDEX `fk_privileges_users1_idx` (`users_id` ASC) VISIBLE,
    CONSTRAINT `fk_privileges_decks1`
        FOREIGN KEY (`decks_id`)
            REFERENCES `flashcards`.`decks` (`id`)
            ON DELETE NO ACTION
            ON UPDATE NO ACTION,
    CONSTRAINT `fk_privileges_users1`
        FOREIGN KEY (`users_id`)
            REFERENCES `flashcards`.`users` (`id`)
            ON DELETE NO ACTION
            ON UPDATE NO ACTION
)
    ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `flashcards`.`images`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `flashcards`.`images`;

CREATE TABLE IF NOT EXISTS `flashcards`.`images`
(
    `src` CHAR(10)   NOT NULL,
    `ext` VARCHAR(8) NOT NULL,
    PRIMARY KEY (`src`)
)
    ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `flashcards`.`answer_images`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `flashcards`.`answer_images`;

CREATE TABLE IF NOT EXISTS `flashcards`.`answer_images`
(
    `id`         INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `cards_id`   BIGINT UNSIGNED NOT NULL,
    `images_src` CHAR(10)        NOT NULL,
    PRIMARY KEY (`id`),
    INDEX `fk_answer_images_cards1_idx` (`cards_id` ASC) VISIBLE,
    INDEX `fk_answer_images_images1_idx` (`images_src` ASC) VISIBLE,
    CONSTRAINT `fk_answer_images_cards1`
        FOREIGN KEY (`cards_id`)
            REFERENCES `flashcards`.`cards` (`id`)
            ON DELETE NO ACTION
            ON UPDATE NO ACTION,
    CONSTRAINT `fk_answer_images_images1`
        FOREIGN KEY (`images_src`)
            REFERENCES `flashcards`.`images` (`src`)
            ON DELETE NO ACTION
            ON UPDATE NO ACTION
)
    ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `flashcards`.`question_images`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `flashcards`.`question_images`;

CREATE TABLE IF NOT EXISTS `flashcards`.`question_images`
(
    `id`         INT UNSIGNED    NOT NULL AUTO_INCREMENT,
    `cards_id`   BIGINT UNSIGNED NOT NULL,
    `images_src` CHAR(10)        NOT NULL,
    PRIMARY KEY (`id`),
    INDEX `fk_question_images_cards1_idx` (`cards_id` ASC) VISIBLE,
    INDEX `fk_question_images_images1_idx` (`images_src` ASC) VISIBLE,
    CONSTRAINT `fk_question_images_cards1`
        FOREIGN KEY (`cards_id`)
            REFERENCES `flashcards`.`cards` (`id`)
            ON DELETE NO ACTION
            ON UPDATE NO ACTION,
    CONSTRAINT `fk_question_images_images1`
        FOREIGN KEY (`images_src`)
            REFERENCES `flashcards`.`images` (`src`)
            ON DELETE NO ACTION
            ON UPDATE NO ACTION
)
    ENGINE = InnoDB;


SET SQL_MODE = @OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS = @OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS = @OLD_UNIQUE_CHECKS;

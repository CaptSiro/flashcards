-- MySQL Script generated by MySQL Workbench
-- Fri Mar 24 22:46:51 2023
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
    `id`    INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `email` VARCHAR(320) NOT NULL,
    PRIMARY KEY (`id`),
    UNIQUE INDEX `email_UNIQUE` (`email` ASC) VISIBLE
)
    ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `flashcards`.`decks`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `flashcards`.`decks`;

CREATE TABLE IF NOT EXISTS `flashcards`.`decks`
(
    `id`       INT UNSIGNED NOT NULL AUTO_INCREMENT,
    `name`     VARCHAR(256) NOT NULL,
    `users_id` INT UNSIGNED NOT NULL,
    PRIMARY KEY (`id`),
    INDEX `fk_decks_users_idx` (`users_id` ASC) VISIBLE,
    CONSTRAINT `fk_decks_users`
        FOREIGN KEY (`users_id`)
            REFERENCES `flashcards`.`users` (`id`)
            ON DELETE NO ACTION
            ON UPDATE NO ACTION
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
    `question` VARCHAR(128)    NOT NULL,
    `answer`   VARCHAR(1024)   NOT NULL,
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


SET SQL_MODE = @OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS = @OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS = @OLD_UNIQUE_CHECKS;

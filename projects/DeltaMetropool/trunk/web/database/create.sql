SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL';

CREATE SCHEMA IF NOT EXISTS `SprintStad` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci ;
USE `SprintStad`;

-- -----------------------------------------------------
-- Table `SprintStad`.`Station`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `SprintStad`.`Station` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `code` VARCHAR(4) NULL ,
  `name` TINYTEXT NULL ,
  `description` TEXT NULL ,
  `image` TINYTEXT NULL ,
  `town` TINYTEXT NULL ,
  `region` TINYTEXT NULL ,
  `POVN` FLOAT NULL COMMENT 'Positie in het OV-Netwerk(score)' ,
  `PWN` FLOAT NULL COMMENT 'Positie in het wegennetwerk(score)' ,
  `IWD` FLOAT NULL COMMENT 'Inwoners- en werknemersdichtheid (aantal/ha.)' ,
  `MNG` FLOAT NULL COMMENT 'Mengingsintensiteit(%)' ,
  `area_cultivated_home` INT NULL ,
  `area_cultivated_work` INT NULL ,
  `area_cultivated_mixed` INT NULL ,
  `area_undeveloped_urban` INT NULL ,
  `area_undeveloped_rural` INT NULL ,
  `transform_area_cultivated_home` INT NULL ,
  `transform_area_cultivated_work` INT NULL ,
  `transform_area_cultivated_mixed` INT NULL ,
  `transform_area_undeveloped_urban` INT NULL ,
  `transform_area_undeveloped_mixed` INT NULL ,
  `count_home_total` INT NULL COMMENT 'Aantal woningen totaal gebied' ,
  `count_home_transform` INT NULL COMMENT 'Aantal woningen transformatiegebied' ,
  `count_work_total` INT NULL COMMENT 'Aantal werk bvo totaal gebied' ,
  `count_work_transform` INT NULL COMMENT 'Aantal werk bvo transformatiegebied' ,
  `network_value` INT NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `SprintStad`.`RoundInfo`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `SprintStad`.`RoundInfo` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `number` INT NOT NULL ,
  `name` TINYTEXT NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `SprintStad`.`Game`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `SprintStad`.`Game` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `name` TINYTEXT NULL ,
  `notes` TEXT NULL ,
  `starttime` DATETIME NULL ,
  `current_round_id` INT NULL ,
  `active` TINYINT(1) NULL DEFAULT true ,
  PRIMARY KEY (`id`) ,
  INDEX `current_round_fk` (`current_round_id` ASC) ,
  CONSTRAINT `current_round_fk`
    FOREIGN KEY (`current_round_id` )
    REFERENCES `SprintStad`.`RoundInfo` (`id` )
    ON DELETE RESTRICT
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `SprintStad`.`Team`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `SprintStad`.`Team` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `name` TINYTEXT NULL ,
  `description` TEXT NULL ,
  `cpu` TINYINT(1) NOT NULL DEFAULT false ,
  `created` DATETIME NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `SprintStad`.`StationInstance`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `SprintStad`.`StationInstance` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `station_id` INT NOT NULL ,
  `team_id` INT NOT NULL ,
  `game_id` INT NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `station_instance-game_fk` (`game_id` ASC) ,
  INDEX `station_instance-station_fk` (`station_id` ASC) ,
  INDEX `station_instance-team_fk` (`team_id` ASC) ,
  CONSTRAINT `station_instance-game_fk`
    FOREIGN KEY (`game_id` )
    REFERENCES `SprintStad`.`Game` (`id` )
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `station_instance-station_fk`
    FOREIGN KEY (`station_id` )
    REFERENCES `SprintStad`.`Station` (`id` )
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `station_instance-team_fk`
    FOREIGN KEY (`team_id` )
    REFERENCES `SprintStad`.`Team` (`id` )
    ON DELETE RESTRICT
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `SprintStad`.`Types`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `SprintStad`.`Types` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `name` TINYTEXT NULL ,
  `type` SET('home','work','leisure') NULL ,
  `description` TEXT NULL ,
  `image` TINYTEXT NULL ,
  `density` FLOAT NOT NULL COMMENT 'Beeld wonen (wo/ha)\nBeeld werken (bvo/ha)\nBeeld leisure (bvo/ha)' ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `SprintStad`.`Program`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `SprintStad`.`Program` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `area_home` INT NULL ,
  `area_work` INT NULL ,
  `area_leisure` INT NULL ,
  `type_home` INT NULL ,
  `type_work` INT NULL ,
  `type_leisure` INT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `type_home_fk` (`type_home` ASC) ,
  INDEX `type_work_fk` (`type_work` ASC) ,
  INDEX `type_leisure_fk` (`type_leisure` ASC) ,
  CONSTRAINT `type_home_fk`
    FOREIGN KEY (`type_home` )
    REFERENCES `SprintStad`.`Types` (`id` )
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `type_work_fk`
    FOREIGN KEY (`type_work` )
    REFERENCES `SprintStad`.`Types` (`id` )
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `type_leisure_fk`
    FOREIGN KEY (`type_leisure` )
    REFERENCES `SprintStad`.`Types` (`id` )
    ON DELETE RESTRICT
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `SprintStad`.`Round`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `SprintStad`.`Round` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `station_id` INT NOT NULL ,
  `round_info_id` INT NOT NULL ,
  `description` TEXT NULL ,
  `new_transform_area` INT NULL ,
  `network_value` INT NULL ,
  `POVN` INT NULL ,
  `PWN` INT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `round-station_fk` (`station_id` ASC) ,
  INDEX `round-round_info_fk` (`round_info_id` ASC) ,
  CONSTRAINT `round-station_fk`
    FOREIGN KEY (`station_id` )
    REFERENCES `SprintStad`.`Station` (`id` )
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `round-round_info_fk`
    FOREIGN KEY (`round_info_id` )
    REFERENCES `SprintStad`.`RoundInfo` (`id` )
    ON DELETE RESTRICT
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `SprintStad`.`RoundInstance`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `SprintStad`.`RoundInstance` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `round_id` INT NOT NULL ,
  `station_instance_id` INT NOT NULL ,
  `program_id` INT NULL ,
  `starttime` DATETIME NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `round_instance-round_fk` (`round_id` ASC) ,
  INDEX `round_instance-program_fk` (`program_id` ASC) ,
  INDEX `round_instance-station_instance_fk` (`station_instance_id` ASC) ,
  CONSTRAINT `round_instance-round_fk`
    FOREIGN KEY (`round_id` )
    REFERENCES `SprintStad`.`Round` (`id` )
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `round_instance-program_fk`
    FOREIGN KEY (`program_id` )
    REFERENCES `SprintStad`.`Program` (`id` )
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `round_instance-station_instance_fk`
    FOREIGN KEY (`station_instance_id` )
    REFERENCES `SprintStad`.`StationInstance` (`id` )
    ON DELETE RESTRICT
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `SprintStad`.`Constants`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `SprintStad`.`Constants` (
  `average_citizens_per_home` FLOAT NULL DEFAULT 2.3 COMMENT 'Gemiddeld aantal inwoners per woning(2.3inw/won)' ,
  `average_workers_per_bvo` FLOAT NULL DEFAULT 0.04 )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `SprintStad`.`StationTypes`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `SprintStad`.`StationTypes` (
  `id` INT NOT NULL ,
  `name` TINYTEXT NULL ,
  `description` TEXT NULL ,
  `image` TINYTEXT NULL ,
  `POVN` FLOAT NULL COMMENT 'Positie in het openbaar vervoersnetwerk' ,
  `PWN` FLOAT NULL COMMENT 'Positie in het wegennetwerk' ,
  `IWD` FLOAT NULL COMMENT 'Inwoners- en werknemersdichtheid in 2010' ,
  `MNG` FLOAT NULL COMMENT 'Mengingsintensiteit' ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `SprintStad`.`sessions`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `SprintStad`.`sessions` (
  `id` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL ,
  `data` TEXT CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL ,
  `updated_on` INT(10) NOT NULL ,
  PRIMARY KEY (`id`) )
ENGINE = MyISAM
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `SprintStad`.`users`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `SprintStad`.`users` (
  `id` INT(11) NOT NULL AUTO_INCREMENT ,
  `username` VARCHAR(65) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL ,
  `password` VARCHAR(65) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL ,
  `level` ENUM('user','admin') CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL ,
  `email` VARCHAR(65) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NULL DEFAULT NULL ,
  PRIMARY KEY (`id`) ,
  UNIQUE INDEX `username` (`username` ASC) )
ENGINE = MyISAM
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;



SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;

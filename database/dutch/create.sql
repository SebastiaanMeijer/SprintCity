SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

CREATE SCHEMA IF NOT EXISTS `SprintCity` DEFAULT CHARACTER SET latin1 COLLATE latin1_swedish_ci ;
USE `SprintCity` ;

-- -----------------------------------------------------
-- Table `Station`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `Station` ;

CREATE  TABLE IF NOT EXISTS `Station` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `code` VARCHAR(5) NULL ,
  `name` TINYTEXT NULL ,
  `variant` TINYTEXT NULL ,
  `description_facts` TEXT NULL ,
  `description_background` TEXT NULL ,
  `description_future` TEXT NULL ,
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
  `transform_area_undeveloped_rural` INT NULL ,
  `count_home_total` INT NULL COMMENT 'Aantal woningen totaal gebied' ,
  `count_home_transform` INT NULL COMMENT 'Aantal woningen transformatiegebied' ,
  `count_work_total` INT NULL COMMENT 'Aantal werk bvo totaal gebied' ,
  `count_work_transform` INT NULL COMMENT 'Aantal werk bvo transformatiegebied' ,
  `count_worker_total` INT NULL COMMENT 'Aantal werkplekken (=werknemers) in het totale gebied (work + mixed).' ,
  `count_worker_transform` INT NULL COMMENT 'Aantal werkplekken (=werknemers) in het tranformatiegebied (work + mixed).' ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `RoundInfo`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `RoundInfo` ;

CREATE  TABLE IF NOT EXISTS `RoundInfo` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `number` INT NOT NULL ,
  `name` TINYTEXT NULL ,
  `description` TEXT NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `TrainTable`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `TrainTable` ;

CREATE  TABLE IF NOT EXISTS `TrainTable` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `filename` TINYTEXT NULL ,
  `import_timestamp` DATETIME NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `Scenario`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `Scenario` ;

CREATE  TABLE IF NOT EXISTS `Scenario` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `train_table_id` INT NULL ,
  `name` TINYTEXT NULL ,
  `description` TEXT NULL ,
  `init_map_position_x` FLOAT NOT NULL DEFAULT 0 ,
  `init_map_position_y` FLOAT NOT NULL DEFAULT 0 ,
  `init_map_scale` FLOAT NOT NULL DEFAULT 1 ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_Scenario_TrainTable1` (`train_table_id` ASC) ,
  CONSTRAINT `fk_Scenario_TrainTable1`
    FOREIGN KEY (`train_table_id` )
    REFERENCES `TrainTable` (`id` )
    ON DELETE RESTRICT
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `Game`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `Game` ;

CREATE  TABLE IF NOT EXISTS `Game` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `scenario_id` INT NOT NULL ,
  `name` TINYTEXT NULL ,
  `notes` TEXT NULL ,
  `starttime` DATETIME NULL ,
  `current_round_id` INT NULL DEFAULT 1 ,
  `active` TINYINT(1) NULL DEFAULT true ,
  PRIMARY KEY (`id`) ,
  INDEX `current_round_fk` (`current_round_id` ASC) ,
  INDEX `scenario_fk` (`scenario_id` ASC) ,
  CONSTRAINT `current_round_fk`
    FOREIGN KEY (`current_round_id` )
    REFERENCES `RoundInfo` (`id` )
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `scenario_fk`
    FOREIGN KEY (`scenario_id` )
    REFERENCES `Scenario` (`id` )
    ON DELETE RESTRICT
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `Team`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `Team` ;

CREATE  TABLE IF NOT EXISTS `Team` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `name` TINYTEXT NULL ,
  `description` TEXT NULL ,
  `color` VARCHAR(6) NULL DEFAULT '000000' ,
  `cpu` TINYINT(1) NOT NULL DEFAULT false ,
  `created` DATETIME NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `TeamInstance`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `TeamInstance` ;

CREATE  TABLE IF NOT EXISTS `TeamInstance` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `game_id` INT NOT NULL ,
  `team_id` INT NOT NULL ,
  `value_description` TEXT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `team_instance-team_fk` (`team_id` ASC) ,
  INDEX `team_instance-game_fk` (`game_id` ASC) ,
  CONSTRAINT `team_instance-team_fk`
    FOREIGN KEY (`team_id` )
    REFERENCES `Team` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `team_instance-game_fk`
    FOREIGN KEY (`game_id` )
    REFERENCES `Game` (`id` )
    ON DELETE RESTRICT
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `Types`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `Types` ;

CREATE  TABLE IF NOT EXISTS `Types` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `name` TINYTEXT NULL ,
  `type` SET('home','work','leisure','average_home','average_work','average_leisure') NULL ,
  `description` TEXT NULL ,
  `color` VARCHAR(6) NULL DEFAULT '000000' ,
  `image` TINYTEXT NULL ,
  `area_density` FLOAT NOT NULL COMMENT 'Beeld wonen (wo/ha)\\nBeeld werken (bvo/ha)\\nBeeld leisure (bvo/ha)' ,
  `people_density` FLOAT NOT NULL COMMENT 'Beeld wonen (wo/ha)\\nBeeld werken (werknemers/ha)\\nBeeld leisure (werknemers/ha)' ,
  `POVN` INT NOT NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `Program`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `Program` ;

CREATE  TABLE IF NOT EXISTS `Program` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `area_home` INT NULL DEFAULT 0 ,
  `area_work` INT NULL DEFAULT 0 ,
  `area_leisure` INT NULL DEFAULT 0 ,
  `type_home` INT NULL DEFAULT 15 ,
  `type_work` INT NULL DEFAULT 16 ,
  `type_leisure` INT NULL DEFAULT 17 ,
  PRIMARY KEY (`id`) ,
  INDEX `type_home_fk` (`type_home` ASC) ,
  INDEX `type_work_fk` (`type_work` ASC) ,
  INDEX `type_leisure_fk` (`type_leisure` ASC) ,
  CONSTRAINT `type_home_fk`
    FOREIGN KEY (`type_home` )
    REFERENCES `Types` (`id` )
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `type_work_fk`
    FOREIGN KEY (`type_work` )
    REFERENCES `Types` (`id` )
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `type_leisure_fk`
    FOREIGN KEY (`type_leisure` )
    REFERENCES `Types` (`id` )
    ON DELETE RESTRICT
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `StationInstance`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `StationInstance` ;

CREATE  TABLE IF NOT EXISTS `StationInstance` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `station_id` INT NOT NULL ,
  `team_instance_id` INT NOT NULL ,
  `program_id` INT NULL ,
  `initial_POVN` INT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `station_instance-station_fk` (`station_id` ASC) ,
  INDEX `station_instance-team_instance_fk` (`team_instance_id` ASC) ,
  INDEX `station_instance-program_fk` (`program_id` ASC) ,
  CONSTRAINT `station_instance-station_fk`
    FOREIGN KEY (`station_id` )
    REFERENCES `Station` (`id` )
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `station_instance-team_instance_fk`
    FOREIGN KEY (`team_instance_id` )
    REFERENCES `TeamInstance` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `station_instance-program_fk`
    FOREIGN KEY (`program_id` )
    REFERENCES `Program` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `Round`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `Round` ;

CREATE  TABLE IF NOT EXISTS `Round` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `station_id` INT NOT NULL ,
  `round_info_id` INT NOT NULL ,
  `description` TEXT NULL ,
  `new_transform_area` INT NULL ,
  `POVN` INT NULL ,
  `PWN` INT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `round-station_fk` (`station_id` ASC) ,
  INDEX `round-round_info_fk` (`round_info_id` ASC) ,
  CONSTRAINT `round-station_fk`
    FOREIGN KEY (`station_id` )
    REFERENCES `Station` (`id` )
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `round-round_info_fk`
    FOREIGN KEY (`round_info_id` )
    REFERENCES `RoundInfo` (`id` )
    ON DELETE RESTRICT
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `RoundInstance`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `RoundInstance` ;

CREATE  TABLE IF NOT EXISTS `RoundInstance` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `round_id` INT NOT NULL ,
  `station_instance_id` INT NOT NULL ,
  `plan_program_id` INT NULL ,
  `exec_program_id` INT NULL ,
  `starttime` DATETIME NULL ,
  `POVN` INT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `round_instance-round_fk` (`round_id` ASC) ,
  INDEX `round_instance-program_fk` (`plan_program_id` ASC) ,
  INDEX `round_instance-station_instance_fk` (`station_instance_id` ASC) ,
  INDEX `round_instance_program_fk2` (`exec_program_id` ASC) ,
  CONSTRAINT `round_instance-round_fk`
    FOREIGN KEY (`round_id` )
    REFERENCES `Round` (`id` )
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `round_instance-program_fk`
    FOREIGN KEY (`plan_program_id` )
    REFERENCES `Program` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `round_instance-station_instance_fk`
    FOREIGN KEY (`station_instance_id` )
    REFERENCES `StationInstance` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `round_instance_program_fk2`
    FOREIGN KEY (`exec_program_id` )
    REFERENCES `Program` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `Constants`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `Constants` ;

CREATE  TABLE IF NOT EXISTS `Constants` (
  `average_citizens_per_home` FLOAT NOT NULL DEFAULT 2.3 COMMENT 'Gemiddeld aantal inwoners per woning(2.3inw/won)' ,
  `average_workers_per_bvo` FLOAT NOT NULL DEFAULT 0.04 ,
  `average_travelers_per_citizen` FLOAT NOT NULL DEFAULT 0.15 ,
  `average_travelers_per_worker` FLOAT NOT NULL DEFAULT 0.08 ,
  `average_travelers_per_ha_leisure` FLOAT NOT NULL DEFAULT 2 )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `StationTypes`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `StationTypes` ;

CREATE  TABLE IF NOT EXISTS `StationTypes` (
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
-- Table `sessions`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `sessions` ;

CREATE  TABLE IF NOT EXISTS `sessions` (
  `id` VARCHAR(255) CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL ,
  `data` TEXT CHARACTER SET 'utf8' COLLATE 'utf8_unicode_ci' NOT NULL ,
  `updated_on` INT(10) NOT NULL ,
  PRIMARY KEY (`id`) )
ENGINE = MyISAM
DEFAULT CHARACTER SET = utf8
COLLATE = utf8_unicode_ci;


-- -----------------------------------------------------
-- Table `users`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `users` ;

CREATE  TABLE IF NOT EXISTS `users` (
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


-- -----------------------------------------------------
-- Table `Value`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `Value` ;

CREATE  TABLE IF NOT EXISTS `Value` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `title` TINYTEXT NULL ,
  `description` TEXT NULL ,
  `type` SET('area','mobility') NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `ValueInstance`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `ValueInstance` ;

CREATE  TABLE IF NOT EXISTS `ValueInstance` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `value_id` INT NOT NULL ,
  `team_instance_id` INT NOT NULL ,
  `checked` TINYINT(1) NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `value_instance-value_fk` (`value_id` ASC) ,
  INDEX `value_instance-team_instance_fk` (`team_instance_id` ASC) ,
  CONSTRAINT `value_instance-value_fk`
    FOREIGN KEY (`value_id` )
    REFERENCES `Value` (`id` )
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `value_instance-team_instance_fk`
    FOREIGN KEY (`team_instance_id` )
    REFERENCES `TeamInstance` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `ClientSession`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `ClientSession` ;

CREATE  TABLE IF NOT EXISTS `ClientSession` (
  `id` VARCHAR(255) NOT NULL ,
  `team_instance_id` INT NOT NULL ,
  `created` INT NOT NULL ,
  INDEX `client_session-team_instance-fk` (`team_instance_id` ASC) ,
  PRIMARY KEY (`id`) ,
  CONSTRAINT `client_session-team_instance-fk`
    FOREIGN KEY (`team_instance_id` )
    REFERENCES `TeamInstance` (`id` )
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `Demand`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `Demand` ;

CREATE  TABLE IF NOT EXISTS `Demand` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `scenario_id` INT NOT NULL ,
  `round_info_id` INT NOT NULL ,
  `type_id` INT NOT NULL ,
  `amount` FLOAT NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `demand-round_info_fk` (`round_info_id` ASC) ,
  INDEX `demand-types_fk` (`type_id` ASC) ,
  INDEX `demand-scenario_fk` (`scenario_id` ASC) ,
  CONSTRAINT `demand-round_info_fk`
    FOREIGN KEY (`round_info_id` )
    REFERENCES `RoundInfo` (`id` )
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `demand-types_fk`
    FOREIGN KEY (`type_id` )
    REFERENCES `Types` (`id` )
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `demand-scenario_fk`
    FOREIGN KEY (`scenario_id` )
    REFERENCES `Scenario` (`id` )
    ON DELETE RESTRICT
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `RoundInfoInstance`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `RoundInfoInstance` ;

CREATE  TABLE IF NOT EXISTS `RoundInfoInstance` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `game_id` INT NOT NULL ,
  `round_info_id` INT NOT NULL ,
  `mobility_report` TEXT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `round_info_instance-game_fk` (`game_id` ASC) ,
  INDEX `round_info_instance-round_info_fk` (`round_info_id` ASC) ,
  CONSTRAINT `round_info_instance-game_fk`
    FOREIGN KEY (`game_id` )
    REFERENCES `Game` (`id` )
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `round_info_instance-round_info_fk`
    FOREIGN KEY (`round_info_id` )
    REFERENCES `RoundInfo` (`id` )
    ON DELETE RESTRICT
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `ScenarioStation`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `ScenarioStation` ;

CREATE  TABLE IF NOT EXISTS `ScenarioStation` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `order` INT NOT NULL DEFAULT 1 ,
  `scenario_id` INT NOT NULL ,
  `station_id` INT NOT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `scenario_station-scenario_fk` (`scenario_id` ASC) ,
  INDEX `scenario_station-station_fk` (`station_id` ASC) ,
  CONSTRAINT `scenario_station-scenario_fk`
    FOREIGN KEY (`scenario_id` )
    REFERENCES `Scenario` (`id` )
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `scenario_station-station_fk`
    FOREIGN KEY (`station_id` )
    REFERENCES `Station` (`id` )
    ON DELETE RESTRICT
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `TrainTableStation`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `TrainTableStation` ;

CREATE  TABLE IF NOT EXISTS `TrainTableStation` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `train_table_id` INT NOT NULL ,
  `code` VARCHAR(5) NULL ,
  `name` TINYTEXT NULL ,
  `chain` INT NULL ,
  `travelers` INT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_TrainTableStation_TrainTable1` (`train_table_id` ASC) ,
  INDEX `fk_Stationcodes` (`code` ASC) ,
  CONSTRAINT `fk_TrainTableStation_TrainTable1`
    FOREIGN KEY (`train_table_id` )
    REFERENCES `TrainTable` (`id` )
    ON DELETE RESTRICT
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `TrainTableTrain`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `TrainTableTrain` ;

CREATE  TABLE IF NOT EXISTS `TrainTableTrain` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `train_table_id` INT NOT NULL ,
  `name` TINYTEXT NULL ,
  `type` TINYTEXT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `fk_TrainTableTrain_TrainTable1` (`train_table_id` ASC) ,
  CONSTRAINT `fk_TrainTableTrain_TrainTable1`
    FOREIGN KEY (`train_table_id` )
    REFERENCES `TrainTable` (`id` )
    ON DELETE RESTRICT
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `TrainTableEntry`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `TrainTableEntry` ;

CREATE  TABLE IF NOT EXISTS `TrainTableEntry` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `train_id` INT NOT NULL ,
  `station_id` INT NOT NULL ,
  `frequency` INT NULL ,
  INDEX `fk_TrainTableEntry_TrainTableTrain1` (`train_id` ASC) ,
  INDEX `fk_TrainTableEntry_TrainTableStation1` (`station_id` ASC) ,
  PRIMARY KEY (`id`) ,
  CONSTRAINT `fk_TrainTableEntry_TrainTableTrain1`
    FOREIGN KEY (`train_id` )
    REFERENCES `TrainTableTrain` (`id` )
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `fk_TrainTableEntry_TrainTableStation1`
    FOREIGN KEY (`station_id` )
    REFERENCES `TrainTableStation` (`id` )
    ON DELETE RESTRICT
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `TrainTableEntryInstance`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `TrainTableEntryInstance` ;

CREATE  TABLE IF NOT EXISTS `TrainTableEntryInstance` (
  `round_info_instance_id` INT NOT NULL ,
  `train_id` INT NOT NULL ,
  `station_id` INT NOT NULL ,
  `frequency` INT NULL ,
  INDEX `fk_TrainTableEntryInstance_TrainTableTrain1` (`train_id` ASC) ,
  INDEX `fk_TrainTableEntryInstance_TrainTableStation1` (`station_id` ASC) ,
  PRIMARY KEY (`round_info_instance_id`, `train_id`, `station_id`) ,
  INDEX `fk_TrainTableEntryInstance_RoundInfoInstance1` (`round_info_instance_id` ASC) ,
  CONSTRAINT `fk_TrainTableEntryInstance_TrainTableTrain1`
    FOREIGN KEY (`train_id` )
    REFERENCES `TrainTableTrain` (`id` )
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `fk_TrainTableEntryInstance_TrainTableStation1`
    FOREIGN KEY (`station_id` )
    REFERENCES `TrainTableStation` (`id` )
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `fk_TrainTableEntryInstance_RoundInfoInstance1`
    FOREIGN KEY (`round_info_instance_id` )
    REFERENCES `RoundInfoInstance` (`id` )
    ON DELETE RESTRICT
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `TravelerHistory`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `TravelerHistory` ;

CREATE  TABLE IF NOT EXISTS `TravelerHistory` (
  `round_info_instance_id` INT NOT NULL ,
  `station_id` INT NOT NULL ,
  `travelers_per_stop` INT NULL ,
  INDEX `fk_StationTravelerHistory_TrainTableStation1` (`station_id` ASC) ,
  PRIMARY KEY (`round_info_instance_id`, `station_id`) ,
  INDEX `fk_StationTravelerHistory_RoundInfoInstance1` (`round_info_instance_id` ASC) ,
  CONSTRAINT `fk_StationTravelerHistory_TrainTableStation1`
    FOREIGN KEY (`station_id` )
    REFERENCES `TrainTableStation` (`id` )
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `fk_StationTravelerHistory_RoundInfoInstance1`
    FOREIGN KEY (`round_info_instance_id` )
    REFERENCES `RoundInfoInstance` (`id` )
    ON DELETE RESTRICT
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `Facility`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `Facility` ;

CREATE  TABLE IF NOT EXISTS `Facility` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `name` TINYTEXT NULL ,
  `description` TEXT NULL ,
  `image` TINYTEXT NULL ,
  `citizens` INT NULL ,
  `workers` INT NULL ,
  `travelers` INT NULL ,
  PRIMARY KEY (`id`) )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `FacilityInstance`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `FacilityInstance` ;

CREATE  TABLE IF NOT EXISTS `FacilityInstance` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `round_instance_id` INT NULL ,
  `facility_id` INT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `facility-instance_round-instance_fk_idx` (`round_instance_id` ASC) ,
  INDEX `facility-instance_facility_fk_idx` (`facility_id` ASC) ,
  CONSTRAINT `facility-instance_round-instance_fk`
    FOREIGN KEY (`round_instance_id` )
    REFERENCES `RoundInstance` (`id` )
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `facility-instance_facility_fk`
    FOREIGN KEY (`facility_id` )
    REFERENCES `Facility` (`id` )
    ON DELETE RESTRICT
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `TypeRestriction`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `TypeRestriction` ;

CREATE  TABLE IF NOT EXISTS `TypeRestriction` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `station_instance_id` INT NULL ,
  `from_round_info_id` INT NULL ,
  `to_round_info_id` INT NULL ,
  `type_id` INT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `type-restriction_station-instance_fk_idx` (`station_instance_id` ASC) ,
  INDEX `from_type-restriction_station-instance_fk_idx` (`from_round_info_id` ASC) ,
  INDEX `to_type-restriction_round-info_fk_idx` (`to_round_info_id` ASC) ,
  INDEX `type-restriction_type_fk_idx` (`type_id` ASC) ,
  CONSTRAINT `type-restriction_station-instance_fk`
    FOREIGN KEY (`station_instance_id` )
    REFERENCES `StationInstance` (`id` )
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `from_type-restriction_round-info_fk`
    FOREIGN KEY (`from_round_info_id` )
    REFERENCES `RoundInfo` (`id` )
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `to_type-restriction_round-info_fk`
    FOREIGN KEY (`to_round_info_id` )
    REFERENCES `RoundInfo` (`id` )
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `type-restriction_type_fk`
    FOREIGN KEY (`type_id` )
    REFERENCES `Types` (`id` )
    ON DELETE RESTRICT
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `InitialNetworkValues`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `InitialNetworkValues` ;

CREATE  TABLE IF NOT EXISTS `InitialNetworkValues` (
  `station_id` INT NULL ,
  `networkValue` DOUBLE NULL ,
  `chainValue` INT NULL ,
  `game_id` INT NULL )
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `InitialTravelersPerStop`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `InitialTravelersPerStop` ;

CREATE  TABLE IF NOT EXISTS `InitialTravelersPerStop` (
  `train_id` INT NULL ,
  `station_id` INT NULL ,
  `travelersPerStop` INT NULL ,
  `game_id` INT NULL )
ENGINE = InnoDB;



SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;

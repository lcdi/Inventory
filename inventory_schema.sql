-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

-- -----------------------------------------------------
-- Schema inventory
-- -----------------------------------------------------

-- -----------------------------------------------------
-- Schema inventory
-- -----------------------------------------------------
CREATE SCHEMA IF NOT EXISTS `inventory` DEFAULT CHARACTER SET utf8 ;
USE `inventory` ;

-- -----------------------------------------------------
-- Table `inventory`.`device`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `inventory`.`device` (
  `SerialNumber` VARCHAR(255) NOT NULL DEFAULT 'LCDI-00000',
  `SerialDevice` VARCHAR(255) NULL,
  `Type` VARCHAR(255) NULL DEFAULT 'NONE',
  `Description` TEXT NULL,
  `Issues` TEXT NULL,
  `PhotoName` VARCHAR(255) NULL,
  `Quality` VARCHAR(255) NULL,
  PRIMARY KEY (`SerialNumber`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `inventory`.`log`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `inventory`.`log` (
  `Identifier` INT NOT NULL AUTO_INCREMENT,
  `SerialNumber` VARCHAR(255) NOT NULL,
  `UserIdentifier` VARCHAR(255) NOT NULL,
  `Purpose` TEXT NOT NULL,
  `DateOut` DATE NOT NULL,
  `DateIn` DATE NOT NULL,
  `AuthorizerIdentifier` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`Identifier`),
  INDEX `SerialNumber_idx` (`SerialNumber` ASC),
  CONSTRAINT `SerialNumber`
    FOREIGN KEY (`SerialNumber`)
    REFERENCES `inventory`.`device` (`SerialNumber`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;

SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;

SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';


-- -----------------------------------------------------
-- Schema dbass
-- -----------------------------------------------------
DROP SCHEMA IF EXISTS `dbass` ;
CREATE SCHEMA IF NOT EXISTS `dbass` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ;
USE `dbass` ;

-- -----------------------------------------------------
-- Table `dbass`.`userpower`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `dbass`.`userpower` ;

CREATE TABLE IF NOT EXISTS `dbass`.`userpower` (
  `id` INT UNSIGNED NOT NULL,
  `label` VARCHAR(30) NOT NULL,
  `description` LONGTEXT NULL COMMENT 'This is where you describe the privilege this user holds.',
  PRIMARY KEY (`id`),
  UNIQUE INDEX `id_UNIQUE` (`id` ASC),
  UNIQUE INDEX `label_UNIQUE` (`label` ASC))
ENGINE = InnoDB
AUTO_INCREMENT = 1;


-- -----------------------------------------------------
-- Table `dbass`.`user`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `dbass`.`user` ;

CREATE TABLE IF NOT EXISTS `dbass`.`user` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(20) NOT NULL,
  `password` VARCHAR(150) NOT NULL,
  `email` VARCHAR(60) NOT NULL,
  `secquestion` VARCHAR(375) NOT NULL,
  `secanswer` VARCHAR(150) NOT NULL,
  `status` INT UNSIGNED NOT NULL COMMENT 'Given value of account status' /* comment truncated */ /*0 = Active
1 = Inactive
2 = Pending*/,
  `is_online` TINYINT UNSIGNED NOT NULL,
  `userpower_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`id`, `username`),
  UNIQUE INDEX `id_UNIQUE` (`id` ASC),
  UNIQUE INDEX `username_UNIQUE` (`username` ASC),
  INDEX `user_fk_userpower_idx` (`userpower_id` ASC),
  CONSTRAINT `user_fk_userpower_id`
    FOREIGN KEY (`userpower_id`)
    REFERENCES `dbass`.`userpower` (`id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION)
ENGINE = InnoDB
AUTO_INCREMENT = 1
ROW_FORMAT = DEFAULT;


-- -----------------------------------------------------
-- Table `dbass`.`course`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `dbass`.`course` ;

CREATE TABLE IF NOT EXISTS `dbass`.`course` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(11) NOT NULL,
  `name` VARCHAR(30) NOT NULL COMMENT 'Qualified 30 characters Course name, unique, of course',
  `description` LONGTEXT NULL COMMENT 'The description of this course',
  `teacher_id` INT UNSIGNED NOT NULL COMMENT '(FK) The teacher who authored this course.',
  `starttime` TIME NULL,
  `endtime` VARCHAR(45) NULL,
  `is_active` TINYINT NULL,
  `gperiod` VARCHAR(45) NULL COMMENT 'The time to be considered within \"Grace period time\"' /* comment truncated */ /*NOTE: Should be less than `endtime`*/,
  PRIMARY KEY (`id`, `code`),
  UNIQUE INDEX `id_UNIQUE` (`id` ASC),
  UNIQUE INDEX `code_UNIQUE` (`code` ASC),
  INDEX `course_fk_user_id_idx` (`teacher_id` ASC),
  CONSTRAINT `course_fk_user_id`
    FOREIGN KEY (`teacher_id`)
    REFERENCES `dbass`.`user` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `dbass`.`sy`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `dbass`.`sy` ;

CREATE TABLE IF NOT EXISTS `dbass`.`sy` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `year` INT UNSIGNED NOT NULL,
  `description` MEDIUMTEXT NULL,
  `admin_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `id_UNIQUE` (`id` ASC),
  UNIQUE INDEX `year_UNIQUE` (`year` ASC),
  INDEX `sy_fk_user_id_idx` (`admin_id` ASC),
  CONSTRAINT `sy_fk_user_id`
    FOREIGN KEY (`admin_id`)
    REFERENCES `dbass`.`user` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `dbass`.`studyfield`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `dbass`.`studyfield` ;

CREATE TABLE IF NOT EXISTS `dbass`.`studyfield` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `description` LONGTEXT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `id_UNIQUE` (`id` ASC),
  UNIQUE INDEX `name_UNIQUE` (`name` ASC))
ENGINE = InnoDB
AUTO_INCREMENT = 1
ROW_FORMAT = DEFAULT;


-- -----------------------------------------------------
-- Table `dbass`.`studentprofile`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `dbass`.`studentprofile` ;

CREATE TABLE IF NOT EXISTS `dbass`.`studentprofile` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `student_id` INT UNSIGNED NOT NULL,
  `studyfield_id` INT UNSIGNED NOT NULL,
  UNIQUE INDEX `student_id_UNIQUE` (`student_id` ASC),
  PRIMARY KEY (`id`),
  UNIQUE INDEX `id_UNIQUE` (`id` ASC),
  INDEX `studentprofile_fk_user_studyfield_id_idx` (`studyfield_id` ASC),
  CONSTRAINT `studentprofile_fk_user_id`
    FOREIGN KEY (`student_id`)
    REFERENCES `dbass`.`user` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `studentprofile_fk_user_studyfield_id`
    FOREIGN KEY (`studyfield_id`)
    REFERENCES `dbass`.`studyfield` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `dbass`.`studentcourse`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `dbass`.`studentcourse` ;

CREATE TABLE IF NOT EXISTS `dbass`.`studentcourse` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `course_id` INT UNSIGNED NOT NULL,
  `student_id` INT UNSIGNED NOT NULL,
  `sy_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `id_UNIQUE` (`id` ASC),
  INDEX `studentcourse_fk_user_id_idx` (`student_id` ASC),
  INDEX `studentcourse_fk_course_id_idx` (`course_id` ASC),
  INDEX `studentcourse_fk_sy_id_idx` (`sy_id` ASC),
  CONSTRAINT `studentcourse_fk_course_id`
    FOREIGN KEY (`course_id`)
    REFERENCES `dbass`.`course` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `studentcourse_fk_user_id`
    FOREIGN KEY (`student_id`)
    REFERENCES `dbass`.`user` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `studentcourse_fk_sy_id`
    FOREIGN KEY (`sy_id`)
    REFERENCES `dbass`.`sy` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `dbass`.`attendance`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `dbass`.`attendance` ;

CREATE TABLE IF NOT EXISTS `dbass`.`attendance` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `createdate` DATE NOT NULL,
  `createtime` TIME NOT NULL,
  `course_id` INT UNSIGNED NOT NULL,
  `student_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `id_UNIQUE` (`id` ASC),
  INDEX `attendance_fk_course_id_idx` (`course_id` ASC),
  INDEX `attendance_fk_user_id_idx` (`student_id` ASC),
  CONSTRAINT `attendance_fk_course_id`
    FOREIGN KEY (`course_id`)
    REFERENCES `dbass`.`course` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `attendance_fk_user_id`
    FOREIGN KEY (`student_id`)
    REFERENCES `dbass`.`user` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
AUTO_INCREMENT = 1;


-- -----------------------------------------------------
-- Table `dbass`.`gscheme`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `dbass`.`gscheme` ;

CREATE TABLE IF NOT EXISTS `dbass`.`gscheme` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(40) NOT NULL,
  `description` LONGTEXT NULL,
  `course_id` INT UNSIGNED NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `id_UNIQUE` (`id` ASC),
  UNIQUE INDEX `name_UNIQUE` (`name` ASC),
  INDEX `fk_course_id_idx` (`course_id` ASC),
  CONSTRAINT `fk_course_id`
    FOREIGN KEY (`course_id`)
    REFERENCES `dbass`.`course` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
AUTO_INCREMENT = 1;


-- -----------------------------------------------------
-- Table `dbass`.`gschemecomponent`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `dbass`.`gschemecomponent` ;

CREATE TABLE IF NOT EXISTS `dbass`.`gschemecomponent` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(45) NOT NULL,
  `percentage` VARCHAR(45) NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `id_UNIQUE` (`id` ASC),
  UNIQUE INDEX `name_UNIQUE` (`name` ASC))
ENGINE = InnoDB
AUTO_INCREMENT = 1;


-- -----------------------------------------------------
-- Table `dbass`.`profile`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `dbass`.`profile` ;

CREATE TABLE IF NOT EXISTS `dbass`.`profile` (
  `id` INT NOT NULL AUTO_INCREMENT,
  `user_id` INT UNSIGNED NOT NULL,
  `fname` VARCHAR(40) NOT NULL,
  `mname` VARCHAR(40) NULL,
  `lname` VARCHAR(40) NOT NULL,
  `gender` VARCHAR(6) NOT NULL,
  `address1` LONGTEXT NOT NULL,
  `address2` LONGTEXT NULL,
  `city` VARCHAR(30) NOT NULL,
  `province` VARCHAR(50) NOT NULL,
  `birthdate` DATE NOT NULL,
  `mobile` VARCHAR(15) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `user_id_UNIQUE` (`user_id` ASC),
  CONSTRAINT `fk_user_id`
    FOREIGN KEY (`user_id`)
    REFERENCES `dbass`.`user` (`id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION)
ENGINE = InnoDB;

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;

-- -----------------------------------------------------
-- Data for table `dbass`.`userpower`
-- -----------------------------------------------------
START TRANSACTION;
USE `dbass`;
INSERT INTO `dbass`.`userpower` (`id`, `label`, `description`) VALUES (0, 'Admin', 'The system admin that manages every existing resource in the system.');
INSERT INTO `dbass`.`userpower` (`id`, `label`, `description`) VALUES (1, 'Instructor', 'Manages respective courses and student entries/records.');
INSERT INTO `dbass`.`userpower` (`id`, `label`, `description`) VALUES (2, 'Student', 'Account for students availing course enrollments in the system.');

COMMIT;


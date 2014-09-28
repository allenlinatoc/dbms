SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

-- -----------------------------------------------------
-- Schema u901322185_dbms
-- -----------------------------------------------------
DROP SCHEMA IF EXISTS `u901322185_dbms` ;
CREATE SCHEMA IF NOT EXISTS `u901322185_dbms` DEFAULT CHARACTER SET utf8 COLLATE utf8_general_ci ;
-- -----------------------------------------------------
-- Schema thegenerals
-- -----------------------------------------------------
DROP SCHEMA IF EXISTS `thegenerals` ;
CREATE SCHEMA IF NOT EXISTS `thegenerals` DEFAULT CHARACTER SET latin1 ;
USE `u901322185_dbms` ;

-- -----------------------------------------------------
-- Table `u901322185_dbms`.`userpower`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `u901322185_dbms`.`userpower` ;

CREATE TABLE IF NOT EXISTS `u901322185_dbms`.`userpower` (
  `id` INT UNSIGNED NOT NULL,
  `label` VARCHAR(30) NOT NULL,
  `description` LONGTEXT NULL COMMENT 'This is where you describe the privilege this user holds.',
  PRIMARY KEY (`id`),
  UNIQUE INDEX `id_UNIQUE` (`id` ASC),
  UNIQUE INDEX `label_UNIQUE` (`label` ASC))
ENGINE = InnoDB
AUTO_INCREMENT = 1;


-- -----------------------------------------------------
-- Table `u901322185_dbms`.`user`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `u901322185_dbms`.`user` ;

CREATE TABLE IF NOT EXISTS `u901322185_dbms`.`user` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `username` VARCHAR(20) NOT NULL,
  `password` VARCHAR(450) NOT NULL,
  `email` VARCHAR(60) NOT NULL,
  `secquestion` VARCHAR(1125) NOT NULL,
  `secanswer` VARCHAR(450) NOT NULL,
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
    REFERENCES `u901322185_dbms`.`userpower` (`id`)
    ON DELETE CASCADE
    ON UPDATE NO ACTION)
ENGINE = InnoDB
AUTO_INCREMENT = 1
ROW_FORMAT = DEFAULT;


-- -----------------------------------------------------
-- Table `u901322185_dbms`.`course`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `u901322185_dbms`.`course` ;

CREATE TABLE IF NOT EXISTS `u901322185_dbms`.`course` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `code` VARCHAR(11) NOT NULL,
  `name` VARCHAR(30) NOT NULL COMMENT 'Qualified 30 characters Course name, unique, of course',
  `description` LONGTEXT NULL COMMENT 'The description of this course',
  `teacher_id` INT UNSIGNED NOT NULL COMMENT '(FK) The teacher who authored this course.',
  `is_active` TINYINT NULL DEFAULT 1,
  `created` DATETIME NOT NULL COMMENT 'Always set its value to <localtime>',
  PRIMARY KEY (`id`, `code`),
  UNIQUE INDEX `id_UNIQUE` (`id` ASC),
  UNIQUE INDEX `code_UNIQUE` (`code` ASC),
  INDEX `course_fk_user_id_idx` (`teacher_id` ASC),
  CONSTRAINT `course_fk_user_id`
    FOREIGN KEY (`teacher_id`)
    REFERENCES `u901322185_dbms`.`user` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `u901322185_dbms`.`sy`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `u901322185_dbms`.`sy` ;

CREATE TABLE IF NOT EXISTS `u901322185_dbms`.`sy` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `year` INT UNSIGNED NOT NULL,
  `description` MEDIUMTEXT NULL,
  `created` DATETIME NOT NULL,
  `is_default` TINYINT NOT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `id_UNIQUE` (`id` ASC),
  UNIQUE INDEX `year_UNIQUE` (`year` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `u901322185_dbms`.`studentprofile`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `u901322185_dbms`.`studentprofile` ;

CREATE TABLE IF NOT EXISTS `u901322185_dbms`.`studentprofile` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `student_id` INT UNSIGNED NOT NULL,
  `studyfield_id` INT UNSIGNED NOT NULL,
  UNIQUE INDEX `student_id_UNIQUE` (`student_id` ASC),
  PRIMARY KEY (`id`),
  UNIQUE INDEX `id_UNIQUE` (`id` ASC),
  CONSTRAINT `studentprofile_fk_user_id`
    FOREIGN KEY (`student_id`)
    REFERENCES `u901322185_dbms`.`user` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `u901322185_dbms`.`studyfield`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `u901322185_dbms`.`studyfield` ;

CREATE TABLE IF NOT EXISTS `u901322185_dbms`.`studyfield` (
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
-- Table `u901322185_dbms`.`gperiod`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `u901322185_dbms`.`gperiod` ;

CREATE TABLE IF NOT EXISTS `u901322185_dbms`.`gperiod` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(20) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `name_UNIQUE` (`name` ASC))
ENGINE = InnoDB
AUTO_INCREMENT = 1;


-- -----------------------------------------------------
-- Table `u901322185_dbms`.`d_course_gperiod`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `u901322185_dbms`.`d_course_gperiod` ;

CREATE TABLE IF NOT EXISTS `u901322185_dbms`.`d_course_gperiod` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `course_id` INT UNSIGNED NOT NULL,
  `gperiod_id` INT UNSIGNED NOT NULL,
  `sy_id` INT UNSIGNED NOT NULL,
  `notes` LONGTEXT NULL,
  `is_current` TINYINT NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  INDEX `fk_course_id_idx` (`course_id` ASC),
  INDEX `fk_gperiod_id_idx` (`gperiod_id` ASC),
  INDEX `fk_coursegperiod_sy_id_idx` (`sy_id` ASC),
  CONSTRAINT `fk_coursegperiod_course_id`
    FOREIGN KEY (`course_id`)
    REFERENCES `u901322185_dbms`.`course` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_coursegperiod_gperiod_id`
    FOREIGN KEY (`gperiod_id`)
    REFERENCES `u901322185_dbms`.`gperiod` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_coursegperiod_sy_id`
    FOREIGN KEY (`sy_id`)
    REFERENCES `u901322185_dbms`.`sy` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
AUTO_INCREMENT = 1
COMMENT = 'Grade computations';


-- -----------------------------------------------------
-- Table `u901322185_dbms`.`attendance`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `u901322185_dbms`.`attendance` ;

CREATE TABLE IF NOT EXISTS `u901322185_dbms`.`attendance` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `created` DATETIME NOT NULL,
  `course_id` INT UNSIGNED NOT NULL,
  `student_id` INT UNSIGNED NOT NULL,
  `period_id` INT UNSIGNED NOT NULL,
  `remark` INT NOT NULL COMMENT '[0] - Absent' /* comment truncated */ /*[1] - Present
[2] - Late*/,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `id_UNIQUE` (`id` ASC),
  INDEX `attendance_fk_course_id_idx` (`course_id` ASC),
  INDEX `attendance_fk_user_id_idx` (`student_id` ASC),
  INDEX `attendance_fk_gperiod_id_idx` (`period_id` ASC),
  CONSTRAINT `attendance_fk_course_id`
    FOREIGN KEY (`course_id`)
    REFERENCES `u901322185_dbms`.`course` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `attendance_fk_user_id`
    FOREIGN KEY (`student_id`)
    REFERENCES `u901322185_dbms`.`user` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `attendance_fk_period_id`
    FOREIGN KEY (`period_id`)
    REFERENCES `u901322185_dbms`.`d_course_gperiod` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
AUTO_INCREMENT = 1;


-- -----------------------------------------------------
-- Table `u901322185_dbms`.`profile`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `u901322185_dbms`.`profile` ;

CREATE TABLE IF NOT EXISTS `u901322185_dbms`.`profile` (
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
    REFERENCES `u901322185_dbms`.`user` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `u901322185_dbms`.`thread`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `u901322185_dbms`.`thread` ;

CREATE TABLE IF NOT EXISTS `u901322185_dbms`.`thread` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `course_id` INT UNSIGNED NOT NULL,
  `author_id` INT UNSIGNED NOT NULL,
  `created` DATETIME NOT NULL,
  `message` LONGTEXT NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_course_id_idx` (`course_id` ASC),
  INDEX `fk_user_id_idx` (`author_id` ASC),
  CONSTRAINT `fk_thread_course_id`
    FOREIGN KEY (`course_id`)
    REFERENCES `u901322185_dbms`.`course` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_thread_user_id`
    FOREIGN KEY (`author_id`)
    REFERENCES `u901322185_dbms`.`user` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `u901322185_dbms`.`threadcomment`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `u901322185_dbms`.`threadcomment` ;

CREATE TABLE IF NOT EXISTS `u901322185_dbms`.`threadcomment` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `thread_id` INT UNSIGNED NOT NULL,
  `author_id` INT UNSIGNED NOT NULL,
  `posteddate` DATE NOT NULL,
  `postedtime` TIME NOT NULL,
  `message` MEDIUMTEXT NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_thread_id_idx` (`thread_id` ASC),
  INDEX `fk_user_id_idx` (`author_id` ASC),
  CONSTRAINT `fk_threadcomment_thread_id`
    FOREIGN KEY (`thread_id`)
    REFERENCES `u901322185_dbms`.`thread` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_threadcomment_user_id`
    FOREIGN KEY (`author_id`)
    REFERENCES `u901322185_dbms`.`user` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `u901322185_dbms`.`coursesched`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `u901322185_dbms`.`coursesched` ;

CREATE TABLE IF NOT EXISTS `u901322185_dbms`.`coursesched` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `course_id` INT UNSIGNED NOT NULL,
  `day` DATE NOT NULL,
  `starttime` TIME NOT NULL,
  `endtime` TIME NOT NULL,
  `notes` LONGTEXT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_course_id_idx` (`course_id` ASC),
  CONSTRAINT `fk_coursesched_course_id`
    FOREIGN KEY (`course_id`)
    REFERENCES `u901322185_dbms`.`course` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `u901322185_dbms`.`task`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `u901322185_dbms`.`task` ;

CREATE TABLE IF NOT EXISTS `u901322185_dbms`.`task` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `course_id` INT UNSIGNED NOT NULL,
  `period_id` INT UNSIGNED NOT NULL,
  `sy_id` INT UNSIGNED NOT NULL,
  `title` MEDIUMTEXT NOT NULL,
  `message` LONGTEXT NOT NULL,
  `postdate` DATE NULL,
  `deaddate` DATE NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `fk_course_id_idx` (`course_id` ASC),
  INDEX `fk_period_id_idx` (`period_id` ASC),
  INDEX `fk_task_sy_id_idx` (`sy_id` ASC),
  CONSTRAINT `fk_task_course_id`
    FOREIGN KEY (`course_id`)
    REFERENCES `u901322185_dbms`.`course` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_task_period_id`
    FOREIGN KEY (`period_id`)
    REFERENCES `u901322185_dbms`.`d_course_gperiod` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_task_sy_id`
    FOREIGN KEY (`sy_id`)
    REFERENCES `u901322185_dbms`.`sy` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
AUTO_INCREMENT = 1;


-- -----------------------------------------------------
-- Table `u901322185_dbms`.`taskattachment`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `u901322185_dbms`.`taskattachment` ;

CREATE TABLE IF NOT EXISTS `u901322185_dbms`.`taskattachment` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `task_id` INT UNSIGNED NOT NULL,
  `user_id` INT UNSIGNED NOT NULL,
  `tokenvalue` VARCHAR(300) NOT NULL,
  `lastdown` DATETIME NOT NULL,
  `downcount` INT UNSIGNED NULL DEFAULT 0,
  PRIMARY KEY (`id`),
  INDEX `fk_user_id_idx` (`user_id` ASC),
  INDEX `fk_task_id_idx` (`task_id` ASC),
  CONSTRAINT `fk_taskattachment_user_id`
    FOREIGN KEY (`user_id`)
    REFERENCES `u901322185_dbms`.`user` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `fk_taskattachment_task_id`
    FOREIGN KEY (`task_id`)
    REFERENCES `u901322185_dbms`.`task` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
AUTO_INCREMENT = 1;


-- -----------------------------------------------------
-- Table `u901322185_dbms`.`d_student_course`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `u901322185_dbms`.`d_student_course` ;

CREATE TABLE IF NOT EXISTS `u901322185_dbms`.`d_student_course` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `student_id` INT UNSIGNED NOT NULL,
  `course_id` INT UNSIGNED NOT NULL,
  `sy_id` INT UNSIGNED NOT NULL,
  `status` INT UNSIGNED NOT NULL DEFAULT 2 COMMENT '[Default = 2]' /* comment truncated */ /*0 = Active
1 = Inactive
2 = Pending*/,
  `entry_created` DATETIME NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `dsc_course_id_idx` (`course_id` ASC),
  INDEX `dsc_student_id_idx` (`student_id` ASC),
  UNIQUE INDEX `id_UNIQUE` (`id` ASC),
  INDEX `dsc_sy_id_idx` (`sy_id` ASC),
  CONSTRAINT `dsc_course_id`
    FOREIGN KEY (`course_id`)
    REFERENCES `u901322185_dbms`.`course` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `dsc_student_id`
    FOREIGN KEY (`student_id`)
    REFERENCES `u901322185_dbms`.`user` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE,
  CONSTRAINT `dsc_sy_id`
    FOREIGN KEY (`sy_id`)
    REFERENCES `u901322185_dbms`.`sy` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
AUTO_INCREMENT = 1;


-- -----------------------------------------------------
-- Table `u901322185_dbms`.`notifications`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `u901322185_dbms`.`notifications` ;

CREATE TABLE IF NOT EXISTS `u901322185_dbms`.`notifications` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `type` INT NOT NULL,
  `datetime` DATETIME NOT NULL,
  `text` LONGTEXT NOT NULL,
  `targetrelpath` LONGTEXT NULL,
  `course_id` INT UNSIGNED NULL COMMENT 'If defined, it means, this notification is defined for certain course too.',
  PRIMARY KEY (`id`),
  INDEX `fk_notifications_course_Id_idx` (`course_id` ASC),
  CONSTRAINT `fk_notifications_course_Id`
    FOREIGN KEY (`course_id`)
    REFERENCES `u901322185_dbms`.`course` (`id`)
    ON DELETE CASCADE
    ON UPDATE CASCADE)
ENGINE = InnoDB
AUTO_INCREMENT = 1;


-- -----------------------------------------------------
-- Table `u901322185_dbms`.`notifications_student`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `u901322185_dbms`.`notifications_student` ;

CREATE TABLE IF NOT EXISTS `u901322185_dbms`.`notifications_student` (
  `id` INT UNSIGNED NOT NULL AUTO_INCREMENT,
  `type` INT UNSIGNED NOT NULL,
  `datetime` DATETIME NOT NULL,
  `text` LONGTEXT NOT NULL,
  `targetrelpath` LONGTEXT NULL,
  `course_id` INT UNSIGNED NULL,
  `student_id` INT UNSIGNED NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `id_UNIQUE` (`id` ASC),
  INDEX `ns_course_id_idx` (`course_id` ASC),
  INDEX `ns_student_id_idx` (`student_id` ASC),
  CONSTRAINT `ns_course_id`
    FOREIGN KEY (`course_id`)
    REFERENCES `u901322185_dbms`.`course` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `ns_student_id`
    FOREIGN KEY (`student_id`)
    REFERENCES `u901322185_dbms`.`user` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB
AUTO_INCREMENT = 1;

USE `thegenerals` ;

-- -----------------------------------------------------
-- Table `thegenerals`.`challenge`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `thegenerals`.`challenge` ;

CREATE TABLE IF NOT EXISTS `thegenerals`.`challenge` (
  `ChallengeID` INT(11) NOT NULL AUTO_INCREMENT,
  `InitiatorID` INT(11) NOT NULL,
  `RecipientID` INT(11) NOT NULL,
  `Action` INT(11) NOT NULL COMMENT '0: Pending, 1:Accepted, -1: Rejected, -2: Cancelled',
  PRIMARY KEY (`ChallengeID`))
ENGINE = InnoDB
AUTO_INCREMENT = 139
DEFAULT CHARACTER SET = latin1;


-- -----------------------------------------------------
-- Table `thegenerals`.`chat`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `thegenerals`.`chat` ;

CREATE TABLE IF NOT EXISTS `thegenerals`.`chat` (
  `ChatID` INT(11) NOT NULL AUTO_INCREMENT,
  `SenderID` INT(11) NOT NULL COMMENT 'if 0: Is System Message',
  `ReceiverID` INT(11) NOT NULL COMMENT 'if 0: Is for Lobby',
  `Message` TEXT NOT NULL,
  `IsSystemMessage` TINYINT(1) NOT NULL COMMENT 'Used if ReceiverID is != 0',
  `Time` DATETIME NOT NULL,
  PRIMARY KEY (`ChatID`))
ENGINE = InnoDB
AUTO_INCREMENT = 56
DEFAULT CHARACTER SET = latin1;


-- -----------------------------------------------------
-- Table `thegenerals`.`flag`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `thegenerals`.`flag` ;

CREATE TABLE IF NOT EXISTS `thegenerals`.`flag` (
  `FlagID` INT(11) NOT NULL AUTO_INCREMENT,
  `Name` TEXT NOT NULL,
  PRIMARY KEY (`FlagID`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = latin1;


-- -----------------------------------------------------
-- Table `thegenerals`.`formation`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `thegenerals`.`formation` ;

CREATE TABLE IF NOT EXISTS `thegenerals`.`formation` (
  `FormationID` INT(11) NOT NULL AUTO_INCREMENT,
  `Board` TEXT NOT NULL,
  `UserID` INT(11) NOT NULL COMMENT 'if 0: System Default',
  `Name` TEXT NOT NULL,
  PRIMARY KEY (`FormationID`))
ENGINE = InnoDB
DEFAULT CHARACTER SET = latin1;


-- -----------------------------------------------------
-- Table `thegenerals`.`match`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `thegenerals`.`match` ;

CREATE TABLE IF NOT EXISTS `thegenerals`.`match` (
  `MatchID` INT(11) NOT NULL AUTO_INCREMENT,
  `IsActive` TINYINT(1) NOT NULL,
  `FirstPlayerID` INT(11) NOT NULL,
  `SecondPlayerID` INT(11) NOT NULL,
  `WinnerID` INT(11) NOT NULL,
  `Turn` INT(11) NOT NULL COMMENT '-1: Setup, 0: 1st player, 1: 2nd player',
  `Outcome` INT(11) NOT NULL COMMENT '0: nothing yet, 1: Won by capture, 2: Won by Goal, 3 Won by Time, -1: Won by resign; -2 Show Flag,',
  PRIMARY KEY (`MatchID`))
ENGINE = InnoDB
AUTO_INCREMENT = 118
DEFAULT CHARACTER SET = latin1;


-- -----------------------------------------------------
-- Table `thegenerals`.`matchhistory`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `thegenerals`.`matchhistory` ;

CREATE TABLE IF NOT EXISTS `thegenerals`.`matchhistory` (
  `MatchHistoryID` INT(11) NOT NULL AUTO_INCREMENT,
  `MatchID` INT(11) NOT NULL,
  `Board` VARCHAR(80) NOT NULL DEFAULT '000000000/000000000/000000000/000000000/000000000/000000000/000000000/000000000',
  `FirstPlayerPoint` INT(11) NOT NULL DEFAULT '-1' COMMENT '0: ready, -1: setup, default: score',
  `SecondPlayerPoint` INT(11) NOT NULL DEFAULT '-1' COMMENT '0: ready, -1: setup, default: score',
  `FirstPlayerLostPieces` VARCHAR(21) NOT NULL,
  `SecondPlayerLostPieces` VARCHAR(21) NOT NULL,
  `TimeUpdated` DATETIME NOT NULL,
  `TurnUserID` INT(11) NOT NULL,
  `TimeRemaining` TIME NOT NULL,
  PRIMARY KEY (`MatchHistoryID`))
ENGINE = InnoDB
AUTO_INCREMENT = 2508
DEFAULT CHARACTER SET = latin1;


-- -----------------------------------------------------
-- Table `thegenerals`.`rank`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `thegenerals`.`rank` ;

CREATE TABLE IF NOT EXISTS `thegenerals`.`rank` (
  `RankID` INT(11) NOT NULL AUTO_INCREMENT,
  `ExperienceRequired` INT(11) NOT NULL,
  `Name` TEXT NOT NULL,
  PRIMARY KEY (`RankID`))
ENGINE = InnoDB
AUTO_INCREMENT = 2
DEFAULT CHARACTER SET = latin1;


-- -----------------------------------------------------
-- Table `thegenerals`.`user`
-- -----------------------------------------------------
DROP TABLE IF EXISTS `thegenerals`.`user` ;

CREATE TABLE IF NOT EXISTS `thegenerals`.`user` (
  `UserID` INT(11) NOT NULL AUTO_INCREMENT,
  `Username` TEXT NOT NULL,
  `Password` TEXT NOT NULL,
  `Email` TEXT NOT NULL,
  `LastUpdated` DATETIME NOT NULL COMMENT 'if not updated within 30 sec, disconnected',
  `CurrentMatchID` INT(11) NOT NULL COMMENT 'if 0: not playing',
  `Comrade` TEXT NOT NULL COMMENT 'Formated as JSON [0,1,2,5]',
  `PendingComrade` TEXT NOT NULL COMMENT 'Formated as JSON [0,1,2,5]',
  `Win` INT(11) NOT NULL,
  `Lose` INT(11) NOT NULL,
  `Streak` INT(11) NOT NULL,
  `HighestStreak` INT(11) NOT NULL,
  `Experience` INT(11) NOT NULL,
  `Rank` INT(11) NOT NULL,
  `Point` INT(11) NOT NULL,
  `ThemeID` INT(11) NOT NULL,
  `IsGuest` TINYINT(1) NOT NULL,
  `FlagID` INT(11) NOT NULL COMMENT 'if 0: Custom Flag, use User\'s ID',
  `IsGameModerator` TINYINT(1) NOT NULL,
  `Latency` INT(11) NOT NULL,
  PRIMARY KEY (`UserID`))
ENGINE = InnoDB
AUTO_INCREMENT = 172
DEFAULT CHARACTER SET = latin1;

USE `u901322185_dbms` ;

-- -----------------------------------------------------
-- Placeholder table for view `u901322185_dbms`.`v_notifications`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `u901322185_dbms`.`v_notifications` (`id` INT, `type` INT, `datetime` INT, `text` INT, `targetrelpath` INT, `course_id` INT);

-- -----------------------------------------------------
-- Placeholder table for view `u901322185_dbms`.`v_notifications_student`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `u901322185_dbms`.`v_notifications_student` (`id` INT, `type` INT, `datetime` INT, `text` INT, `targetrelpath` INT, `course_id` INT, `student_id` INT);

-- -----------------------------------------------------
-- procedure addStudentCourseEntry
-- -----------------------------------------------------

USE `u901322185_dbms`;
DROP procedure IF EXISTS `u901322185_dbms`.`addStudentCourseEntry`;

DELIMITER $$
USE `u901322185_dbms`$$
CREATE PROCEDURE `addStudentCourseEntry`(
	IN studentID INT,
	IN courseID INT)
BEGIN
	DECLARE currentsy_id INTEGER;
	SELECT id 
		INTO currentsy_id
		FROM sy 
		WHERE is_default = 1 
		LIMIT 1;
	INSERT INTO `d_student_course`(student_id, course_id, entry_created, sy_id)
	VALUES(studentID, courseID, localtime(), currentsy_id);
END
$$

DELIMITER ;

-- -----------------------------------------------------
-- procedure banStudentCourseEntry
-- -----------------------------------------------------

USE `u901322185_dbms`;
DROP procedure IF EXISTS `u901322185_dbms`.`banStudentCourseEntry`;

DELIMITER $$
USE `u901322185_dbms`$$


CREATE PROCEDURE `banStudentCourseEntry`(
	IN studentID INT,
	IN courseID INT)
BEGIN
	DECLARE currentsy_id INTEGER;
	SELECT id 
		INTO currentsy_id
		FROM sy 
		WHERE is_default = 1 
		LIMIT 1;
	UPDATE `d_student_course`
	SET `status` = 1
	WHERE `student_id` = studentID
		AND `course_id` = courseID
		AND `sy_id` = currentsy_id;
END
$$

DELIMITER ;

-- -----------------------------------------------------
-- procedure deleteStudentCourseEntry
-- -----------------------------------------------------

USE `u901322185_dbms`;
DROP procedure IF EXISTS `u901322185_dbms`.`deleteStudentCourseEntry`;

DELIMITER $$
USE `u901322185_dbms`$$


CREATE PROCEDURE `deleteStudentCourseEntry`(
	IN studentID INT,
	IN courseID INT)
BEGIN
	DECLARE currentsy_id INTEGER;
	SELECT id 
		INTO currentsy_id
		FROM sy 
		WHERE is_default = 1 
		LIMIT 1;
	DELETE FROM `d_student_course`
	WHERE `student_id` = studentID
		AND `course_id` = courseID
		AND `sy_id` = currentsy_id;
END
$$

DELIMITER ;

-- -----------------------------------------------------
-- View `u901322185_dbms`.`v_notifications`
-- -----------------------------------------------------
DROP VIEW IF EXISTS `u901322185_dbms`.`v_notifications` ;
DROP TABLE IF EXISTS `u901322185_dbms`.`v_notifications`;
USE `u901322185_dbms`;
CREATE  OR REPLACE VIEW `v_notifications` AS
SELECT id, type, date_format(datetime, '%l:%i%p &centerdot; %e %b %Y') as datetime, text, targetrelpath, course_id
FROM notifications;

-- -----------------------------------------------------
-- View `u901322185_dbms`.`v_notifications_student`
-- -----------------------------------------------------
DROP VIEW IF EXISTS `u901322185_dbms`.`v_notifications_student` ;
DROP TABLE IF EXISTS `u901322185_dbms`.`v_notifications_student`;
USE `u901322185_dbms`;
CREATE  OR REPLACE VIEW `v_notifications_student` AS
SELECT id, type, date_format(datetime, '%l:%i%p &centerdot; %e %b %Y') as datetime, text, targetrelpath, course_id, student_id
FROM notifications_student;

SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;

-- -----------------------------------------------------
-- Data for table `u901322185_dbms`.`userpower`
-- -----------------------------------------------------
START TRANSACTION;
USE `u901322185_dbms`;
INSERT INTO `u901322185_dbms`.`userpower` (`id`, `label`, `description`) VALUES (0, 'Admin', 'The system admin that manages every existing resource in the system.');
INSERT INTO `u901322185_dbms`.`userpower` (`id`, `label`, `description`) VALUES (1, 'Instructor', 'Manages respective courses and student entries/records.');
INSERT INTO `u901322185_dbms`.`userpower` (`id`, `label`, `description`) VALUES (2, 'Student', 'Account for students availing course enrollments in the system.');

COMMIT;


-- -----------------------------------------------------
-- Data for table `u901322185_dbms`.`gperiod`
-- -----------------------------------------------------
START TRANSACTION;
USE `u901322185_dbms`;
INSERT INTO `u901322185_dbms`.`gperiod` (`id`, `name`) VALUES (1, 'Prelim');
INSERT INTO `u901322185_dbms`.`gperiod` (`id`, `name`) VALUES (2, 'Midterm');
INSERT INTO `u901322185_dbms`.`gperiod` (`id`, `name`) VALUES (3, 'Semi-finals');
INSERT INTO `u901322185_dbms`.`gperiod` (`id`, `name`) VALUES (4, 'Finals');

COMMIT;

USE `u901322185_dbms`;

DELIMITER $$

USE `u901322185_dbms`$$
DROP TRIGGER IF EXISTS `u901322185_dbms`.`THREAD_AI` $$
USE `u901322185_dbms`$$
CREATE TRIGGER `THREAD_AI` AFTER INSERT ON `thread` FOR EACH ROW
BEGIN
	DECLARE fullname VARCHAR(100);

	DECLARE id INTEGER;
	DECLARE course_id INTEGER;


	SET id = NEW.author_id;
	SET course_id = NEW.course_id;
	
	-- Storing user's fullname
	SELECT concat(profile.fname, ' ', profile.lname) AS name
	INTO fullname
	FROM `profile`
	WHERE `profile`.`user_id` = id;

	INSERT INTO `notifications`(type, datetime, text, targetrelpath, course_id)
	VALUES(
		0, -- messageboard post
		localtime(),
		concat('<a href="?page=user-profile&USER_ID=', NEW.author_id, '"><b><img src="web+/site/img/user.png">', fullname, '</b></a> posted in message board'),
		'?page=user-courses-messageboard',
		course_id
		);

END;$$


USE `u901322185_dbms`$$
DROP TRIGGER IF EXISTS `u901322185_dbms`.`thread_au` $$
USE `u901322185_dbms`$$
CREATE TRIGGER `thread_au` AFTER UPDATE ON `thread` FOR EACH ROW
BEGIN

END;$$


USE `u901322185_dbms`$$
DROP TRIGGER IF EXISTS `u901322185_dbms`.`d_student_course_ai` $$
USE `u901322185_dbms`$$
CREATE TRIGGER `d_student_course_ai` AFTER INSERT ON `d_student_course` FOR EACH ROW
BEGIN
	DECLARE course_id INTEGER;
	DECLARE student_id INTEGER;
	DECLARE fullname VARCHAR(100);
	DECLARE coursename VARCHAR(100);

	SET course_id = NEW.course_id;
	SET student_id = NEW.student_id;

	SELECT concat(profile.fname, ' ', profile.lname) AS fullname
	INTO fullname
	FROM `profile`
	WHERE `profile`.`user_id` = student_id;

	SELECT name
	INTO coursename
	FROM `course`
	WHERE `course`.`id` = course_id;


	INSERT INTO notifications(type, datetime, text, targetrelpath, course_id)
	VALUES(
		2
	  , localtime()
	  , concat('<a href="?page=user-profile&USER_ID=', student_id, '"><b><img src="">', fullname, '</b></a> asked to be enrolled in ', coursename)
	  , concat('?page=user-courses-home&COURSE_ID=', course_id, '&REDIRECT_TO=instructor-courses-home-spending')
	  , course_id
	);
END;
$$


USE `u901322185_dbms`$$
DROP TRIGGER IF EXISTS `u901322185_dbms`.`d_student_course_au` $$
USE `u901322185_dbms`$$
CREATE TRIGGER `d_student_course_au` AFTER UPDATE ON `d_student_course` FOR EACH ROW
BEGIN
	DECLARE coursename VARCHAR(100) DEFAULT 'NO_COURSE';

	DECLARE actiondone VARCHAR(20);
	DECLARE actiontype INTEGER;
	DECLARE notificationtext VARCHAR(150);

	IF NEW.status = 0 THEN
		SET actiontype = 7;
		SET actiondone = 'accepted';

		SELECT `name`
		INTO coursename
		FROM `course`
		WHERE `course`.`id`=NEW.course_id;

		-- prepare Notification message
		SET notificationtext = concat('Your enrollment entry in <a href="?page=user-courses-home&COURSE_ID=', NEW.course_id, '"><b>', coursename, '</b></a> has been accepted');
		-- check if this student was just unblocked
		IF OLD.status = 1 THEN
			SET notificationtext = concat('You are now unblocked from course <a href="?page=user-courses-home&COURSE_ID=', NEW.course_id, '"><b>', coursename, '</b></a>');
		END IF;


		INSERT INTO `notifications_student`(type, datetime, text, targetrelpath, course_id, student_id)
		VALUES(
			actiontype
		  , localtime()
		  , notificationtext
		  , concat('?page=user-courses-home&COURSE_ID=', NEW.course_id)
		  , NEW.course_id
		  , NEW.student_id
		);
	END IF;
END;$$


USE `u901322185_dbms`$$
DROP TRIGGER IF EXISTS `u901322185_dbms`.`d_student_course_ad` $$
USE `u901322185_dbms`$$
CREATE TRIGGER `d_student_course_ad` AFTER DELETE ON `d_student_course` FOR EACH ROW
BEGIN
	DECLARE coursename VARCHAR(100);
	DECLARE notifmessage VARCHAR(150);

	SELECT name
	INTO coursename
	FROM `course`
	WHERE `course`.`id` = OLD.course_id;

	-- prepare notification message
	SET notifmessage = concat('Your entry in ', coursename, ' was dropped out.');
	IF OLD.status = 0 THEN
		SET notifmessage = concat('Your enrollment request in ', coursename, ' has been denied.');
	END IF;

	INSERT INTO `dbass`.`notifications_student` (`type`, `datetime`, `text`, `targetrelpath`, `course_id`, `student_id`)
	VALUES (
		8
	  , localtime()
	  , notifmessage
	  , NULL
	  , OLD.course_id
	  , OLD.student_id);
END;$$


DELIMITER ;

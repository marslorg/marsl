SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL';

-- -----------------------------------------------------
-- Table `user_album`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `user_album` (
  `album` INT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(100) NOT NULL ,
  `user` INT NOT NULL ,
  `description` LONGTEXT NULL ,
  `folder` VARCHAR(100) NOT NULL ,
  `visible` TINYINT(1) NOT NULL ,
  `deleted` TINYINT(1) NOT NULL ,
  `date` INT NOT NULL ,
  PRIMARY KEY (`album`) ,
  INDEX `user_album` (`user` ASC) )
ENGINE = MyISAM;


-- -----------------------------------------------------
-- Table `user_picture`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `user_picture` (
  `picture` BIGINT NOT NULL AUTO_INCREMENT ,
  `album` INT NOT NULL ,
  `subtitle` TEXT NULL ,
  `filename` VARCHAR(100) NOT NULL ,
  `deleted` TINYINT(1) NOT NULL ,
  PRIMARY KEY (`picture`) ,
  INDEX `user_picture_album` (`album` ASC) )
ENGINE = MyISAM;


-- -----------------------------------------------------
-- Table `role`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `role` (
  `role` INT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(50) NOT NULL ,
  PRIMARY KEY (`role`) ,
  UNIQUE INDEX `UNIQUE` (`name` ASC) )
ENGINE = MyISAM;


-- -----------------------------------------------------
-- Table `user`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `user` (
  `user` INT NOT NULL AUTO_INCREMENT ,
  `nickname` VARCHAR(50) NOT NULL ,
  `password` CHAR(128) NOT NULL ,
  `prename` VARCHAR(50) NULL ,
  `name` VARCHAR(50) NULL ,
  `postcount` INT NOT NULL ,
  `info` LONGTEXT NULL ,
  `regdate` INT NOT NULL ,
  `lastlogin` INT NULL ,
  `lastlogout` INT NULL ,
  `signature` LONGTEXT NULL ,
  `birthdate` INT NULL ,
  `sessionid` CHAR(128) NOT NULL ,
  `lastseen` INT NULL ,
  `gender` VARCHAR(6) NULL ,
  `interests` LONGTEXT NULL ,
  `job` VARCHAR(100) NULL ,
  `zip` INT NULL ,
  `street` VARCHAR(100) NULL ,
  `house` VARCHAR(100) NULL ,
  `picture` BIGINT NULL ,
  `deleted` TINYINT(1) NOT NULL ,
  `role` INT NOT NULL ,
  `city` VARCHAR(100) NULL ,
  `acronym` VARCHAR(50) NULL ,
  PRIMARY KEY (`user`) ,
  UNIQUE INDEX `UNIQUE` (`nickname` ASC, `sessionid` ASC, `acronym` ASC) ,
  INDEX `user_picture` (`picture` ASC) ,
  INDEX `user_role` (`role` ASC) )
ENGINE = MyISAM;


-- -----------------------------------------------------
-- Table `email`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `email` (
  `email` VARCHAR(100) NOT NULL ,
  `user` INT NOT NULL ,
  `confirmed` TINYINT(1) NOT NULL DEFAULT FALSE ,
  `time` INT NOT NULL ,
  `confirm_id` CHAR(32) NOT NULL ,
  PRIMARY KEY (`email`) ,
  INDEX `user_email` (`user` ASC) )
ENGINE = MyISAM;


-- -----------------------------------------------------
-- Table `contact_form`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `contact_form` (
  `contact_form` VARCHAR(100) NOT NULL ,
  `structure` VARCHAR(100) NOT NULL ,
  PRIMARY KEY (`contact_form`) )
ENGINE = MyISAM;


-- -----------------------------------------------------
-- Table `contact`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `contact` (
  `contact` VARCHAR(100) NOT NULL ,
  `contact_form` VARCHAR(100) NOT NULL ,
  `user` INT NOT NULL ,
  PRIMARY KEY (`contact`, `contact_form`) ,
  INDEX `user_contact` (`user` ASC) ,
  INDEX `contact_form` (`contact_form` ASC) )
ENGINE = MyISAM;


-- -----------------------------------------------------
-- Table `news_picture`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `news_picture` (
  `picture` INT NOT NULL AUTO_INCREMENT ,
  `url` VARCHAR(100) NOT NULL ,
  `subtitle` TEXT NULL ,
  `photograph` VARCHAR(100) NULL ,
  PRIMARY KEY (`picture`) )
ENGINE = MyISAM;


-- -----------------------------------------------------
-- Table `news`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `news` (
  `news` INT NOT NULL AUTO_INCREMENT ,
  `author` INT NOT NULL ,
  `author_ip` VARCHAR(100) NOT NULL ,
  `admin` INT NULL ,
  `admin_ip` VARCHAR(100) NULL ,
  `headline` TINYTEXT NULL ,
  `title` TINYTEXT NULL ,
  `teaser` LONGTEXT NULL ,
  `text` LONGTEXT NULL ,
  `picture1` INT NULL ,
  `picture2` INT NULL ,
  `date` INT NOT NULL ,
  `visible` TINYINT(1) NOT NULL DEFAULT false ,
  `deleted` TINYINT(1) NOT NULL DEFAULT false ,
  `location` INT NOT NULL ,
  `city` VARCHAR(100) NOT NULL ,
  `postdate` INT NOT NULL ,
  `expire` INT NULL ,
  `featured` TINYINT(1) NOT NULL DEFAULT false ,
  PRIMARY KEY (`news`) ,
  INDEX `author_user` (`author` ASC) ,
  INDEX `admin_user` (`admin` ASC) ,
  INDEX `teaser_picture` (`picture1` ASC) ,
  INDEX `news_picture` (`picture2` ASC) )
ENGINE = MyISAM;


-- -----------------------------------------------------
-- Table `navigation`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `navigation` (
  `id` INT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(100) NOT NULL ,
  `type` INT NOT NULL ,
  `category` INT NULL ,
  `head` LONGTEXT NULL ,
  `module` VARCHAR(100) NULL ,
  `foot` LONGTEXT NULL ,
  `pos` INT NOT NULL DEFAULT 0 ,
  `maps_to` INT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `link_category` (`category` ASC) ,
  INDEX `link_map` (`maps_to` ASC) )
ENGINE = MyISAM;


-- -----------------------------------------------------
-- Table `album`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `album` (
  `album` INT NOT NULL AUTO_INCREMENT ,
  `name` VARCHAR(100) NOT NULL ,
  `author` INT NOT NULL ,
  `author_ip` VARCHAR(100) NOT NULL ,
  `admin` INT NULL ,
  `admin_ip` VARCHAR(100) NULL ,
  `photograph` VARCHAR(100) NULL ,
  `description` LONGTEXT NULL ,
  `folder` VARCHAR(100) NOT NULL ,
  `visible` TINYINT(1) NOT NULL ,
  `deleted` TINYINT(1) NOT NULL ,
  `date` INT NOT NULL ,
  `postdate` INT NOT NULL ,
  `location` INT NOT NULL ,
  PRIMARY KEY (`album`) ,
  INDEX `album_author` (`author` ASC) ,
  INDEX `album_admin` (`admin` ASC) ,
  INDEX `album_location` (`location` ASC) )
ENGINE = MyISAM;


-- -----------------------------------------------------
-- Table `picture`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `picture` (
  `picture` BIGINT NOT NULL AUTO_INCREMENT ,
  `album` INT NOT NULL ,
  `subtitle` TEXT NULL ,
  `filename` VARCHAR(100) NOT NULL ,
  `deleted` TINYINT(1) NOT NULL ,
  `visible` TINYINT(1) NOT NULL ,
  PRIMARY KEY (`picture`) ,
  INDEX `picture_album` (`album` ASC) )
ENGINE = MyISAM;


-- -----------------------------------------------------
-- Table `rights`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `rights` (
  `role` INT NOT NULL ,
  `location` INT NOT NULL ,
  `read` TINYINT(1) NOT NULL DEFAULT false ,
  `write` TINYINT(1) NOT NULL DEFAULT false ,
  `extended` TINYINT(1) NOT NULL DEFAULT false ,
  `admin` TINYINT(1) NOT NULL DEFAULT false ,
  PRIMARY KEY (`role`, `location`) ,
  INDEX `rights_role` (`role` ASC) ,
  INDEX `rights_navigation` (`location` ASC) )
ENGINE = MyISAM;


-- -----------------------------------------------------
-- Table `registration_tos`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `registration_tos` (
  `id` INT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `tos_navigation` (`id` ASC) )
ENGINE = MyISAM;


-- -----------------------------------------------------
-- Table `module`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `module` (
  `name` VARCHAR(100) NOT NULL ,
  `file` VARCHAR(100) NOT NULL ,
  `class` VARCHAR(100) NOT NULL ,
  PRIMARY KEY (`name`) )
ENGINE = MyISAM;


-- -----------------------------------------------------
-- Table `rights_module`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `rights_module` (
  `role` INT NOT NULL ,
  `module` VARCHAR(100) NOT NULL ,
  `read` TINYINT(1) NOT NULL DEFAULT false ,
  `write` TINYINT(1) NOT NULL DEFAULT false ,
  `extended` TINYINT(1) NOT NULL DEFAULT false ,
  `admin` TINYINT(1) NOT NULL DEFAULT false ,
  PRIMARY KEY (`role`, `module`) ,
  INDEX `rights_role` (`role` ASC) ,
  INDEX `rights_module` (`module` ASC) )
ENGINE = MyISAM;


-- -----------------------------------------------------
-- Table `role_editor`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `role_editor` (
  `master` INT NOT NULL ,
  `slave` INT NOT NULL ,
  PRIMARY KEY (`master`, `slave`) ,
  INDEX `master.role` (`master` ASC) ,
  INDEX `slave.role` (`slave` ASC) )
ENGINE = MyISAM;


-- -----------------------------------------------------
-- Table `stdroles`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `stdroles` (
  `guest` INT NOT NULL ,
  `user` INT NOT NULL ,
  INDEX `guest` (`guest` ASC) ,
  INDEX `user` (`user` ASC) )
ENGINE = MyISAM;


-- -----------------------------------------------------
-- Table `homepage`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `homepage` (
  `homepage` INT NOT NULL ,
  PRIMARY KEY (`homepage`) ,
  INDEX `standard_link` (`homepage` ASC) )
ENGINE = MyISAM;


-- -----------------------------------------------------
-- Table `board`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `board` (
  `board` INT NOT NULL AUTO_INCREMENT ,
  `pos` INT NOT NULL DEFAULT 0 ,
  `description` LONGTEXT NULL ,
  `title` TINYTEXT NOT NULL ,
  `type` INT NOT NULL DEFAULT 0 ,
  `location` INT NOT NULL ,
  `threadcount` INT NOT NULL DEFAULT 0 ,
  `postcount` INT NOT NULL DEFAULT 0 ,
  PRIMARY KEY (`board`) ,
  INDEX `board_location` (`location` ASC) )
ENGINE = MyISAM;


-- -----------------------------------------------------
-- Table `rights_board`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `rights_board` (
  `role` INT NOT NULL ,
  `board` INT NOT NULL ,
  `read` TINYINT(1) NOT NULL ,
  `write` TINYINT(1) NOT NULL ,
  `extended` TINYINT(1) NOT NULL ,
  `admin` TINYINT(1) NOT NULL ,
  PRIMARY KEY (`role`, `board`) ,
  INDEX `board_role` (`role` ASC) ,
  INDEX `role_board` (`board` ASC) )
ENGINE = MyISAM;


-- -----------------------------------------------------
-- Table `board_operator`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `board_operator` (
  `board` INT NOT NULL ,
  `user` INT NOT NULL ,
  PRIMARY KEY (`board`, `user`) ,
  INDEX `operator_board` (`board` ASC) ,
  INDEX `operator_user` (`user` ASC) )
ENGINE = MyISAM;


-- -----------------------------------------------------
-- Table `post`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `post` (
  `post` INT NOT NULL AUTO_INCREMENT ,
  `author` INT NOT NULL ,
  `thread` INT NOT NULL ,
  `date` INT NOT NULL ,
  `operator` INT NULL ,
  `lastedit` INT NULL ,
  `content` LONGTEXT NULL ,
  `ip` VARCHAR(100) NOT NULL ,
  `deleted` TINYINT(1) NOT NULL ,
  PRIMARY KEY (`post`) ,
  INDEX `post_author` (`author` ASC) ,
  INDEX `post_thread` (`thread` ASC) ,
  INDEX `post_operator` (`operator` ASC) )
ENGINE = MyISAM;


-- -----------------------------------------------------
-- Table `thread`
-- -----------------------------------------------------
CREATE  TABLE IF NOT EXISTS `thread` (
  `thread` INT NOT NULL AUTO_INCREMENT ,
  `board` INT NOT NULL ,
  `postcount` INT NOT NULL DEFAULT 0 ,
  `type` INT NOT NULL ,
  `title` TINYTEXT NULL ,
  `author` INT NOT NULL ,
  `viewcount` INT NOT NULL DEFAULT 0 ,
  `lastpost` INT NOT NULL ,
  PRIMARY KEY (`thread`) ,
  INDEX `thread_board` (`board` ASC) ,
  INDEX `thread_author` (`author` ASC) ,
  INDEX `thread_post` (`lastpost` ASC) )
ENGINE = MyISAM;



SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;

-- MySQL Script generated by MySQL Workbench
-- Tue Apr  6 23:42:05 2021
-- Model: New Model    Version: 1.0
-- MySQL Workbench Forward Engineering

SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

-- -----------------------------------------------------
-- Table `user_album`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `user_album` (
  `album` BIGINT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `user` BIGINT NOT NULL,
  `description` LONGTEXT NULL,
  `folder` VARCHAR(100) NOT NULL,
  `visible` TINYINT(1) NOT NULL,
  `deleted` TINYINT(1) NOT NULL,
  `date` INT NOT NULL,
  PRIMARY KEY (`album`),
  INDEX `user_album_idx` (`user` ASC),
  CONSTRAINT `user_album`
    FOREIGN KEY (`user`)
    REFERENCES `user` (`user`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `user_picture`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `user_picture` (
  `picture` BIGINT NOT NULL AUTO_INCREMENT,
  `album` BIGINT NOT NULL,
  `subtitle` TEXT NULL,
  `filename` VARCHAR(100) NOT NULL,
  `deleted` TINYINT(1) NOT NULL,
  PRIMARY KEY (`picture`),
  INDEX `user_picture_album_idx` (`album` ASC),
  CONSTRAINT `user_picture_album`
    FOREIGN KEY (`album`)
    REFERENCES `user_album` (`album`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `role`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `role` (
  `role` BIGINT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(50) NOT NULL,
  PRIMARY KEY (`role`),
  UNIQUE INDEX `UNIQUE` (`name` ASC),
  INDEX `role_idx` (`role` ASC),
  INDEX `name_idx` (`name` ASC),
  INDEX `role_name_idx` (`role` ASC, `name` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `user`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `user` (
  `user` BIGINT NOT NULL AUTO_INCREMENT,
  `nickname` VARCHAR(50) NOT NULL,
  `password` CHAR(128) NOT NULL,
  `prename` VARCHAR(50) NULL,
  `name` VARCHAR(50) NULL,
  `postcount` INT NOT NULL,
  `info` LONGTEXT NULL,
  `regdate` INT NOT NULL,
  `lastlogin` INT NULL,
  `lastlogout` INT NULL,
  `signature` LONGTEXT NULL,
  `birthdate` INT NULL,
  `sessionid` CHAR(128) NOT NULL,
  `lastseen` INT NULL,
  `gender` VARCHAR(6) NULL,
  `interests` LONGTEXT NULL,
  `job` VARCHAR(100) NULL,
  `zip` INT NULL,
  `street` VARCHAR(100) NULL,
  `house` VARCHAR(100) NULL,
  `picture` BIGINT NULL,
  `deleted` TINYINT(1) NOT NULL,
  `role` BIGINT NOT NULL,
  `city` VARCHAR(100) NULL,
  `acronym` VARCHAR(50) NULL,
  PRIMARY KEY (`user`),
  UNIQUE INDEX `UNIQUE` (`nickname` ASC, `sessionid` ASC, `acronym` ASC),
  INDEX `user_picture_idx` (`picture` ASC),
  INDEX `user_role_idx` (`role` ASC),
  INDEX `sessionid_idx` (`sessionid` ASC),
  INDEX `role_user_idx` (`user` ASC, `role` ASC),
  INDEX `nickname_idx` (`nickname` ASC),
  INDEX `user_role_nickname_idx` (`user` ASC, `role` ASC, `nickname` ASC),
  INDEX `user_idx` (`user` ASC),
  INDEX `deleted_idx` (`deleted` ASC),
  INDEX `session_deleted_idx` (`sessionid` ASC, `deleted` ASC),
  INDEX `role_session_deleted_idx` (`role` ASC, `sessionid` ASC, `deleted` ASC),
  INDEX `user_deleted_idx` (`user` ASC, `deleted` ASC),
  INDEX `role_user_deleted_idx` (`role` ASC, `deleted` ASC),
  INDEX `password_idx` (`password` ASC),
  INDEX `nickname_password_idx` (`nickname` ASC, `password` ASC),
  INDEX `acronym_idx` (`acronym` ASC),
  INDEX `acronym_user_idx` (`acronym` ASC, `user` ASC),
  INDEX `nickname_user_idx` (`nickname` ASC, `user` ASC),
  INDEX `role_deleted_idx` (`role` ASC, `deleted` ASC),
  CONSTRAINT `user_picture`
    FOREIGN KEY (`picture`)
    REFERENCES `user_picture` (`picture`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `user_role`
    FOREIGN KEY (`role`)
    REFERENCES `role` (`role`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `email`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `email` (
  `email` VARCHAR(100) NOT NULL,
  `user` BIGINT NULL,
  `confirmed` TINYINT(1) NOT NULL DEFAULT FALSE,
  `time` INT NOT NULL,
  `confirm_id` CHAR(32) NOT NULL,
  `primary` TINYINT(1) NOT NULL DEFAULT FALSE,
  PRIMARY KEY (`email`),
  INDEX `user_email_idx` (`user` ASC),
  UNIQUE INDEX `confirm_id_UNIQUE` (`confirm_id` ASC),
  INDEX `email_idx` (`email` ASC),
  INDEX `confirm_id_idx` (`confirm_id` ASC),
  INDEX `confirmed_idx` (`confirmed` ASC),
  INDEX `user_idx` (`user` ASC),
  INDEX `confirmed_user_email_idx` (`confirmed` ASC, `user` ASC, `email` ASC),
  INDEX `primary_idx` (`primary` ASC),
  INDEX `user_primary_email_idx` (`user` ASC, `primary` ASC, `email` ASC),
  INDEX `email_user_idx` (`email` ASC, `user` ASC),
  INDEX `user_confirmed_primary_idx` (`user` ASC, `confirmed` DESC, `primary` DESC),
  INDEX `email_confirmed_idx` (`email` ASC, `confirmed` ASC),
  CONSTRAINT `user_email`
    FOREIGN KEY (`user`)
    REFERENCES `user` (`user`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `contact_form`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `contact_form` (
  `contact_form` VARCHAR(100) NOT NULL,
  `structure` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`contact_form`))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `contact`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `contact` (
  `contact` VARCHAR(100) NOT NULL,
  `contact_form` VARCHAR(100) NOT NULL,
  `user` BIGINT NOT NULL,
  PRIMARY KEY (`contact`, `contact_form`),
  INDEX `user_contact_idx` (`user` ASC),
  INDEX `contact_form_idx` (`contact_form` ASC),
  CONSTRAINT `user_contact`
    FOREIGN KEY (`user`)
    REFERENCES `user` (`user`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `contact_form`
    FOREIGN KEY (`contact_form`)
    REFERENCES `contact_form` (`contact_form`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `news_picture`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `news_picture` (
  `picture` BIGINT NOT NULL AUTO_INCREMENT,
  `url` VARCHAR(100) NOT NULL,
  `subtitle` TEXT NULL,
  `photograph` VARCHAR(100) NULL,
  PRIMARY KEY (`picture`),
  INDEX `picture_idx` (`picture` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `news`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `news` (
  `news` BIGINT NOT NULL AUTO_INCREMENT,
  `author` BIGINT NOT NULL,
  `author_ip` VARCHAR(100) NOT NULL,
  `admin` BIGINT NULL,
  `admin_ip` VARCHAR(100) NULL,
  `headline` TINYTEXT NULL,
  `title` TINYTEXT NULL,
  `teaser` LONGTEXT NULL,
  `text` LONGTEXT NULL,
  `picture1` BIGINT NULL,
  `picture2` BIGINT NULL,
  `date` INT NOT NULL,
  `visible` TINYINT(1) NOT NULL DEFAULT false,
  `deleted` TINYINT(1) NOT NULL DEFAULT false,
  `location` BIGINT NOT NULL,
  `city` VARCHAR(100) NOT NULL,
  `postdate` INT NOT NULL,
  `expire` INT NULL,
  `featured` TINYINT(1) NOT NULL DEFAULT false,
  `corrected` TINYINT(1) NOT NULL DEFAULT false,
  PRIMARY KEY (`news`),
  INDEX `author_user_idx` (`author` ASC),
  INDEX `admin_user_idx` (`admin` ASC),
  INDEX `teaser_picture_idx` (`picture1` ASC),
  INDEX `news_picture_idx` (`picture2` ASC),
  INDEX `deleted_idx` (`deleted` ASC),
  INDEX `visible_idx` (`visible` ASC),
  INDEX `deleted_visible_idx` (`deleted` ASC, `visible` ASC),
  INDEX `location_idx` (`location` ASC),
  INDEX `location_deleted_visible_idx` (`deleted` ASC, `visible` ASC, `location` ASC),
  INDEX `location_deleted_visible_postdate_idx` (`postdate` DESC, `deleted` ASC, `location` ASC, `visible` ASC),
  INDEX `news_idx` (`news` ASC),
  INDEX `news_location_idx` (`news` ASC, `location` ASC),
  INDEX `teaser_picture_visible_deleted_idx` (`picture1` ASC, `visible` ASC, `deleted` ASC),
  INDEX `news_deleted_idx` (`news` ASC, `deleted` ASC),
  INDEX `visible_deleted_postdate_idx` (`postdate` DESC, `visible` ASC, `deleted` ASC),
  INDEX `teaser_picture_visible_deleted_postdate_idx` (`postdate` DESC, `picture1` ASC, `visible` ASC, `deleted` ASC),
  INDEX `pictures_news_deleted_idx` (`picture1` ASC, `picture2` ASC, `news` ASC, `deleted` ASC),
  INDEX `teaser_picture_visible_deleted_location_postdate_idx` (`postdate` DESC, `picture1` ASC, `visible` ASC, `deleted` ASC, `location` ASC),
  INDEX `news_location_deleted_visible_idx` (`news` ASC, `deleted` ASC, `visible` ASC, `location` ASC),
  INDEX `pictures_news_location_deleted_visible_idx` (`picture1` ASC, `picture2` ASC, `news` ASC, `location` ASC, `deleted` ASC, `visible` ASC),
  INDEX `deleted_visible_featured_postdate_idx` (`postdate` DESC, `deleted` ASC, `visible` ASC, `featured` ASC),
  INDEX `news_picture_deleted_visible_featured_postdate_idx` (`postdate` DESC, `picture2` ASC, `deleted` ASC, `visible` ASC, `featured` ASC),
  INDEX `location_deleted_visible_featured_postdate_idx` (`postdate` DESC, `location` ASC, `deleted` ASC, `visible` ASC, `featured` ASC),
  INDEX `teaser_picture_location_deleted_visible_featured_postdate_idx` (`postdate` DESC, `featured` ASC, `picture2` ASC, `location` ASC, `deleted` ASC, `visible` ASC),
  CONSTRAINT `author_user`
    FOREIGN KEY (`author`)
    REFERENCES `user` (`user`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `admin_user`
    FOREIGN KEY (`admin`)
    REFERENCES `user` (`user`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `teaser_picture`
    FOREIGN KEY (`picture1`)
    REFERENCES `news_picture` (`picture`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE,
  CONSTRAINT `news_picture`
    FOREIGN KEY (`picture2`)
    REFERENCES `news_picture` (`picture`)
    ON DELETE RESTRICT
    ON UPDATE CASCADE)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `navigation`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `navigation` (
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `type` INT NOT NULL,
  `category` INT NULL,
  `head` LONGTEXT NULL,
  `module` VARCHAR(100) NULL,
  `foot` LONGTEXT NULL,
  `pos` INT NOT NULL DEFAULT 0,
  `maps_to` INT NULL,
  PRIMARY KEY (`id`),
  INDEX `link_category_idx` (`category` ASC),
  INDEX `link_map_idx` (`maps_to` ASC),
  INDEX `module_idx` (`module` ASC),
  INDEX `id_idx` (`id` ASC),
  INDEX `module_id_idx` (`module` ASC, `id` ASC),
  INDEX `type_idx` (`type` ASC),
  INDEX `pos_idx` (`pos` ASC),
  INDEX `pos_module_type_idx` (`pos` ASC, `module` ASC, `type` ASC),
  INDEX `id_type_idx` (`id` ASC, `type` ASC),
  INDEX `pos_type_idx` (`pos` ASC, `type` ASC),
  CONSTRAINT `link_category`
    FOREIGN KEY (`category`)
    REFERENCES `navigation` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `link_map`
    FOREIGN KEY (`maps_to`)
    REFERENCES `navigation` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `album`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `album` (
  `album` BIGINT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `author` BIGINT NOT NULL,
  `author_ip` VARCHAR(100) NOT NULL,
  `admin` BIGINT NULL,
  `admin_ip` VARCHAR(100) NULL,
  `photograph` VARCHAR(100) NULL,
  `description` LONGTEXT NULL,
  `folder` VARCHAR(100) NOT NULL,
  `visible` TINYINT(1) NOT NULL,
  `deleted` TINYINT(1) NOT NULL,
  `date` INT NOT NULL,
  `postdate` INT NOT NULL,
  `location` BIGINT NOT NULL,
  PRIMARY KEY (`album`),
  INDEX `album_author_idx` (`author` ASC),
  INDEX `album_admin_idx` (`admin` ASC),
  INDEX `album_location_idx` (`location` ASC),
  INDEX `album_idx` (`album` DESC),
  INDEX `visible_idx` (`visible` ASC),
  INDEX `deleted_idx` (`deleted` ASC),
  INDEX `visible_deleted_idx` (`visible` ASC, `deleted` ASC),
  INDEX `album_deleted_idx` (`album` ASC, `deleted` ASC),
  INDEX `visible_deleted_album_idx` (`visible` ASC, `deleted` ASC, `album` DESC),
  INDEX `visible_deleted_location_idx` (`visible` ASC, `deleted` ASC, `location` ASC),
  INDEX `visible_deleted_location_album_idx` (`visible` ASC, `deleted` ASC, `location` ASC, `album` DESC),
  CONSTRAINT `album_author`
    FOREIGN KEY (`author`)
    REFERENCES `user` (`user`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `album_admin`
    FOREIGN KEY (`admin`)
    REFERENCES `user` (`user`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `album_location`
    FOREIGN KEY (`location`)
    REFERENCES `navigation` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `picture`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `picture` (
  `picture` BIGINT NOT NULL AUTO_INCREMENT,
  `album` BIGINT NOT NULL,
  `subtitle` TEXT NULL,
  `filename` VARCHAR(100) NOT NULL,
  `deleted` TINYINT(1) NOT NULL,
  `visible` TINYINT(1) NOT NULL,
  PRIMARY KEY (`picture`),
  INDEX `picture_album_idx` (`album` ASC),
  INDEX `picture_idx` (`picture` ASC),
  INDEX `filename_idx` (`filename` ASC),
  INDEX `album_filename_idx` (`album` ASC, `filename` ASC),
  INDEX `album_picture_idx` (`album` ASC, `picture` ASC),
  INDEX `deleted_idx` (`deleted` ASC),
  INDEX `album_deleted_idx` (`album` ASC, `deleted` ASC),
  INDEX `album_deleted_filename_idx` (`filename` ASC, `album` ASC, `deleted` ASC),
  INDEX `visible_idx` (`visible` ASC),
  INDEX `album_deleted_visible_idx` (`album` ASC, `deleted` ASC, `visible` ASC),
  INDEX `album_deleted_visible_filename_idx` (`album` ASC, `filename` ASC, `deleted` ASC, `visible` ASC),
  CONSTRAINT `picture_album`
    FOREIGN KEY (`album`)
    REFERENCES `album` (`album`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `rights`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `rights` (
  `role` BIGINT NOT NULL,
  `location` BIGINT NOT NULL,
  `read` TINYINT(1) NOT NULL DEFAULT false,
  `write` TINYINT(1) NOT NULL DEFAULT false,
  `extended` TINYINT(1) NOT NULL DEFAULT false,
  `admin` TINYINT(1) NOT NULL DEFAULT false,
  PRIMARY KEY (`role`, `location`),
  INDEX `rights_role_idx` (`role` ASC),
  INDEX `rights_navigation_idx` (`location` ASC),
  INDEX `read_idx` (`read` ASC),
  INDEX `write_idx` (`write` ASC),
  INDEX `extended_idx` (`extended` ASC),
  INDEX `admin_idx` (`admin` ASC),
  INDEX `role_location_read_idx` (`role` ASC, `location` ASC, `read` ASC),
  INDEX `role_location_idx` (`role` ASC, `location` ASC),
  INDEX `read_write_extended_admin_idx` (`read` ASC, `write` ASC, `extended` ASC, `admin` ASC),
  INDEX `role_admin_idx` (`role` ASC, `admin` ASC),
  CONSTRAINT `rights_role`
    FOREIGN KEY (`role`)
    REFERENCES `role` (`role`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `rights_navigation`
    FOREIGN KEY (`location`)
    REFERENCES `navigation` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `registration_tos`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `registration_tos` (
  `id` BIGINT NULL,
  PRIMARY KEY (`id`),
  INDEX `tos_navigation_idx` (`id` ASC),
  CONSTRAINT `tos_navigation`
    FOREIGN KEY (`id`)
    REFERENCES `navigation` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `module`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `module` (
  `name` VARCHAR(100) NOT NULL,
  `file` VARCHAR(100) NOT NULL,
  `class` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`name`),
  INDEX `file_idx` (`file` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `rights_module`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `rights_module` (
  `role` BIGINT NOT NULL,
  `module` VARCHAR(100) NOT NULL,
  `read` TINYINT(1) NOT NULL DEFAULT false,
  `write` TINYINT(1) NOT NULL DEFAULT false,
  `extended` TINYINT(1) NOT NULL DEFAULT false,
  `admin` TINYINT(1) NOT NULL DEFAULT false,
  PRIMARY KEY (`role`, `module`),
  INDEX `rights_role_idx` (`role` ASC),
  INDEX `rights_module_idx` (`module` ASC),
  INDEX `role_module_idx` (`role` ASC, `module` ASC),
  INDEX `read_idx` (`read` ASC),
  INDEX `write_idx` (`write` ASC),
  INDEX `extended_idx` (`extended` ASC),
  INDEX `admin_idx` (`admin` ASC),
  INDEX `read_write_extended_admin_idx` (`read` ASC, `write` ASC, `extended` ASC, `admin` ASC),
  INDEX `role_admin_idx` (`role` ASC, `admin` ASC),
  CONSTRAINT `rights_role`
    FOREIGN KEY (`role`)
    REFERENCES `role` (`role`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `rights_module`
    FOREIGN KEY (`module`)
    REFERENCES `module` (`name`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `role_editor`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `role_editor` (
  `master` BIGINT NOT NULL,
  `slave` BIGINT NOT NULL,
  PRIMARY KEY (`master`, `slave`),
  INDEX `master.role_idx` (`master` ASC),
  INDEX `slave.role_idx` (`slave` ASC),
  INDEX `master_slave_idx` (`master` ASC, `slave` ASC),
  CONSTRAINT `master.role`
    FOREIGN KEY (`master`)
    REFERENCES `role` (`role`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `slave.role`
    FOREIGN KEY (`slave`)
    REFERENCES `role` (`role`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `stdroles`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `stdroles` (
  `guest` BIGINT NOT NULL,
  `user` BIGINT NOT NULL,
  INDEX `guest_idx` (`guest` ASC),
  INDEX `user_idx` (`user` ASC),
  CONSTRAINT `guest`
    FOREIGN KEY (`guest`)
    REFERENCES `role` (`role`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `user`
    FOREIGN KEY (`user`)
    REFERENCES `role` (`role`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `homepage`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `homepage` (
  `homepage` BIGINT NOT NULL,
  PRIMARY KEY (`homepage`),
  INDEX `standard_link_idx` (`homepage` ASC),
  CONSTRAINT `standard_link`
    FOREIGN KEY (`homepage`)
    REFERENCES `navigation` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `board`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `board` (
  `board` BIGINT NOT NULL AUTO_INCREMENT,
  `pos` INT NOT NULL DEFAULT 0,
  `description` LONGTEXT NULL,
  `title` TINYTEXT NOT NULL,
  `type` INT NOT NULL DEFAULT 0,
  `location` BIGINT NOT NULL,
  `threadcount` INT NOT NULL DEFAULT 0,
  `postcount` INT NOT NULL DEFAULT 0,
  PRIMARY KEY (`board`),
  INDEX `board_location_idx` (`location` ASC),
  INDEX `type_idx` (`type` ASC),
  INDEX `pos_idx` (`pos` ASC),
  INDEX `type_location_pos_idx` (`type` ASC, `location` ASC, `pos` ASC),
  INDEX `board_idx` (`board` ASC),
  INDEX `board_type_idx` (`board` ASC, `type` ASC),
  INDEX `type_pos_idx` (`type` ASC, `pos` ASC),
  CONSTRAINT `board_location`
    FOREIGN KEY (`location`)
    REFERENCES `navigation` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `rights_board`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `rights_board` (
  `role` BIGINT NOT NULL,
  `board` BIGINT NOT NULL,
  `read` TINYINT(1) NOT NULL,
  `write` TINYINT(1) NOT NULL,
  `extended` TINYINT(1) NOT NULL,
  `admin` TINYINT(1) NOT NULL,
  PRIMARY KEY (`role`, `board`),
  INDEX `board_role_idx` (`role` ASC),
  INDEX `role_board_idx` (`board` ASC),
  INDEX `read_idx` (`read` ASC),
  INDEX `write_idx` (`write` ASC),
  INDEX `extended_idx` (`extended` ASC),
  INDEX `admin_idx` (`admin` ASC),
  INDEX `read_write_extended_admin_idx` (`read` ASC, `write` ASC, `extended` ASC, `admin` ASC),
  INDEX `role_board_conj_idx` (`role` ASC, `board` ASC),
  INDEX `role_board_read_write_extended_admin_idx` (`role` ASC, `board` ASC, `read` ASC, `write` ASC, `extended` ASC, `admin` ASC),
  INDEX `board_read_write_extended_admin_idx` (`board` ASC, `read` ASC, `write` ASC, `extended` ASC, `admin` ASC),
  CONSTRAINT `board_role`
    FOREIGN KEY (`role`)
    REFERENCES `role` (`role`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `role_board`
    FOREIGN KEY (`board`)
    REFERENCES `board` (`board`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `board_operator`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `board_operator` (
  `board` BIGINT NOT NULL,
  `user` BIGINT NOT NULL,
  PRIMARY KEY (`board`, `user`),
  INDEX `operator_board_idx` (`board` ASC),
  INDEX `operator_user_idx` (`user` ASC),
  INDEX `board_user_idx` (`board` ASC, `user` ASC),
  CONSTRAINT `operator_board`
    FOREIGN KEY (`board`)
    REFERENCES `board` (`board`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `operator_user`
    FOREIGN KEY (`user`)
    REFERENCES `user` (`user`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `post`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `post` (
  `post` BIGINT NOT NULL AUTO_INCREMENT,
  `author` BIGINT NOT NULL,
  `thread` BIGINT NOT NULL,
  `date` INT NOT NULL,
  `operator` BIGINT NULL,
  `lastedit` INT NULL,
  `content` LONGTEXT NULL,
  `ip` VARCHAR(100) NOT NULL,
  `deleted` TINYINT(1) NOT NULL,
  PRIMARY KEY (`post`),
  INDEX `post_author_idx` (`author` ASC),
  INDEX `post_thread_idx` (`thread` ASC),
  INDEX `post_operator_idx` (`operator` ASC),
  INDEX `deleted_idx` (`deleted` ASC),
  INDEX `post_idx` (`post` ASC),
  INDEX `thread_post_idx` (`post` ASC, `thread` ASC),
  INDEX `deleted_idx` (`deleted` ASC),
  INDEX `date_idx` (`date` DESC),
  INDEX `deleted_date_idx` (`date` DESC, `deleted` ASC),
  INDEX `thread_deleted_date_idx` (`date` DESC, `thread` ASC, `deleted` ASC),
  INDEX `thread_deleted_idx` (`thread` ASC, `deleted` ASC),
  INDEX `date_asc_idx` (`date` ASC),
  INDEX `thread_deleted_date_asc_idx` (`date` ASC, `thread` ASC, `deleted` ASC),
  INDEX `post_deleted_idx` (`post` ASC, `deleted` ASC),
  INDEX `contend_idx` (`content` ASC),
  INDEX `operator_idx` (`operator` ASC),
  INDEX `lastedit_idx` (`lastedit` ASC),
  INDEX `post_content_operator_lastedit_idx` (`post` ASC, `content` ASC, `operator` ASC, `lastedit` ASC),
  INDEX `post_date_idx` (`date` DESC, `post` ASC),
  CONSTRAINT `post_author`
    FOREIGN KEY (`author`)
    REFERENCES `user` (`user`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `post_thread`
    FOREIGN KEY (`thread`)
    REFERENCES `thread` (`thread`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `post_operator`
    FOREIGN KEY (`operator`)
    REFERENCES `user` (`user`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `thread`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `thread` (
  `thread` BIGINT NOT NULL AUTO_INCREMENT,
  `board` BIGINT NOT NULL,
  `postcount` INT NOT NULL DEFAULT 0,
  `type` INT NOT NULL,
  `title` TINYTEXT NULL,
  `author` BIGINT NOT NULL,
  `viewcount` INT NOT NULL DEFAULT 0,
  `lastpost` BIGINT NOT NULL,
  PRIMARY KEY (`thread`),
  INDEX `thread_board_idx` (`board` ASC),
  INDEX `thread_author_idx` (`author` ASC),
  INDEX `thread_post_idx` (`lastpost` ASC),
  INDEX `thread_idx` (`thread` ASC),
  INDEX `type_idx` (`type` ASC),
  INDEX `type_board_idx` (`type` ASC, `board` ASC),
  INDEX `thread_type_board_idx` (`thread` ASC, `type` ASC, `board` ASC),
  INDEX `thread_type_idx` (`thread` ASC, `type` ASC),
  INDEX `lastpost_type_board_idx` (`lastpost` ASC, `type` ASC, `board` ASC),
  CONSTRAINT `thread_board`
    FOREIGN KEY (`board`)
    REFERENCES `board` (`board`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `thread_author`
    FOREIGN KEY (`author`)
    REFERENCES `user` (`user`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `thread_post`
    FOREIGN KEY (`lastpost`)
    REFERENCES `post` (`post`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `news_tag`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `news_tag` (
  `tag` BIGINT NOT NULL,
  `news` BIGINT NOT NULL,
  `type` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`tag`, `news`, `type`),
  INDEX `news_idx` (`news` ASC),
  INDEX `tag_idx` (`tag` ASC),
  INDEX `type_idx` (`type` ASC),
  INDEX `tag_type_idx` (`tag` ASC, `type` ASC),
  INDEX `tag_type_news_idx` (`tag` ASC, `news` ASC, `type` ASC),
  INDEX `type_news_idx` (`news` ASC, `type` ASC),
  INDEX `tag_news_idx` (`tag` ASC, `news` ASC),
  CONSTRAINT `tag_news`
    FOREIGN KEY (`news`)
    REFERENCES `news` (`news`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `general`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `general` (
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `tag` VARCHAR(100) NOT NULL,
  PRIMARY KEY (`id`),
  INDEX `tag_idx` (`tag` ASC),
  INDEX `id_idx` (`id` ASC),
  INDEX `tag_id_idx` (`tag` ASC, `id` ASC),
  CONSTRAINT `general_news`
    FOREIGN KEY (`id`)
    REFERENCES `news_tag` (`tag`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `location`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `location` (
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `tag` VARCHAR(100) NOT NULL,
  `street` VARCHAR(100) NULL,
  `number` VARCHAR(100) NULL,
  `zip` VARCHAR(100) NULL,
  `city` VARCHAR(100) NULL,
  `country` VARCHAR(100) NULL,
  `capacity` VARCHAR(100) NULL,
  `info` LONGTEXT NULL,
  PRIMARY KEY (`id`),
  INDEX `tag_idx` (`tag` ASC),
  INDEX `id_idx` (`id` ASC),
  INDEX `tag_id_idx` (`tag` ASC, `id` ASC),
  CONSTRAINT `location_tag`
    FOREIGN KEY (`id`)
    REFERENCES `news_tag` (`tag`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `band`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `band` (
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `tag` VARCHAR(100) NOT NULL,
  `founded` VARCHAR(100) NULL,
  `ended` VARCHAR(100) NULL,
  `info` LONGTEXT NULL,
  PRIMARY KEY (`id`),
  INDEX `band_tag_idx` (`id` ASC),
  INDEX `tag_idx` (`tag` ASC),
  INDEX `tag_id_idx` (`tag` ASC, `id` ASC),
  CONSTRAINT `band_tag`
    FOREIGN KEY (`id`)
    REFERENCES `news_tag` (`tag`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `event`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `event` (
  `event` BIGINT NOT NULL AUTO_INCREMENT,
  `title` LONGTEXT NULL,
  `visible` TINYINT(1) NOT NULL DEFAULT FALSE,
  `deleted` TINYINT(1) NOT NULL DEFAULT FALSE,
  `start` INT NOT NULL,
  `end` INT NULL,
  `doors` INT NULL,
  `date` INT NOT NULL,
  `author` BIGINT NOT NULL,
  `foreign_id` VARCHAR(255) NULL,
  PRIMARY KEY (`event`),
  INDEX `event_author_idx` (`author` ASC),
  CONSTRAINT `event_author`
    FOREIGN KEY (`author`)
    REFERENCES `user` (`user`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `event_location`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `event_location` (
  `location` BIGINT NOT NULL,
  `event` BIGINT NOT NULL,
  PRIMARY KEY (`location`, `event`),
  INDEX `el_location_idx` (`location` ASC),
  INDEX `el_event_idx` (`event` ASC),
  CONSTRAINT `el_location`
    FOREIGN KEY (`location`)
    REFERENCES `location` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `el_event`
    FOREIGN KEY (`event`)
    REFERENCES `event` (`event`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `event_band`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `event_band` (
  `band` BIGINT NOT NULL,
  `event` BIGINT NOT NULL,
  PRIMARY KEY (`band`, `event`),
  INDEX `eb_band_idx` (`band` ASC),
  INDEX `eb_event_idx` (`event` ASC),
  CONSTRAINT `eb_band`
    FOREIGN KEY (`band`)
    REFERENCES `band` (`id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `eb_event`
    FOREIGN KEY (`event`)
    REFERENCES `event` (`event`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `attachment`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `attachment` (
  `file` BIGINT NOT NULL AUTO_INCREMENT,
  `servername` VARCHAR(100) NOT NULL,
  `realname` VARCHAR(100) NOT NULL,
  `key` VARCHAR(255) NOT NULL,
  `temporary` VARCHAR(255) NOT NULL,
  PRIMARY KEY (`file`),
  INDEX `file_idx` (`file` ASC),
  INDEX `servername_idx` (`servername` ASC),
  INDEX `key_idx` (`key` ASC),
  INDEX `temporary_idx` (`temporary` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `post_attachment`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `post_attachment` (
  `post` BIGINT NOT NULL,
  `file` BIGINT NOT NULL,
  PRIMARY KEY (`post`, `file`),
  INDEX `pa_post_idx` (`post` ASC),
  INDEX `pa_file_idx` (`file` ASC),
  INDEX `file_post_idx` (`post` ASC, `file` ASC),
  CONSTRAINT `pa_post`
    FOREIGN KEY (`post`)
    REFERENCES `post` (`post`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `pa_file`
    FOREIGN KEY (`file`)
    REFERENCES `attachment` (`file`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `newsletter_configuration`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `newsletter_configuration` (
  `allow_anon_registration` TINYINT(1) NULL)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `newsletter_entry`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `newsletter_entry` (
  `confirm_id` CHAR(32) NOT NULL,
  `prename` VARCHAR(50) NULL,
  `name` VARCHAR(50) NULL,
  `role` BIGINT NOT NULL,
  PRIMARY KEY (`confirm_id`, `role`),
  INDEX `newsletter_role_idx` (`role` ASC),
  CONSTRAINT `newsletter_email`
    FOREIGN KEY (`confirm_id`)
    REFERENCES `email` (`confirm_id`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION,
  CONSTRAINT `newsletter_role`
    FOREIGN KEY (`role`)
    REFERENCES `role` (`role`)
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `app`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `app` (
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `name` VARCHAR(100) NOT NULL,
  `key` CHAR(32) NOT NULL,
  `secret` CHAR(128) NOT NULL,
  PRIMARY KEY (`id`),
  UNIQUE INDEX `name_UNIQUE` (`name` ASC),
  UNIQUE INDEX `key_UNIQUE` (`key` ASC),
  UNIQUE INDEX `secret_UNIQUE` (`secret` ASC),
  INDEX `id_idx` (`id` ASC),
  INDEX `key_idx` (`key` ASC))
ENGINE = InnoDB;


-- -----------------------------------------------------
-- Table `pushtoken`
-- -----------------------------------------------------
CREATE TABLE IF NOT EXISTS `pushtoken` (
  `id` BIGINT NOT NULL AUTO_INCREMENT,
  `type` VARCHAR(7) NOT NULL,
  `pushtoken` VARCHAR(100) NULL,
  `endpoint` LONGTEXT NULL,
  `key` VARCHAR(100) NULL,
  `auth` VARCHAR(100) NULL,
  PRIMARY KEY (`id`),
  INDEX `type_idx` (`type` ASC),
  INDEX `endpoint_idx` (`endpoint` ASC),
  INDEX `auth_idx` (`auth` ASC),
  INDEX `key_idx` (`key` ASC),
  INDEX `type_endpoint_auth_key_idx` (`type` ASC, `endpoint` ASC, `auth` ASC, `key` ASC))
ENGINE = InnoDB;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;
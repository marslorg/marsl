SET @OLD_UNIQUE_CHECKS=@@UNIQUE_CHECKS, UNIQUE_CHECKS=0;
SET @OLD_FOREIGN_KEY_CHECKS=@@FOREIGN_KEY_CHECKS, FOREIGN_KEY_CHECKS=0;
SET @OLD_SQL_MODE=@@SQL_MODE, SQL_MODE='TRADITIONAL,ALLOW_INVALID_DATES';

ALTER TABLE `user` 
DROP INDEX `user_picture` 
, ADD INDEX `user_picture_idx` (`picture` ASC) 
, DROP INDEX `user_role` 
, ADD INDEX `user_role_idx` (`role` ASC) ;

ALTER TABLE `email` 
DROP INDEX `user_email` 
, ADD INDEX `user_email_idx` (`user` ASC) ;

ALTER TABLE `contact` 
DROP INDEX `user_contact` 
, ADD INDEX `user_contact_idx` (`user` ASC) 
, DROP INDEX `contact_form` 
, ADD INDEX `contact_form_idx` (`contact_form` ASC) ;

ALTER TABLE `user_album` 
DROP INDEX `user_album` 
, ADD INDEX `user_album_idx` (`user` ASC) ;

ALTER TABLE `user_picture` 
DROP INDEX `user_picture_album` 
, ADD INDEX `user_picture_album_idx` (`album` ASC) ;

ALTER TABLE `news` 
DROP INDEX `author_user` 
, ADD INDEX `author_user_idx` (`author` ASC) 
, DROP INDEX `admin_user` 
, ADD INDEX `admin_user_idx` (`admin` ASC) 
, DROP INDEX `teaser_picture` 
, ADD INDEX `teaser_picture_idx` (`picture1` ASC) 
, DROP INDEX `news_picture` 
, ADD INDEX `news_picture_idx` (`picture2` ASC) ;

ALTER TABLE `album` 
DROP INDEX `album_author` 
, ADD INDEX `album_author_idx` (`author` ASC) 
, DROP INDEX `album_admin` 
, ADD INDEX `album_admin_idx` (`admin` ASC) 
, DROP INDEX `album_location` 
, ADD INDEX `album_location_idx` (`location` ASC) ;

ALTER TABLE `picture` 
DROP INDEX `picture_album` 
, ADD INDEX `picture_album_idx` (`album` ASC) ;

ALTER TABLE `navigation` 
DROP INDEX `link_category` 
, ADD INDEX `link_category_idx` (`category` ASC) 
, DROP INDEX `link_map` 
, ADD INDEX `link_map_idx` (`maps_to` ASC) ;

ALTER TABLE `rights` 
DROP INDEX `rights_role` 
, ADD INDEX `rights_role_idx` (`role` ASC) 
, DROP INDEX `rights_navigation` 
, ADD INDEX `rights_navigation_idx` (`location` ASC) ;

ALTER TABLE `registration_tos` 
DROP INDEX `tos_navigation` 
, ADD INDEX `tos_navigation_idx` (`id` ASC) ;

ALTER TABLE `rights_module` 
DROP INDEX `rights_role` 
, ADD INDEX `rights_role_idx` (`role` ASC) 
, DROP INDEX `rights_module` 
, ADD INDEX `rights_module_idx` (`module` ASC) ;

ALTER TABLE `role_editor` 
DROP INDEX `master.role` 
, ADD INDEX `master.role_idx` (`master` ASC) 
, DROP INDEX `slave.role` 
, ADD INDEX `slave.role_idx` (`slave` ASC) ;

ALTER TABLE `stdroles` 
DROP INDEX `guest` 
, ADD INDEX `guest_idx` (`guest` ASC) 
, DROP INDEX `user` 
, ADD INDEX `user_idx` (`user` ASC) ;

ALTER TABLE `homepage` 
DROP INDEX `standard_link` 
, ADD INDEX `standard_link_idx` (`homepage` ASC) ;

ALTER TABLE `rights_board` 
ADD INDEX `board_role_idx` (`role` ASC) 
, ADD INDEX `role_board_idx` (`board` ASC) 
, DROP INDEX `role_board` 
, DROP INDEX `board_role` ;

ALTER TABLE `board` 
ADD INDEX `board_location_idx` (`location` ASC) 
, DROP INDEX `board_location` ;

ALTER TABLE `board_operator` 
ADD INDEX `operator_board_idx` (`board` ASC) 
, ADD INDEX `operator_user_idx` (`user` ASC) 
, DROP INDEX `operator_user` 
, DROP INDEX `operator_board` ;

ALTER TABLE `thread` 
ADD INDEX `thread_board_idx` (`board` ASC) 
, ADD INDEX `thread_author_idx` (`author` ASC) 
, ADD INDEX `thread_post_idx` (`lastpost` ASC) 
, DROP INDEX `thread_post` 
, DROP INDEX `thread_author` 
, DROP INDEX `thread_board` ;

ALTER TABLE `post` 
ADD INDEX `post_author_idx` (`author` ASC) 
, ADD INDEX `post_thread_idx` (`thread` ASC) 
, ADD INDEX `post_operator_idx` (`operator` ASC) 
, DROP INDEX `post_operator` 
, DROP INDEX `post_thread` 
, DROP INDEX `post_author` ;

CREATE  TABLE IF NOT EXISTS `general` (
  `tag` VARCHAR(100) NOT NULL ,
  `id` INT(11) NOT NULL AUTO_INCREMENT ,
  PRIMARY KEY (`id`) )
ENGINE = MyISAM
DEFAULT CHARACTER SET = latin1
COLLATE = latin1_swedish_ci;

CREATE  TABLE IF NOT EXISTS `location` (
  `id` INT(11) NOT NULL AUTO_INCREMENT ,
  `tag` VARCHAR(100) NOT NULL ,
  `street` VARCHAR(100) NULL DEFAULT NULL ,
  `number` VARCHAR(100) NULL DEFAULT NULL ,
  `zip` VARCHAR(100) NULL DEFAULT NULL ,
  `city` VARCHAR(100) NULL DEFAULT NULL ,
  `country` VARCHAR(100) NULL DEFAULT NULL ,
  `capacity` VARCHAR(100) NULL DEFAULT NULL ,
  `info` LONGTEXT NULL DEFAULT NULL ,
  PRIMARY KEY (`id`) )
ENGINE = MyISAM
DEFAULT CHARACTER SET = latin1
COLLATE = latin1_swedish_ci;

CREATE  TABLE IF NOT EXISTS `news_tag` (
  `tag` INT(11) NOT NULL ,
  `news` INT(11) NOT NULL ,
  `type` VARCHAR(100) NOT NULL ,
  PRIMARY KEY (`tag`, `news`, `type`) ,
  INDEX `tag_news_idx` (`news` ASC) )
ENGINE = MyISAM
DEFAULT CHARACTER SET = latin1
COLLATE = latin1_swedish_ci;

CREATE  TABLE IF NOT EXISTS `band` (
  `id` INT(11) NOT NULL AUTO_INCREMENT ,
  `tag` VARCHAR(100) NOT NULL ,
  `founded` VARCHAR(100) NULL DEFAULT NULL ,
  `ended` VARCHAR(100) NULL DEFAULT NULL ,
  `info` LONGTEXT NULL DEFAULT NULL ,
  PRIMARY KEY (`id`) ,
  INDEX `band_tag_idx` (`id` ASC) ,
  CONSTRAINT `band_tag`
    FOREIGN KEY (`id` )
    REFERENCES `news_tag` (`tag` )
    ON DELETE NO ACTION
    ON UPDATE NO ACTION)
ENGINE = MyISAM
DEFAULT CHARACTER SET = latin1
COLLATE = latin1_swedish_ci;


SET SQL_MODE=@OLD_SQL_MODE;
SET FOREIGN_KEY_CHECKS=@OLD_FOREIGN_KEY_CHECKS;
SET UNIQUE_CHECKS=@OLD_UNIQUE_CHECKS;

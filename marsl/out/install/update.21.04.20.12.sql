ALTER TABLE `user` 
DROP FOREIGN KEY `user_role`;

ALTER TABLE `email` 
DROP FOREIGN KEY `user_email`;

ALTER TABLE `contact` 
DROP FOREIGN KEY `user_contact`;

ALTER TABLE `user_album` 
DROP FOREIGN KEY `user_album`;

ALTER TABLE `user_picture` 
DROP FOREIGN KEY `user_picture_album`;

ALTER TABLE `news` 
DROP FOREIGN KEY `author_user`,
DROP FOREIGN KEY `admin_user`,
DROP FOREIGN KEY `teaser_picture`,
DROP FOREIGN KEY `news_picture`;

ALTER TABLE `album` 
DROP FOREIGN KEY `album_author`,
DROP FOREIGN KEY `album_admin`,
DROP FOREIGN KEY `album_location`;

ALTER TABLE `picture` 
DROP FOREIGN KEY `picture_album`;

ALTER TABLE `rights` 
DROP FOREIGN KEY `rights_role`,
DROP FOREIGN KEY `rights_navigation`;

ALTER TABLE `registration_tos` 
DROP FOREIGN KEY `tos_navigation`;

ALTER TABLE `rights_module` 
DROP FOREIGN KEY `rights_role`;

ALTER TABLE `role_editor` 
DROP FOREIGN KEY `master.role`,
DROP FOREIGN KEY `slave.role`;

ALTER TABLE `stdroles` 
DROP FOREIGN KEY `guest`,
DROP FOREIGN KEY `user`;

ALTER TABLE `homepage` 
DROP FOREIGN KEY `standard_link`;

ALTER TABLE `rights_board` 
DROP FOREIGN KEY `board_role`,
DROP FOREIGN KEY `role_board`;

ALTER TABLE `board` 
DROP FOREIGN KEY `board_location`;

ALTER TABLE `board_operator` 
DROP FOREIGN KEY `operator_board`,
DROP FOREIGN KEY `operator_user`;

ALTER TABLE `thread` 
DROP FOREIGN KEY `thread_board`,
DROP FOREIGN KEY `thread_author`,
DROP FOREIGN KEY `thread_post`;

ALTER TABLE `post` 
DROP FOREIGN KEY `post_author`,
DROP FOREIGN KEY `post_thread`,
DROP FOREIGN KEY `post_operator`;

ALTER TABLE `general` 
DROP FOREIGN KEY `general_news`;

ALTER TABLE `location` 
DROP FOREIGN KEY `location_tag`;

ALTER TABLE `news_tag` 
DROP FOREIGN KEY `tag_news`;

ALTER TABLE `band` 
DROP FOREIGN KEY `band_tag`;

ALTER TABLE `event` 
DROP FOREIGN KEY `event_author`;

ALTER TABLE `event_location` 
DROP FOREIGN KEY `el_location`,
DROP FOREIGN KEY `el_event`;

ALTER TABLE `event_band` 
DROP FOREIGN KEY `eb_band`,
DROP FOREIGN KEY `eb_event`;

ALTER TABLE `post_attachment` 
DROP FOREIGN KEY `pa_post`,
DROP FOREIGN KEY `pa_file`;

ALTER TABLE `newsletter_entry` 
DROP FOREIGN KEY `newsletter_role`;

ALTER TABLE `user` 
CHANGE COLUMN `user` `user` BIGINT(20) NOT NULL AUTO_INCREMENT ,
CHANGE COLUMN `role` `role` BIGINT(20) NOT NULL ,
ADD INDEX `sessionid_idx` (`sessionid` ASC),
ADD INDEX `role_user_idx` (`user` ASC, `role` ASC),
ADD INDEX `nickname_idx` (`nickname` ASC),
ADD INDEX `user_role_nickname_idx` (`user` ASC, `role` ASC, `nickname` ASC),
ADD INDEX `user_idx` (`user` ASC),
ADD INDEX `deleted_idx` (`deleted` ASC),
ADD INDEX `session_deleted_idx` (`sessionid` ASC, `deleted` ASC),
ADD INDEX `role_session_deleted_idx` (`role` ASC, `sessionid` ASC, `deleted` ASC),
ADD INDEX `user_deleted_idx` (`user` ASC, `deleted` ASC),
ADD INDEX `role_user_deleted_idx` (`role` ASC, `deleted` ASC),
ADD INDEX `password_idx` (`password` ASC),
ADD INDEX `nickname_password_idx` (`nickname` ASC, `password` ASC),
ADD INDEX `acronym_idx` (`acronym` ASC),
ADD INDEX `acronym_user_idx` (`acronym` ASC, `user` ASC),
ADD INDEX `nickname_user_idx` (`nickname` ASC, `user` ASC),
ADD INDEX `role_deleted_idx` (`role` ASC, `deleted` ASC);
;

ALTER TABLE `role` 
CHANGE COLUMN `role` `role` BIGINT(20) NOT NULL AUTO_INCREMENT ,
ADD INDEX `role_idx` (`role` ASC),
ADD INDEX `name_idx` (`name` ASC),
ADD INDEX `role_name_idx` (`role` ASC, `name` ASC);
;

ALTER TABLE `email` 
CHANGE COLUMN `user` `user` BIGINT(20) NULL DEFAULT NULL ,
ADD INDEX `email_idx` (`email` ASC),
ADD INDEX `confirm_id_idx` (`confirm_id` ASC),
ADD INDEX `confirmed_idx` (`confirmed` ASC),
ADD INDEX `user_idx` (`user` ASC),
ADD INDEX `confirmed_user_email_idx` (`confirmed` ASC, `user` ASC, `email` ASC),
ADD INDEX `primary_idx` (`primary` ASC),
ADD INDEX `user_primary_email_idx` (`user` ASC, `primary` ASC, `email` ASC),
ADD INDEX `email_user_idx` (`email` ASC, `user` ASC),
ADD INDEX `user_confirmed_primary_idx` (`user` ASC, `confirmed` DESC, `primary` DESC),
ADD INDEX `email_confirmed_idx` (`email` ASC, `confirmed` ASC);
;

ALTER TABLE `contact` 
CHANGE COLUMN `user` `user` BIGINT(20) NOT NULL;
;

ALTER TABLE `user_album` 
CHANGE COLUMN `album` `album` BIGINT(20) NOT NULL AUTO_INCREMENT ,
CHANGE COLUMN `user` `user` BIGINT(20) NOT NULL ;

ALTER TABLE `user_picture` 
CHANGE COLUMN `album` `album` BIGINT(20) NOT NULL ;

ALTER TABLE `news` 
CHANGE COLUMN `news` `news` BIGINT(20) NOT NULL AUTO_INCREMENT ,
CHANGE COLUMN `author` `author` BIGINT(20) NOT NULL ,
CHANGE COLUMN `admin` `admin` BIGINT(20) NULL DEFAULT NULL ,
CHANGE COLUMN `picture1` `picture1` BIGINT(20) NULL DEFAULT NULL ,
CHANGE COLUMN `picture2` `picture2` BIGINT(20) NULL DEFAULT NULL ,
CHANGE COLUMN `location` `location` BIGINT(20) NOT NULL ,
ADD INDEX `deleted_idx` (`deleted` ASC),
ADD INDEX `visible_idx` (`visible` ASC),
ADD INDEX `deleted_visible_idx` (`deleted` ASC, `visible` ASC),
ADD INDEX `location_idx` (`location` ASC),
ADD INDEX `location_deleted_visible_idx` (`deleted` ASC, `visible` ASC, `location` ASC),
ADD INDEX `location_deleted_visible_postdate_idx` (`postdate` DESC, `deleted` ASC, `location` ASC, `visible` ASC),
ADD INDEX `news_idx` (`news` ASC),
ADD INDEX `news_location_idx` (`news` ASC, `location` ASC),
ADD INDEX `teaser_picture_visible_deleted_idx` (`picture1` ASC, `visible` ASC, `deleted` ASC),
ADD INDEX `news_deleted_idx` (`news` ASC, `deleted` ASC),
ADD INDEX `visible_deleted_postdate_idx` (`postdate` DESC, `visible` ASC, `deleted` ASC),
ADD INDEX `teaser_picture_visible_deleted_postdate_idx` (`postdate` DESC, `picture1` ASC, `visible` ASC, `deleted` ASC),
ADD INDEX `pictures_news_deleted_idx` (`picture1` ASC, `picture2` ASC, `news` ASC, `deleted` ASC),
ADD INDEX `teaser_picture_visible_deleted_location_postdate_idx` (`postdate` DESC, `picture1` ASC, `visible` ASC, `deleted` ASC, `location` ASC),
ADD INDEX `news_location_deleted_visible_idx` (`news` ASC, `deleted` ASC, `visible` ASC, `location` ASC),
ADD INDEX `pictures_news_location_deleted_visible_idx` (`picture1` ASC, `picture2` ASC, `news` ASC, `location` ASC, `deleted` ASC, `visible` ASC),
ADD INDEX `deleted_visible_featured_postdate_idx` (`postdate` DESC, `deleted` ASC, `visible` ASC, `featured` ASC),
ADD INDEX `news_picture_deleted_visible_featured_postdate_idx` (`postdate` DESC, `picture2` ASC, `deleted` ASC, `visible` ASC, `featured` ASC),
ADD INDEX `location_deleted_visible_featured_postdate_idx` (`postdate` DESC, `location` ASC, `deleted` ASC, `visible` ASC, `featured` ASC),
ADD INDEX `teaser_picture_location_deleted_visible_featured_postdate_idx` (`postdate` DESC, `featured` ASC, `picture2` ASC, `location` ASC, `deleted` ASC, `visible` ASC);
;

ALTER TABLE `news_picture` 
CHANGE COLUMN `picture` `picture` BIGINT(20) NOT NULL AUTO_INCREMENT,
ADD INDEX `picture_idx` (`picture` ASC);
;

ALTER TABLE `album` 
CHANGE COLUMN `album` `album` BIGINT(20) NOT NULL AUTO_INCREMENT,
CHANGE COLUMN `author` `author` BIGINT(20) NOT NULL ,
CHANGE COLUMN `admin` `admin` BIGINT(20) NULL DEFAULT NULL ,
CHANGE COLUMN `location` `location` BIGINT(20) NOT NULL ,
ADD INDEX `album_idx` (`album` DESC),
ADD INDEX `visible_idx` (`visible` ASC),
ADD INDEX `deleted_idx` (`deleted` ASC),
ADD INDEX `visible_deleted_idx` (`visible` ASC, `deleted` ASC),
ADD INDEX `album_deleted_idx` (`album` ASC, `deleted` ASC),
ADD INDEX `visible_deleted_album_idx` (`visible` ASC, `deleted` ASC, `album` DESC),
ADD INDEX `visible_deleted_location_idx` (`visible` ASC, `deleted` ASC, `location` ASC),
ADD INDEX `visible_deleted_location_album_idx` (`visible` ASC, `deleted` ASC, `location` ASC, `album` DESC);
;

ALTER TABLE `picture` 
CHANGE COLUMN `album` `album` BIGINT(20) NOT NULL ,
ADD INDEX `picture_idx` (`picture` ASC),
ADD INDEX `filename_idx` (`filename` ASC),
ADD INDEX `album_filename_idx` (`album` ASC, `filename` ASC),
ADD INDEX `album_picture_idx` (`album` ASC, `picture` ASC),
ADD INDEX `deleted_idx` (`deleted` ASC),
ADD INDEX `album_deleted_idx` (`album` ASC, `deleted` ASC),
ADD INDEX `album_deleted_filename_idx` (`filename` ASC, `album` ASC, `deleted` ASC),
ADD INDEX `visible_idx` (`visible` ASC),
ADD INDEX `album_deleted_visible_idx` (`album` ASC, `deleted` ASC, `visible` ASC),
ADD INDEX `album_deleted_visible_filename_idx` (`album` ASC, `filename` ASC, `deleted` ASC, `visible` ASC);
;

ALTER TABLE `navigation` 
CHANGE COLUMN `id` `id` BIGINT(20) NOT NULL AUTO_INCREMENT ,
ADD INDEX `module_idx` (`module` ASC),
ADD INDEX `id_idx` (`id` ASC),
ADD INDEX `module_id_idx` (`module` ASC, `id` ASC),
ADD INDEX `type_idx` (`type` ASC),
ADD INDEX `pos_idx` (`pos` ASC),
ADD INDEX `pos_module_type_idx` (`pos` ASC, `module` ASC, `type` ASC),
ADD INDEX `id_type_idx` (`id` ASC, `type` ASC),
ADD INDEX `pos_type_idx` (`pos` ASC, `type` ASC);
;

ALTER TABLE `rights` 
CHANGE COLUMN `role` `role` BIGINT(20) NOT NULL ,
CHANGE COLUMN `location` `location` BIGINT(20) NOT NULL ,
ADD INDEX `read_idx` (`read` ASC),
ADD INDEX `write_idx` (`write` ASC),
ADD INDEX `extended_idx` (`extended` ASC),
ADD INDEX `admin_idx` (`admin` ASC),
ADD INDEX `role_location_read_idx` (`role` ASC, `location` ASC, `read` ASC),
ADD INDEX `role_location_idx` (`role` ASC, `location` ASC),
ADD INDEX `read_write_extended_admin_idx` (`read` ASC, `write` ASC, `extended` ASC, `admin` ASC),
ADD INDEX `role_admin_idx` (`role` ASC, `admin` ASC);
;

ALTER TABLE `registration_tos` 
CHANGE COLUMN `id` `id` BIGINT(20) NULL DEFAULT NULL;
;

ALTER TABLE `module`
ADD INDEX `file_idx` (`file` ASC);
;

ALTER TABLE `rights_module` 
CHANGE COLUMN `role` `role` BIGINT(20) NOT NULL,
ADD INDEX `role_module_idx` (`role` ASC, `module` ASC),
ADD INDEX `read_idx` (`read` ASC),
ADD INDEX `write_idx` (`write` ASC),
ADD INDEX `extended_idx` (`extended` ASC),
ADD INDEX `admin_idx` (`admin` ASC),
ADD INDEX `read_write_extended_admin_idx` (`read` ASC, `write` ASC, `extended` ASC, `admin` ASC),
ADD INDEX `role_admin_idx` (`role` ASC, `admin` ASC);
;

ALTER TABLE `role_editor` 
CHANGE COLUMN `master` `master` BIGINT(20) NOT NULL ,
CHANGE COLUMN `slave` `slave` BIGINT(20) NOT NULL,
ADD INDEX `master_slave_idx` (`master` ASC, `slave` ASC);
;

ALTER TABLE `stdroles` 
CHANGE COLUMN `guest` `guest` BIGINT(20) NOT NULL ,
CHANGE COLUMN `user` `user` BIGINT(20) NOT NULL;
;

ALTER TABLE `homepage` 
CHANGE COLUMN `homepage` `homepage` BIGINT(20) NOT NULL;
;

ALTER TABLE `rights_board` 
CHANGE COLUMN `role` `role` BIGINT(20) NOT NULL ,
CHANGE COLUMN `board` `board` BIGINT(20) NOT NULL,
ADD INDEX `read_idx` (`read` ASC),
ADD INDEX `write_idx` (`write` ASC),
ADD INDEX `extended_idx` (`extended` ASC),
ADD INDEX `admin_idx` (`admin` ASC),
ADD INDEX `read_write_extended_admin_idx` (`read` ASC, `write` ASC, `extended` ASC, `admin` ASC),
ADD INDEX `role_board_conj_idx` (`role` ASC, `board` ASC),
ADD INDEX `role_board_read_write_extended_admin_idx` (`role` ASC, `board` ASC, `read` ASC, `write` ASC, `extended` ASC, `admin` ASC),
ADD INDEX `board_read_write_extended_admin_idx` (`board` ASC, `read` ASC, `write` ASC, `extended` ASC, `admin` ASC);
;

ALTER TABLE `board` 
CHANGE COLUMN `board` `board` BIGINT(20) NOT NULL AUTO_INCREMENT ,
CHANGE COLUMN `location` `location` BIGINT(20) NOT NULL ,
ADD INDEX `type_idx` (`type` ASC),
ADD INDEX `pos_idx` (`pos` ASC),
ADD INDEX `type_location_pos_idx` (`type` ASC, `location` ASC, `pos` ASC),
ADD INDEX `board_idx` (`board` ASC),
ADD INDEX `board_type_idx` (`board` ASC, `type` ASC),
ADD INDEX `type_pos_idx` (`type` ASC, `pos` ASC);
;

ALTER TABLE `board_operator` 
CHANGE COLUMN `board` `board` BIGINT(20) NOT NULL ,
CHANGE COLUMN `user` `user` BIGINT(20) NOT NULL ,
ADD INDEX `board_user_idx` (`board` ASC, `user` ASC);
;

ALTER TABLE `thread` 
CHANGE COLUMN `thread` `thread` BIGINT(20) NOT NULL AUTO_INCREMENT ,
CHANGE COLUMN `board` `board` BIGINT(20) NOT NULL ,
CHANGE COLUMN `author` `author` BIGINT(20) NOT NULL ,
CHANGE COLUMN `lastpost` `lastpost` BIGINT(20) NOT NULL ,
ADD INDEX `thread_idx` (`thread` ASC),
ADD INDEX `type_idx` (`type` ASC),
ADD INDEX `type_board_idx` (`type` ASC, `board` ASC),
ADD INDEX `thread_type_board_idx` (`thread` ASC, `type` ASC, `board` ASC),
ADD INDEX `thread_type_idx` (`thread` ASC, `type` ASC),
ADD INDEX `lastpost_type_board_idx` (`lastpost` ASC, `type` ASC, `board` ASC);
;

ALTER TABLE `post` 
CHANGE COLUMN `post` `post` BIGINT(20) NOT NULL AUTO_INCREMENT ,
CHANGE COLUMN `author` `author` BIGINT(20) NOT NULL ,
CHANGE COLUMN `thread` `thread` BIGINT(20) NOT NULL ,
CHANGE COLUMN `operator` `operator` BIGINT(20) NULL DEFAULT NULL ,
ADD INDEX `deleted_idx` (`deleted` ASC),
ADD INDEX `post_idx` (`post` ASC),
ADD INDEX `thread_post_idx` (`post` ASC, `thread` ASC),
ADD INDEX `date_idx` (`date` DESC),
ADD INDEX `deleted_date_idx` (`date` DESC, `deleted` ASC),
ADD INDEX `thread_deleted_date_idx` (`date` DESC, `thread` ASC, `deleted` ASC),
ADD INDEX `thread_deleted_idx` (`thread` ASC, `deleted` ASC),
ADD INDEX `date_asc_idx` (`date` ASC),
ADD INDEX `thread_deleted_date_asc_idx` (`date` ASC, `thread` ASC, `deleted` ASC),
ADD INDEX `post_deleted_idx` (`post` ASC, `deleted` ASC),
ADD INDEX `operator_idx` (`operator` ASC),
ADD INDEX `lastedit_idx` (`lastedit` ASC),
ADD INDEX `post_date_idx` (`date` DESC, `post` ASC);
;

ALTER TABLE `general` 
CHANGE COLUMN `id` `id` BIGINT(20) NOT NULL AUTO_INCREMENT FIRST,
ADD INDEX `tag_idx` (`tag` ASC),
ADD INDEX `id_idx` (`id` ASC),
ADD INDEX `tag_id_idx` (`tag` ASC, `id` ASC);
;

ALTER TABLE `location` 
CHANGE COLUMN `id` `id` BIGINT(20) NOT NULL AUTO_INCREMENT ,
ADD INDEX `tag_idx` (`tag` ASC),
ADD INDEX `id_idx` (`id` ASC),
ADD INDEX `tag_id_idx` (`tag` ASC, `id` ASC);
;

ALTER TABLE `news_tag` 
CHANGE COLUMN `tag` `tag` BIGINT(20) NOT NULL ,
CHANGE COLUMN `news` `news` BIGINT(20) NOT NULL ,
DROP INDEX `tag_news_idx` ,
ADD INDEX `news_idx` (`news` ASC),
ADD INDEX `tag_idx` (`tag` ASC),
ADD INDEX `type_idx` (`type` ASC),
ADD INDEX `tag_type_idx` (`tag` ASC, `type` ASC),
ADD INDEX `tag_type_news_idx` (`tag` ASC, `news` ASC, `type` ASC),
ADD INDEX `type_news_idx` (`news` ASC, `type` ASC);
;

ALTER TABLE `band` 
CHANGE COLUMN `id` `id` BIGINT(20) NOT NULL AUTO_INCREMENT ,
ADD INDEX `tag_idx` (`tag` ASC),
ADD INDEX `tag_id_idx` (`tag` ASC, `id` ASC);
;

ALTER TABLE `event` 
CHANGE COLUMN `event` `event` BIGINT(20) NOT NULL AUTO_INCREMENT ,
CHANGE COLUMN `author` `author` BIGINT(20) NOT NULL ;

ALTER TABLE `event_location` 
CHANGE COLUMN `location` `location` BIGINT(20) NOT NULL ,
CHANGE COLUMN `event` `event` BIGINT(20) NOT NULL;
;

ALTER TABLE `event_band` 
CHANGE COLUMN `band` `band` BIGINT(20) NOT NULL ,
CHANGE COLUMN `event` `event` BIGINT(20) NOT NULL;
;

ALTER TABLE `attachment` 
CHANGE COLUMN `file` `file` BIGINT(20) NOT NULL AUTO_INCREMENT ,
ADD INDEX `file_idx` (`file` ASC),
ADD INDEX `servername_idx` (`servername` ASC),
ADD INDEX `key_idx` (`key` ASC),
ADD INDEX `temporary_idx` (`temporary` ASC);
;

ALTER TABLE `post_attachment` 
CHANGE COLUMN `post` `post` BIGINT(20) NOT NULL ,
CHANGE COLUMN `file` `file` BIGINT(20) NOT NULL,
ADD INDEX `file_post_idx` (`post` ASC, `file` ASC);
;

ALTER TABLE `newsletter_entry` 
CHANGE COLUMN `role` `role` BIGINT(20) NOT NULL;
;

ALTER TABLE `app` 
CHANGE COLUMN `id` `id` BIGINT(20) NOT NULL AUTO_INCREMENT ,
ADD INDEX `id_idx` (`id` ASC),
ADD INDEX `key_idx` (`key` ASC);
;

ALTER TABLE `pushtoken` 
CHANGE COLUMN `id` `id` BIGINT(20) NOT NULL AUTO_INCREMENT ,
ADD INDEX `type_idx` (`type` ASC),
ADD INDEX `auth_idx` (`auth` ASC),
ADD INDEX `key_idx` (`key` ASC),
;

ALTER TABLE `user` 
ADD CONSTRAINT `user_role`
  FOREIGN KEY (`role`)
  REFERENCES `role` (`role`)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION;

ALTER TABLE `email` 
ADD CONSTRAINT `user_email`
  FOREIGN KEY (`user`)
  REFERENCES `user` (`user`)
  ON DELETE RESTRICT
  ON UPDATE CASCADE;

ALTER TABLE `contact` 
ADD CONSTRAINT `user_contact`
  FOREIGN KEY (`user`)
  REFERENCES `user` (`user`)
  ON DELETE RESTRICT
  ON UPDATE CASCADE;

ALTER TABLE `user_album` 
ADD CONSTRAINT `user_album`
  FOREIGN KEY (`user`)
  REFERENCES `user` (`user`)
  ON DELETE RESTRICT
  ON UPDATE CASCADE;

ALTER TABLE `user_picture` 
ADD CONSTRAINT `user_picture_album`
  FOREIGN KEY (`album`)
  REFERENCES `user_album` (`album`)
  ON DELETE RESTRICT
  ON UPDATE CASCADE;

ALTER TABLE `news` 
ADD CONSTRAINT `author_user`
  FOREIGN KEY (`author`)
  REFERENCES `user` (`user`)
  ON DELETE RESTRICT
  ON UPDATE CASCADE,
ADD CONSTRAINT `admin_user`
  FOREIGN KEY (`admin`)
  REFERENCES `user` (`user`)
  ON DELETE RESTRICT
  ON UPDATE CASCADE,
ADD CONSTRAINT `teaser_picture`
  FOREIGN KEY (`picture1`)
  REFERENCES `news_picture` (`picture`)
  ON DELETE RESTRICT
  ON UPDATE CASCADE,
ADD CONSTRAINT `news_picture`
  FOREIGN KEY (`picture2`)
  REFERENCES `news_picture` (`picture`)
  ON DELETE RESTRICT
  ON UPDATE CASCADE;

ALTER TABLE `album` 
ADD CONSTRAINT `album_author`
  FOREIGN KEY (`author`)
  REFERENCES `user` (`user`)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION,
ADD CONSTRAINT `album_admin`
  FOREIGN KEY (`admin`)
  REFERENCES `user` (`user`)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION,
ADD CONSTRAINT `album_location`
  FOREIGN KEY (`location`)
  REFERENCES `navigation` (`id`)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION;

ALTER TABLE `picture` 
ADD CONSTRAINT `picture_album`
  FOREIGN KEY (`album`)
  REFERENCES `album` (`album`)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION;

ALTER TABLE `rights` 
ADD CONSTRAINT `rights_role`
  FOREIGN KEY (`role`)
  REFERENCES `role` (`role`)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION,
ADD CONSTRAINT `rights_navigation`
  FOREIGN KEY (`location`)
  REFERENCES `navigation` (`id`)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION;

ALTER TABLE `registration_tos` 
ADD CONSTRAINT `tos_navigation`
  FOREIGN KEY (`id`)
  REFERENCES `navigation` (`id`)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION;

ALTER TABLE `rights_module` 
ADD CONSTRAINT `rights_role`
  FOREIGN KEY (`role`)
  REFERENCES `role` (`role`)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION;

ALTER TABLE `role_editor` 
ADD CONSTRAINT `master.role`
  FOREIGN KEY (`master`)
  REFERENCES `role` (`role`)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION,
ADD CONSTRAINT `slave.role`
  FOREIGN KEY (`slave`)
  REFERENCES `role` (`role`)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION;

ALTER TABLE `stdroles` 
ADD CONSTRAINT `guest`
  FOREIGN KEY (`guest`)
  REFERENCES `role` (`role`)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION,
ADD CONSTRAINT `user`
  FOREIGN KEY (`user`)
  REFERENCES `role` (`role`)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION;

ALTER TABLE `homepage` 
ADD CONSTRAINT `standard_link`
  FOREIGN KEY (`homepage`)
  REFERENCES `navigation` (`id`)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION;

ALTER TABLE `rights_board` 
ADD CONSTRAINT `board_role`
  FOREIGN KEY (`role`)
  REFERENCES `role` (`role`)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION,
ADD CONSTRAINT `role_board`
  FOREIGN KEY (`board`)
  REFERENCES `board` (`board`)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION;

ALTER TABLE `board` 
ADD CONSTRAINT `board_location`
  FOREIGN KEY (`location`)
  REFERENCES `navigation` (`id`)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION;

ALTER TABLE `board_operator` 
ADD CONSTRAINT `operator_board`
  FOREIGN KEY (`board`)
  REFERENCES `board` (`board`)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION,
ADD CONSTRAINT `operator_user`
  FOREIGN KEY (`user`)
  REFERENCES `user` (`user`)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION;

ALTER TABLE `thread` 
ADD CONSTRAINT `thread_board`
  FOREIGN KEY (`board`)
  REFERENCES `board` (`board`)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION,
ADD CONSTRAINT `thread_author`
  FOREIGN KEY (`author`)
  REFERENCES `user` (`user`)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION,
ADD CONSTRAINT `thread_post`
  FOREIGN KEY (`lastpost`)
  REFERENCES `post` (`post`)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION;

ALTER TABLE `post` 
ADD CONSTRAINT `post_author`
  FOREIGN KEY (`author`)
  REFERENCES `user` (`user`)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION,
ADD CONSTRAINT `post_thread`
  FOREIGN KEY (`thread`)
  REFERENCES `thread` (`thread`)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION,
ADD CONSTRAINT `post_operator`
  FOREIGN KEY (`operator`)
  REFERENCES `user` (`user`)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION;

ALTER TABLE `general` 
ADD CONSTRAINT `general_news`
  FOREIGN KEY (`id`)
  REFERENCES `news_tag` (`tag`)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION;

ALTER TABLE `location` 
ADD CONSTRAINT `location_tag`
  FOREIGN KEY (`id`)
  REFERENCES `news_tag` (`tag`)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION;

ALTER TABLE `news_tag` 
ADD CONSTRAINT `tag_news`
  FOREIGN KEY (`news`)
  REFERENCES `news` (`news`)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION;

ALTER TABLE `band` 
ADD CONSTRAINT `band_tag`
  FOREIGN KEY (`id`)
  REFERENCES `news_tag` (`tag`)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION;

ALTER TABLE `event` 
ADD CONSTRAINT `event_author`
  FOREIGN KEY (`author`)
  REFERENCES `user` (`user`)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION;

ALTER TABLE `event_location` 
ADD CONSTRAINT `el_location`
  FOREIGN KEY (`location`)
  REFERENCES `location` (`id`)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION,
ADD CONSTRAINT `el_event`
  FOREIGN KEY (`event`)
  REFERENCES `event` (`event`)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION;

ALTER TABLE `event_band` 
ADD CONSTRAINT `eb_band`
  FOREIGN KEY (`band`)
  REFERENCES `band` (`id`)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION,
ADD CONSTRAINT `eb_event`
  FOREIGN KEY (`event`)
  REFERENCES `event` (`event`)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION;

ALTER TABLE `post_attachment` 
ADD CONSTRAINT `pa_post`
  FOREIGN KEY (`post`)
  REFERENCES `post` (`post`)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION,
ADD CONSTRAINT `pa_file`
  FOREIGN KEY (`file`)
  REFERENCES `attachment` (`file`)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION;

ALTER TABLE `newsletter_entry` 
ADD CONSTRAINT `newsletter_role`
  FOREIGN KEY (`role`)
  REFERENCES `role` (`role`)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION;

ALTER TABLE `news`
DROP FOREIGN KEY `teaser_picture`,
DROP FOREIGN KEY `news_picture`;

ALTER TABLE `general` 
DROP FOREIGN KEY `general_news`;

ALTER TABLE `location`
DROP FOREIGN KEY `location_tag`;

ALTER TABLE `band`
DROP FOREIGN KEY `band_tag`;

ALTER TABLE `user` 
DROP INDEX `user_role_idx` ;

ALTER TABLE `navigation` 
DROP INDEX `link_map_idx` ,
DROP INDEX `link_category_idx` ;

ALTER TABLE `rights_module` 
ADD CONSTRAINT `rights_role`
  FOREIGN KEY (`role`)
  REFERENCES `role` (`role`)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION,
ADD CONSTRAINT `rights_module_role`
  FOREIGN KEY (`module`)
  REFERENCES `module` (`file`)
  ON DELETE NO ACTION
  ON UPDATE NO ACTION;
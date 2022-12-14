ALTER TABLE `album` ENGINE=InnoDB;
ALTER TABLE `attachment` ENGINE=InnoDB;
ALTER TABLE `band` ENGINE=InnoDB;
ALTER TABLE `board` ENGINE=InnoDB;
ALTER TABLE `board_operator` ENGINE=InnoDB;
ALTER TABLE `contact` ENGINE=InnoDB;
ALTER TABLE `contact_form` ENGINE=InnoDB;
ALTER TABLE `email` ENGINE=InnoDB;
ALTER TABLE `event` ENGINE=InnoDB;
ALTER TABLE `event_band` ENGINE=InnoDB;
ALTER TABLE `event_location` ENGINE=InnoDB;
ALTER TABLE `general` ENGINE=InnoDB;
ALTER TABLE `homepage` ENGINE=InnoDB;
ALTER TABLE `location` ENGINE=InnoDB;
ALTER TABLE `module` ENGINE=InnoDB;
ALTER TABLE `navigation` ENGINE=InnoDB;
ALTER TABLE `news` ENGINE=InnoDB;
ALTER TABLE `news_picture` ENGINE=InnoDB;
ALTER TABLE `news_tag` ENGINE=InnoDB;
ALTER TABLE `newsletter_configuration` ENGINE=InnoDB;
ALTER TABLE `newsletter_entry` ENGINE=InnoDB;
ALTER TABLE `picture` ENGINE=InnoDB;
ALTER TABLE `post` ENGINE=InnoDB;
ALTER TABLE `post_attachment` ENGINE=InnoDB;
ALTER TABLE `registration_tos` ENGINE=InnoDB;
ALTER TABLE `rights` ENGINE=InnoDB;
ALTER TABLE `rights_board` ENGINE=InnoDB;
ALTER TABLE `rights_module` ENGINE=InnoDB;
ALTER TABLE `role` ENGINE=InnoDB;
ALTER TABLE `role_editor` ENGINE=InnoDB;
ALTER TABLE `stdroles` ENGINE=InnoDB;
ALTER TABLE `thread` ENGINE=InnoDB;
ALTER TABLE `user` ENGINE=InnoDB;
ALTER TABLE `user_album` ENGINE=InnoDB;
ALTER TABLE `user_picture` ENGINE=InnoDB;

ALTER TABLE `news` DROP INDEX `title_search`;
ALTER TABLE `news` DROP INDEX `headline_search`;
ALTER TABLE `news` DROP INDEX `teaser_search`;
ALTER TABLE `news` DROP INDEX `text_search`;

ALTER TABLE `album` ADD CONSTRAINT `album_author` FOREIGN KEY (`author`) REFERENCES `user` (`user`) ON DELETE NO ACTION ON UPDATE NO ACTION;
ALTER TABLE `album` ADD CONSTRAINT `album_admin` FOREIGN KEY (`admin`) REFERENCES `user` (`user`) ON DELETE NO ACTION ON UPDATE NO ACTION;
ALTER TABLE `album` ADD CONSTRAINT `album_location` FOREIGN KEY (`location`) REFERENCES `navigation` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;
ALTER TABLE `band` ADD CONSTRAINT `band_tag` FOREIGN KEY (`id`) REFERENCES `news_tag` (`tag`) ON DELETE NO ACTION ON UPDATE NO ACTION;
ALTER TABLE `board` ADD CONSTRAINT `board_location` FOREIGN KEY (`location`) REFERENCES `navigation` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;
ALTER TABLE `board_operator` ADD CONSTRAINT `operator_board` FOREIGN KEY (`board`) REFERENCES `board` (`board`) ON DELETE NO ACTION ON UPDATE NO ACTION;
ALTER TABLE `board_operator` ADD CONSTRAINT `operator_user` FOREIGN KEY (`user`) REFERENCES `user` (`user`) ON DELETE NO ACTION ON UPDATE NO ACTION;
ALTER TABLE `contact` ADD CONSTRAINT `user_contact` FOREIGN KEY (`user`) REFERENCES `user` (`user`) ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE `contact` ADD CONSTRAINT `contact_form` FOREIGN KEY (`contact_form`) REFERENCES `contact_form` (`contact_form`) ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE `email` ADD CONSTRAINT `user_email` FOREIGN KEY (`user`) REFERENCES `user` (`user`) ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE `event` ADD CONSTRAINT `event_author` FOREIGN KEY (`author`) REFERENCES `user` (`user`) ON DELETE NO ACTION ON UPDATE NO ACTION;
ALTER TABLE `event_band` ADD CONSTRAINT `eb_band` FOREIGN KEY (`band`) REFERENCES `band` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;
ALTER TABLE `event_band` ADD CONSTRAINT `eb_event` FOREIGN KEY (`event`) REFERENCES `event` (`event`) ON DELETE NO ACTION ON UPDATE NO ACTION;
ALTER TABLE `event_location` ADD CONSTRAINT `el_location` FOREIGN KEY (`location`) REFERENCES `location` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;
ALTER TABLE `event_location` ADD CONSTRAINT `el_event` FOREIGN KEY (`event`) REFERENCES `event` (`event`) ON DELETE NO ACTION ON UPDATE NO ACTION;
ALTER TABLE `general` ADD CONSTRAINT `general_news` FOREIGN KEY (`id`) REFERENCES `news_tag` (`tag`) ON DELETE NO ACTION ON UPDATE NO ACTION;
ALTER TABLE `homepage` ADD CONSTRAINT `standard_link` FOREIGN KEY (`homepage`) REFERENCES `navigation` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;
ALTER TABLE `location` ADD CONSTRAINT `location_tag` FOREIGN KEY (`id`) REFERENCES `news_tag` (`tag`) ON DELETE NO ACTION ON UPDATE NO ACTION;
ALTER TABLE `navigation` ADD CONSTRAINT `link_category` FOREIGN KEY (`category`) REFERENCES `navigation` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;
ALTER TABLE `navigation` ADD CONSTRAINT `link_map` FOREIGN KEY (`maps_to`) REFERENCES `navigation` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;
ALTER TABLE `news` ADD CONSTRAINT `author_user` FOREIGN KEY (`author`) REFERENCES `user` (`user`) ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE `news` ADD CONSTRAINT `admin_user` FOREIGN KEY (`admin`) REFERENCES `user` (`user`) ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE `news` ADD CONSTRAINT `teaser_picture` FOREIGN KEY (`picture1`) REFERENCES `news_picture` (`picture`) ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE `news` ADD CONSTRAINT `news_picture` FOREIGN KEY (`picture2`) REFERENCES `news_picture` (`picture`) ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE `news_tag` ADD CONSTRAINT `tag_news` FOREIGN KEY (`news`) REFERENCES `news` (`news`) ON DELETE NO ACTION ON UPDATE NO ACTION;
ALTER TABLE `newsletter_entry` ADD CONSTRAINT `newsletter_email` FOREIGN KEY (`confirm_id`) REFERENCES `email` (`confirm_id`) ON DELETE NO ACTION ON UPDATE NO ACTION;
ALTER TABLE `newsletter_entry` ADD CONSTRAINT `newsletter_role` FOREIGN KEY (`role`) REFERENCES `role` (`role`) ON DELETE NO ACTION ON UPDATE NO ACTION;
ALTER TABLE `picture` ADD CONSTRAINT `picture_album` FOREIGN KEY (`album`) REFERENCES `album` (`album`) ON DELETE NO ACTION ON UPDATE NO ACTION;
ALTER TABLE `post` ADD CONSTRAINT `post_author` FOREIGN KEY (`author`) REFERENCES `user` (`user`) ON DELETE NO ACTION ON UPDATE NO ACTION;
ALTER TABLE `post` ADD CONSTRAINT `post_thread` FOREIGN KEY (`thread`) REFERENCES `thread` (`thread`) ON DELETE NO ACTION ON UPDATE NO ACTION;
ALTER TABLE `post` ADD CONSTRAINT `post_operator` FOREIGN KEY (`operator`) REFERENCES `user` (`user`) ON DELETE NO ACTION ON UPDATE NO ACTION;
ALTER TABLE `post_attachment` ADD CONSTRAINT `pa_post` FOREIGN KEY (`post`) REFERENCES `post` (`post`) ON DELETE NO ACTION ON UPDATE NO ACTION;
ALTER TABLE `post_attachment` ADD CONSTRAINT `pa_file` FOREIGN KEY (`file`) REFERENCES `attachment` (`file`) ON DELETE NO ACTION ON UPDATE NO ACTION;
ALTER TABLE `registration_tos` ADD CONSTRAINT `tos_navigation` FOREIGN KEY (`id`) REFERENCES `navigation` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;
ALTER TABLE `rights` ADD CONSTRAINT `rights_role` FOREIGN KEY (`role`) REFERENCES `role` (`role`) ON DELETE NO ACTION ON UPDATE NO ACTION;
ALTER TABLE `rights` ADD CONSTRAINT `rights_navigation` FOREIGN KEY (`location`) REFERENCES `navigation` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION;
ALTER TABLE `rights_board` ADD CONSTRAINT `board_role` FOREIGN KEY (`role`) REFERENCES `role` (`role`) ON DELETE NO ACTION ON UPDATE NO ACTION;
ALTER TABLE `rights_board` ADD CONSTRAINT `role_board` FOREIGN KEY (`board`) REFERENCES `board` (`board`) ON DELETE NO ACTION ON UPDATE NO ACTION;
ALTER TABLE `rights_module` ADD CONSTRAINT `rights_role` FOREIGN KEY (`role`) REFERENCES `role` (`role`) ON DELETE NO ACTION ON UPDATE NO ACTION;
ALTER TABLE `rights_module` ADD CONSTRAINT `rights_module` FOREIGN KEY (`module`) REFERENCES `module` (`name`) ON DELETE NO ACTION ON UPDATE NO ACTION;
ALTER TABLE `role_editor` ADD CONSTRAINT `master.role` FOREIGN KEY (`master`) REFERENCES `role` (`role`) ON DELETE NO ACTION ON UPDATE NO ACTION;
ALTER TABLE `role_editor` ADD CONSTRAINT `slave.role` FOREIGN KEY (`slave`) REFERENCES `role` (`role`) ON DELETE NO ACTION ON UPDATE NO ACTION;
ALTER TABLE `stdroles` ADD CONSTRAINT `guest` FOREIGN KEY (`guest`) REFERENCES `role` (`role`) ON DELETE NO ACTION ON UPDATE NO ACTION;
ALTER TABLE `stdroles` ADD CONSTRAINT `user` FOREIGN KEY (`user`) REFERENCES `role` (`role`) ON DELETE NO ACTION ON UPDATE NO ACTION;
ALTER TABLE `thread` ADD CONSTRAINT `thread_board` FOREIGN KEY (`board`) REFERENCES `board` (`board`) ON DELETE NO ACTION ON UPDATE NO ACTION;
ALTER TABLE `thread` ADD CONSTRAINT `thread_author` FOREIGN KEY (`author`) REFERENCES `user` (`user`) ON DELETE NO ACTION ON UPDATE NO ACTION;
ALTER TABLE `thread` ADD CONSTRAINT `thread_post` FOREIGN KEY (`lastpost`) REFERENCES `post` (`post`) ON DELETE NO ACTION ON UPDATE NO ACTION;
ALTER TABLE `user` ADD CONSTRAINT `user_picture` FOREIGN KEY (`picture`) REFERENCES `user_picture` (`picture`) ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE `user` ADD CONSTRAINT `user_role` FOREIGN KEY (`role`) REFERENCES `role` (`role`) ON DELETE NO ACTION ON UPDATE NO ACTION;
ALTER TABLE `user_album` ADD CONSTRAINT `user_album` FOREIGN KEY (`user`) REFERENCES `user` (`user`) ON DELETE RESTRICT ON UPDATE CASCADE;
ALTER TABLE `user_picture` ADD CONSTRAINT `user_picture_album` FOREIGN KEY (`album`) REFERENCES `user_album` (`album`) ON DELETE RESTRICT ON UPDATE CASCADE;
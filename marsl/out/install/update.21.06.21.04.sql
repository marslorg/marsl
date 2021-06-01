ALTER TABLE `album` 
ADD INDEX `postdate_idx` (`postdate` ASC),
ADD INDEX `visible_deleted_postdate_idx` (`visible` ASC, `deleted` ASC, `postdate` DESC),
ADD INDEX `visible_deleted_location_postdate_idx` (`visible` ASC, `deleted` ASC, `location` ASC, `postdate` DESC);
;

ALTER TABLE `pushtoken` 
ADD INDEX `pushtoken_idx` (`pushtoken` ASC),
ADD INDEX `pushtoken_type_idx` (`type` ASC, `pushtoken` ASC);
;

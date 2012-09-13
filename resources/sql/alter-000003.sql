--
-- Conductor model alter #3
--
-- Philip Graham <philip@zeptech.ca>
-- 2011-11-23
--
-- This alter adds a column to the users table for record a user's openId
-- identity.  It also removes the NOT NULL constraints from the username and
-- password columns since an openId user may not have a username and password.
--

ALTER TABLE `users` ADD COLUMN `oid_identity` varchar(333) UNIQUE;

ALTER TABLE `users` MODIFY COLUMN `username` varchar(128);
ALTER TABLE `users` MODIFY COLUMN `password` varchar(32);

-- Update conductor database version
UPDATE `config_values` SET `value` = '3' WHERE `name` = 'cdt-alter';

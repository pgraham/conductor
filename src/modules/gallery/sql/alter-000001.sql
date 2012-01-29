--
-- Gallery Modules Alter #1
--
-- Philip Graham <philip@zeptech.ca>
-- 2012-01-26
--
-- This alter creates the initial schema for the module
--

CREATE TABLE `photos` (
  `id`             integer       AUTO_INCREMENT PRIMARY KEY,
  `imgtype`        varchar(3)    NOT NULL,
  `caption`        TEXT          NULL DEFAULT NULL,
  `categories_id`  integer       NULL DEFAULT NULL
);

CREATE TABLE `categories` (
  `id`             integer       AUTO_INCREMENT PRIMARY KEY,
  `name`           varchar(256)  NULL DEFAULT NULL UNIQUE
);

INSERT INTO `config_values` (`name`, `value`) VALUES ('module-gallery-alter', '1');

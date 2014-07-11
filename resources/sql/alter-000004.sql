--
-- Conductor model alter #4
--
-- Philip Graham <philip@zeptch.ca>
-- 2012-01-01
--
-- This alter adds initial support for content management and localization.
--

CREATE TABLE "content" (
  "id"             integer      AUTO_INCREMENT PRIMARY KEY,
  "key"            varchar(128) NOT NULL UNIQUE,
  "txt"            text         NOT NULL DEFAULT ''
);

--
-- Conductor model alter #2
--
-- Philip Graham <philip@zeptech.ca>
-- 2011-08-28
--
-- This alters adds a Visitor model which is similar to a session but more
-- permanent and not associated with a user.  The granularity of a visitor is
-- one per browser per device.
--

CREATE TABLE "visitors" (
  "id"             integer      AUTO_INCREMENT PRIMARY KEY,
  "key"            varchar(32)  NOT NULL UNIQUE
);

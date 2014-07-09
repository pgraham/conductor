-- This file contains the SQL for creating the tables for the models defined in
-- this directory

-- ConfigValue
CREATE TABLE "config_values" (
  "id"             integer      AUTO_INCREMENT PRIMARY KEY,
  "name"           varchar(255) NOT NULL UNIQUE,
  "value"          varchar(255) NULL
);

-- Permission
CREATE TABLE "permissions" (
  "id"             integer      AUTO_INCREMENT PRIMARY KEY,
  "name"           varchar(255) NOT NULL UNIQUE
);
INSERT INTO "permissions" ("name") VALUES ('cdt-admin');

-- Session
CREATE TABLE "session" (
  "id"             integer      AUTO_INCREMENT PRIMARY KEY,
  "sess_key"       varchar(32)  NOT NULL UNIQUE,
  "last_access"    bigint       NOT NULL,
  "users_id"       integer      NULL
);

-- User
CREATE TABLE "users" (
  "id"             integer      AUTO_INCREMENT PRIMARY KEY,
  "username"       varchar(128) NOT NULL UNIQUE,
  "password"       varchar(32)  NOT NULL
);
INSERT INTO "users" ("username", "password")
  VALUES ('pgraham', md5('weedy gumbo smolt implant crotch'));

-- UserPermission
CREATE TABLE "users_permissions_link" (
  "id"             integer      AUTO_INCREMENT PRIMARY KEY,
  "users_id"       integer      NOT NULL,
  "permissions_id" integer      NOT NULL,
  "level"          varchar(8)   NOT NULL
);
INSERT INTO "users_permissions_link" ("users_id", "permissions_id", "level")
  VALUES (
    (SELECT "id" FROM "users" WHERE "username" = 'pgraham'),
    (SELECT "id" FROM "permissions" WHERE "name" = 'cdt-admin'),
    'write');

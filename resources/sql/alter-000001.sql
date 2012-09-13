--
-- Conductor model alter #1
--
-- Philip Graham <philip@zeptech.ca>
-- 2011-06-23
--
-- This alter adds an editable column to the config_values table indicating
-- whether or not the configuration value is editable in the admin interface.
-- The alter also creates an applied alter config_value (not-editable) which can
-- be used to drive a database update script.  All subsequent alters should
-- update this number.
--

-- The column is created with a default value of 1 so that existing
-- configuration values remain editable.  Once the default of 1 has been
-- assigned to existing rows, the default is changed to 0 for new rows.
ALTER TABLE `config_values` ADD COLUMN `editable` SMALLINT NOT NULL DEFAULT 1;

ALTER TABLE `config_values` ALTER `editable` SET DEFAULT 0;

-- Insert a configuration value for tracking the conductor alters that have
-- been applied to the database
INSERT INTO `config_values` (`name`, `value`) VALUES ('cdt-alter', '1');

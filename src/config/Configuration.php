<?php
/*
 * Copyright (c) 2014, Philip Graham
 * All rights reserved.
 *
 *
 * This file is part of Conductor. For the full copyright and license information
 * please view the LICENSE file that was distributed with this source code.
 */
namespace zpt\cdt\config;

use Symfony\Component\Yaml\Parser as YamlParser;
use Symfony\Component\Yaml\Exception\ParserException as YamlParserException;
use InvalidArgumentException;

/**
 * Container for parsed YAML configuration. This avoids having to run the parser
 * multiple times when more than one config subset is needed.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class Configuration {

	private static $parser;
	private static $yaml = [];

	/**
	 * Get the parsed configuration for the site at the specified root.
	 *
	 * @param string $root
	 *   The root of the site for which to retrieve parsed configuration.
	 * @return mixed A PHP value
	 * @throws InvalidArgumentException
	 */
	public static function get($root) {
		if (!isset(self::$yaml[$root])) {
			self::$yaml[$root] = self::parse("$root/conductor.yml");
		}
		return self::$yaml[$root];
	}

	/**
	 * Parse the configuration at the specified path.
	 *
	 * @param string $configPath
	 *   The path to the configuration file.
	 * @return mixed
	 * @throws InvalidArgumentException
	 */
	public static function parse($configPath) {
			if (!file_exists($configPath)) {
				throw new InvalidArgumentException(
					"Configuration file $configPath does not exist."
				);
			}

			if (self::$parser === null) {
				self::$parser = new YamlParser();
			}

			try {
				$config = self::$parser->parse(file_get_contents($configPath));
			} catch (YamlParserException $e) {
				throw new InvalidArgumentException(
					"Unable to parse configuration file $configPath: " . $e->getMessage()
				);
			}

			return $config;
	}
}

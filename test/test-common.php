<?php
/**
 * =============================================================================
 * Copyright (c) 2011, Philip Graham
 * All rights reserved.
 *
 * This file is part of Conductor and is licensed by the Copyright holder under
 * the 3-clause BSD License.  The full text of the license can be found in the
 * LICENSE.txt file included in the root directory of this distribution or at
 * the link below.
 * =============================================================================
 *
 * This file sets up the environment for running tests.
 *
 * @license http://www.opensource.org/licenses/bsd-license.php
 */

$loader = require __DIR__ . '/../vendor/autoload.php';
$loader->add('zpt\dyn', __DIR__ . '/target');
$loader->addPsr4('zpt\\orm\\test\\',
                 __DIR__ . '/../vendor/zeptech/clarinet/test/common');

function getComposerLoader() {
	global $loader;
	return $loader;
}

class ComposerLoaderContainer {

	public static $loader;

}
ComposerLoaderContainer::$loader = $loader;

return $loader;

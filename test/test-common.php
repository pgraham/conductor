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
 * @package conductor/test
 */

/*
 * -----------------------------------------------------------------------------
 * SINCE CONDUCTOR RELIES ON REED, CLARINET, BASSOON AND OBOE, IN ORDER TO TEST
 * PROPERTY WE NEED TO LOAD THESE LIBRARIES' CLASSES.  FOR THIS REASON THE TESTS
 * WON'T RUN UNTIL THESE PATHS POINT TO VALID INSTALLS.
 * -----------------------------------------------------------------------------
 */
define('REED_PATH',     __DIR__ . '/../../reed');
define('BASSOON_PATH',  __DIR__ . '/../../bassoon');
define('CLARINET_PATH', __DIR__ . '/../../clarinet');
define('OBOE_PATH',     __DIR__ . '/../../oboe');

/*
 * -----------------------------------------------------------------------------
 * Include the necessary autoloaders.
 * -----------------------------------------------------------------------------
 */
require_once REED_PATH . '/src/Autoloader.php';
require_once BASSOON_PATH . '/src/Autoloader.php';
require_once CLARINET_PATH . '/src/Autoloader.php';
require_once OBOE_PATH . '/src/Autoloader.php';

// The conductor autoloader and test autoloader
require_once __DIR__ . '/../src/Autoloader.php';
require_once __DIR__ . '/Autoloader.php';

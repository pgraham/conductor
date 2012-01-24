<?php
/**
 * =============================================================================
 * Copyright (c) 2010, Philip Graham
 * All rights reserved.
 *
 * This file is part of Conductor and is licensed by the Copyright holder under
 * the 3-clause BSD License.  The full text of the license can be found in the
 * LICENSE.txt file included in the root directory of this distribution or at
 * the link below.
 * =============================================================================
 *
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
namespace conductor;

/**
 * This class encapsulates configuration for a conductor web site.  Conductor
 * configuration can be split into two categories, path information and
 * resource definition.
 *
 * Path information consists of defined paths for where how the site is layed
 * out in the file system and how the file system maps to the web server.  It
 * consists of:
 *
 *   Root path:     The path to the base directory for the entire site.
 *   Document root: The file system path to the root web accessible directory.
 *   Target path:   Path to the folder where any generated, non-web accessible
 *                  files are located.  For development deployments this
 *                  directory needs to be writeable by the web server.
 *   Web target:    Path to the folder where any generated, web accessible files
 *                  are located.  For development deployments this directory
 *                  needs to be writeable by the web server.
 *   Web root:      Web path to the root of the web site.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */


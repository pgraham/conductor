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
namespace conductor\model;

use \clarinet\model\Relationship;

/**
 * Interface for objects that decorate a DecoratedRelationship.  A
 * RelationshipDecorator instance has a one-to-one relationship with the
 * property it is decorating.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
interface RelationshipDecorator {}

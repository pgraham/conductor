<?php
/*
 * Copyright (c) 2014, Philip Graham
 * All rights reserved.
 *
 * This file is part of Conductor. For the full copyright and license information
 * please view the LICENSE file that was distributed with this source code.
 */
namespace zpt\cdt\html;

use zpt\oobo\Body;
use zpt\oobo\Head;
use zpt\rest\Request;

/**
 * Interface for site classes that provide an HTML page.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
interface HtmlContentProvider {

	public function populateHead(Head $head, Request $request);

	public function populateBody(Body $body, Request $request);
}

<?php
/**
 * Copyright (c) 2013, Philip Graham
 * All rights reserved.
 *
 * This file is part of Conductor and is licensed by the Copyright holder under
 * the 3-clause BSD License.	The full text of the license can be found in the
 * LICENSE.txt file included in the root directory of this distribution or at
 * the link below.
 *
 * @license http://www.opensource.org/licenses/bsd-license.php
 */
namespace zpt\cdt\html;

use \zpt\anno\Annotations;
use \ReflectionClass;

/**
 * This class parses the declared resources required by a page definition.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class PageResourceParser {

	private $page;
	private $template;

	public function __construct($className) {
		// TODO Recent versions of php-annotations will just accept the class name 
		//      and don't need a Reflector instance.
		$pageClass = new ReflectionClass($className);
		$this->page = new Annotations($pageClass);

		if (!isset($this->page['page'])) {
			throw new NotAPageDefinitionException($className);
		}

		if (isset($page['template'])) {
			$templateClass = new ReflectionClass($this->parseTemplateClass);
			$this->template = new Annotations($templateClass);
		} else {
			$this->template = new Annotations();
		}
	}

	public function getCssGroups() {
		$declared = array_merge(
			$this->template->asArray('css'),
			$this->page->asArray('css')
		);

		return array_filter($declared, function ($css) {
			return !String($css)->endsWith('.css');
		});
	}

	/*
	 * Determine if the given template name is absolute or relative and if 
	 * relative append the namespace of the page class.
	 */
	private function getTemplateClass($template, $pageClass) {
		if (strpos($template, '\\') !== false) {
			return $template;
		}

		return substr($pageClass, 0, strrpos($pageClass, '\\') + 1) . $template;
	}
}

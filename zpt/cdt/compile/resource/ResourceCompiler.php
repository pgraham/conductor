<?php
/**
 * Copyright (c) 2012, Philip Graham
 * All rights reserved.
 */
namespace zpt\cdt\compile\resource;

use \zpt\pct\CodeTemplateParser;
use \zpt\util\File;
use \DirectoryIterator;

/**
 * This class compiles a resource directory.	A resource is an image, a css
 * file or a javascript file.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class ResourceCompiler {

	private $lessCompiler;
	private $templateParser;

	/**
	 * Create a new resource compiler instance with optional injected 
	 * dependencies.
	 *
	 * @param CodeTemplateParser $templateParser CodeTemplateParser instance. If 
	 * not provided a default implementation will be instantiated.
	 * @param LessCompiler $lessCompiler LessCompiler instance. If not provided 
	 * a default implementation will be instantiated.
	 */
	public function __construct($templateParser = null, $lessCompiler = null) {
		if ($templateParser === null) {
			$templateParser = new CodeTemplateParser();
		}
		if ($lessCompiler === null) {
			$lessCompiler = new LessCompiler();
		}
		$this->lessCompiler = $lessCompiler;
		$this->templateParser = $templateParser;
	}

	/**
	 * Compile the resource(s) found in the given source path to the specified 
	 * target path. If the specified source is a code template then an array of 
	 * substitution values _must_ be provided.
	 *
	 * @param string $src Path to the resource source.
	 * @param string $target Path to the compilation target.
	 * @param array $value Optional array of substitution values for a template 
	 * resource.
	 */
	public function compile($src, $target, $values = array()) {
		if (!file_exists($src)) {
			return;
		}

		if (is_dir($src)) {
			$this->compileResourceDirectory($src, $target);
		} else {
			$this->compileResource($src, $target, $values);
		}
	}

	/* Compile a directory of non-code template resources. */
	private function compileResourceDirectory($srcDir, $outDir) {
		$dir = new DirectoryIterator($srcDir);

		if (!file_exists($outDir)) {
			mkdir($outDir, 0755, true);
		}

		foreach ($dir as $resource) {
			if ($resource->isDot() || File::isHidden($resource)) {
				continue;
			}

			$fname = $resource->getFilename();
			if ($resource->isDir()) {
				$this->compile($resource->getPathname(), "$outDir/$fname");
				continue;
			}

			// PHP5.3.6: $ext = $resource->getExtension();
			$ext = pathinfo($fname, PATHINFO_EXTENSION);
			$basename = $resource->getBasename(".$ext");
			$pathname = $resource->getPathname();
			if ($ext === 'less') {
				$this->lessCompiler->compile($pathname, "$outDir/$basename.css");
			} else {
				copy($resource->getPathname(), "$outDir/$fname");
			}
		}
	}

	/* Compile a resource. Resource may be a code template. */
	private function compileResource($src, $target, $values) {
		$tmpl = $this->templateParser->parse(file_get_contents($src));
		$tmpl->save($target, $values);
	}

}

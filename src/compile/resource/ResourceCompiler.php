<?php
/**
 * Copyright (c) 2012, Philip Graham
 * All rights reserved.
 */
namespace zpt\cdt\compile\resource;

use \zpt\pct\CodeTemplateParser;
use \zpt\util\File;
use \CSSmin;
use \DirectoryIterator;

/**
 * This class compiles a resource directory.	A resource is an image, a css
 * file or a javascript file.
 *
 * @author Philip Graham <philip@zeptech.ca>
 */
class ResourceCompiler {

	private $cssMin;
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
	 * @param CssCompiler $cssCompiler CssCompiler instance. If no provided
	 * a default implementation will be instantiated.
	 */
	public function __construct(
		$templateParser = null,
		$lessCompiler = null
	) {
		if ($templateParser === null) {
			$templateParser = new CodeTemplateParser();
		}
		if ($lessCompiler === null) {
			$lessCompiler = new LessCompiler();
		}
		$this->templateParser = $templateParser;
		$this->lessCompiler = $lessCompiler;

		$this->cssMin = new CSSmin(false /* Don't change php mem settings */);
	}

	/**
	 * Combine a compiled resource group. Given the path to a target directory
	 * that contains compiled resources, this function will compile a group of
	 * resources of a specified type. The group is specified using a notation
	 * similar to Java's package notation. E.g:
	 *
	 *     $resourceCompiler->combineGroup($target, 'css', 'my.group');
	 *
	 * will combine all CSS files at `$target/css/my/group`. For more information
	 * on how the order in which files are grouped see the
	 * {@link ResourceDiscoverer} class.
	 *
	 * @param string $target The target directory where the compiled resource are
	 * found.
	 * @param string $resourceType The type of resource to combine.
	 * @param string $group The resource group to combine.
	 */
	public function combineGroup($target, $type, $group) {
		$resourceDiscoverer = new ResourceDiscoverer("$target/$type", $type);

		$files = $resourceDiscoverer->discover($group);

		$out = "$target/$type/$group.$type";
		$fp = fopen($out, 'w');
		foreach ($files as $file) {
			fwrite($fp, file_get_contents("$target/$type/$file"));
		}
		fclose($fp);
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
			$this->compileResourceDirectory($src, $target, $values);
		} else {
			$this->compileResource($src, $target, $values);
		}
	}

	/* Compile a directory of non-code template resources. */
	private function compileResourceDirectory($srcDir, $outDir, $values) {
		$dir = new DirectoryIterator($srcDir);

		if (!file_exists($outDir)) {
			mkdir($outDir, 0755, true);
		}

		$this->lessCompiler->setVariables($values);

		foreach ($dir as $resource) {
			if ($resource->isDot() || File::isHidden($resource)) {
				continue;
			}

			$fname = $resource->getFilename();
			if ($resource->isDir()) {
				$this->compile($resource->getPathname(), "$outDir/$fname");
				continue;
			}

			$ext = $resource->getExtension();
			$basename = $resource->getBasename(".$ext");
			$pathname = $resource->getPathname();

			if ($ext === 'css') {
				$css = file_get_contents($resource->getPathname());
				$minified = $this->cssMin->run($css);
				file_put_contents("$outDir/$fname", $minified);
			} else if ($ext === 'less') {
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

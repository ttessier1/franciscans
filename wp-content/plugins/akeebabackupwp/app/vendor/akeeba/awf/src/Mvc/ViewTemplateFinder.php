<?php
/**
 * @package   awf
 * @copyright Copyright (c)2014-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

namespace Awf\Mvc;

use Awf\Container\ContainerAwareInterface;
use Awf\Container\ContainerAwareTrait;
use Awf\Inflector\Inflector;
use Awf\Utils\Path;

/**
 * Locates the appropriate template file for a view
 */
class ViewTemplateFinder implements ContainerAwareInterface
{
	use ContainerAwareTrait;

	/** @var  View  The view we are attached to */
	protected $view;

	/** @var  array  The layout template extensions to look for */
	protected $extensions = array('.blade.php', '.php');

	/** @var  string  Default layout's name (default: "default") */
	protected $defaultLayout = 'default';

	/** @var  string  Default subtemplate name (default: empty) */
	protected $defaultTpl = '';

	/** @var  bool  Should I only look in the specified view (true) or also the pluralised/singularised (false) */
	protected $strictView = true;

	/** @var  bool  Should I only look for the defined subtemplate or also no subtemplate? */
	protected $strictTpl = true;

	/** @var  bool  Should  Should I only look for this layout or also the default layout? */
	protected $strictLayout = true;

	/**
	 * Public constructor. The config array can contain the following keys
	 * extensions       array
	 * defaultLayout    string
	 * defaultTpl       string
	 * strictView       bool
	 * strictTpl        bool
	 * strictLayout     bool
	 * For the descriptions of each key please see the same-named property of this class
	 *
	 * @param   View   $view    The view we are attached to
	 * @param   array  $config  The configuration for this view template finder
	 */
	function __construct(View $view, array $config = array())
	{
		$this->view = $view;
		$this->setContainer($view->getContainer());

		if (isset($config['extensions']))
		{
			if (!is_array($config['extensions']))
			{
				$config['extensions'] = trim($config['extensions']);
				$config['extensions'] = explode(',', $config['extensions']);
				$config['extensions'] = array_map(function ($x) { return trim($x); }, $config['extensions']);
			}

			$this->setExtensions($config['extensions']);
		}

		if (isset($config['defaultLayout']))
		{
			$this->setDefaultLayout($config['defaultLayout']);
		}

		if (isset($config['defaultTpl']))
		{
			$this->setDefaultTpl($config['defaultTpl']);
		}

		if (isset($config['strictView']))
		{
			$config['strictView'] = in_array($config['strictView'], array(true, 'true', 'yes', 'on', 1));

			$this->setStrictView($config['strictView']);
		}

		if (isset($config['strictTpl']))
		{
			$config['strictTpl'] = in_array($config['strictTpl'], array(true, 'true', 'yes', 'on', 1));

			$this->setStrictTpl($config['strictTpl']);
		}

		if (isset($config['strictLayout']))
		{
			$config['strictLayout'] = in_array($config['strictLayout'], array(true, 'true', 'yes', 'on', 1));

			$this->setStrictLayout($config['strictLayout']);
		}
	}

	/**
	 * Returns a list of template URIs for a specific component, view, template and sub-template. The $parameters array
	 * can have any of the following keys:
	 * view             string  The name of the view
	 * layout           string  The name of the layout
	 * tpl              string  The name of the subtemplate
	 * strictView       bool    Should I only look in the specified view, or should I look in the pluralised/singularised view as well?
	 * strictLayout     bool    Should I only look for this layout, or also for the default layout?
	 * strictTpl        bool    Should I only look for this subtemplate or also for no subtemplate?
	 *
	 * @param   array $parameters See above
	 *
	 * @return  array
	 * @throws \Exception
	 */
	public function getViewTemplateUris(array $parameters)
	{
		// Merge the default parameters with the parameters given
		$parameters = array_merge(array(
			'view'          => $this->view->getName(),
			'layout'        => $this->defaultLayout,
			'tpl'           => $this->defaultTpl,
			'strictView'    => $this->strictView,
			'strictLayout'  => $this->strictLayout,
			'strictTpl'     => $this->strictTpl,
		), $parameters);

		$uris = array();

		$view            = $parameters['view'];
		$layout          = $parameters['layout'];
		$tpl             = $parameters['tpl'];
		$strictView      = $parameters['strictView'];
		$strictLayout    = $parameters['strictLayout'];
		$strictTpl       = $parameters['strictTpl'];

		$basePath = $view . '/';

		$uris[] = $basePath . $layout . ($tpl ? "_$tpl" : '');

		if (!$strictTpl)
		{
			$uris[] = $basePath . $layout;
		}

		if (!$strictLayout)
		{
			$uris[] = $basePath . 'default' . ($tpl ? "_$tpl" : '');

			if (!$strictTpl)
			{
				$uris[] = $basePath . 'default';
			}
		}

		if (!$strictView)
		{
			$parameters['view'] = Inflector::isSingular($view) ? Inflector::pluralize($view) : Inflector::singularize($view);
			$parameters['strictView'] = true;

			$extraUris = $this->getViewTemplateUris($parameters);
			$uris = array_merge($uris, $extraUris);
			unset ($extraUris);
		}

		return array_unique($uris);
	}

	/**
	 * Parses a template URI in the form of view/layout to an array listing the view and template referenced therein.
	 *
	 * @param   string $uri The template path to parse
	 *
	 * @return  array  A hash array with the parsed path parts. Keys: view, template
	 * @throws \Exception
	 */
	public function parseTemplateUri($uri = '')
	{
		$parts = array(
			'view'		 => $this->view->getName(),
			'template'	 => 'default'
		);

		if (empty($uri))
		{
			return $parts;
		}

		$uriParts = explode('/', $uri, 2);
		$partCount = count($uriParts);

		if ($partCount >= 1)
		{
			$parts['view'] = $uriParts[0];
		}

		if ($partCount >= 2)
		{
			$parts['template'] = $uriParts[1];
		}

		return $parts;
	}

	/**
	 * Resolves a view template URI (e.g. Items/cheese) to an absolute filesystem path
	 * (e.g. /var/www/html/View/Items/tmpl/cheese.php)
	 *
	 * @param   string $uri            The view template URI to parse
	 * @param   string $layoutTemplate The layout template override of the View class
	 * @param   array  $extraPaths     Any extra lookup paths where we'll be looking for this view template
	 *
	 * @return  string
	 *
	 * @throws \RuntimeException
	 * @throws \Exception
	 */
	public function resolveUriToPath($uri, $layoutTemplate = '', array $extraPaths = array())
	{
		// Parse the URI into its parts
		$parts = $this->parseTemplateUri($uri);

		try
		{
			$templateName = $this->container->application->getTemplate();
		}
		catch (\Exception $e)
		{
			$templateName = 'system';
		}

		// Get the lookup paths
		$paths = array(
			// Template override
			$this->container->templatePath . '/' . $templateName . '/html/' . $parts['view'],
			// Legacy template override (legacy; do not use)
			$this->container->templatePath . '/html/' . $parts['view'],
			// Application ViewTemplates folder (preferred)
			$this->container->basePath . '/ViewTemplates/' . $parts['view'],
			// Application View folder (deprecated, mixing view objects and templates inside the tmpl subfolder)
			$this->container->basePath . '/View/' . $parts['view'] . '/tmpl',
			// Application views folder (legacy, don't use)
			$this->container->basePath . '/views/' . $parts['view'] . '/tmpl',
			// System template fallback
			$this->container->templatePath . '/system/html/' . $parts['view'],
		);

		// Add extra paths
		if (!empty($extraPaths))
		{
			$paths = array_merge($extraPaths, $paths);
		}

		// Remove duplicate paths
		$paths = array_map(function ($path) {
			return rtrim($path, '/' . DIRECTORY_SEPARATOR);
		}, $paths);
		$paths = array_unique($paths);

		foreach ($this->extensions as $extension)
		{
			$filenameToFind = $parts['template'] . $extension;

			$fileName = Path::find($paths, $filenameToFind);

			if ($fileName)
			{
				return $fileName;
			}
		}

		throw new \Exception($this->getContainer()->language->sprintf('AWF_APPLICATION_ERROR_LAYOUTFILE_NOT_FOUND', $uri), 500);
	}

	/**
	 * Get the list of view template extensions
	 *
	 * @return  array
	 */
	public function getExtensions()
	{
		return $this->extensions;
	}

	/**
	 * Set the list of view template extensions
	 *
	 * @param   array  $extensions
	 *
	 * @return  void
	 */
	public function setExtensions(array $extensions)
	{
		$this->extensions = $extensions;
	}

	/**
	 * Add an extension to the list of view template extensions
	 *
	 * @param   string  $extension
	 *
	 * @return  void
	 */
	public function addExtension($extension)
	{
		if (empty($extension))
		{
			return;
		}

		if (substr($extension, 0, 1) != '.')
		{
			$extension = '.' . $extension;
		}

		if (!in_array($extension, $this->extensions))
		{
			$this->extensions[] = $extension;
		}
	}

	/**
	 * Remove an extension from the list of view template extensions
	 *
	 * @param   string  $extension
	 *
	 * @return  void
	 */
	public function removeExtension($extension)
	{
		if (empty($extension))
		{
			return;
		}

		if (substr($extension, 0, 1) != '.')
		{
			$extension = '.' . $extension;
		}

		if (!in_array($extension, $this->extensions))
		{
			return;
		}

		$pos = array_search($extension, $this->extensions);
		unset ($this->extensions[$pos]);
	}

	/**
	 * Returns the default layout name
	 *
	 * @return  string
	 */
	public function getDefaultLayout()
	{
		return $this->defaultLayout;
	}

	/**
	 * Sets the default layout name
	 *
	 * @param   string  $defaultLayout
	 *
	 * @return  void
	 */
	public function setDefaultLayout($defaultLayout)
	{
		$this->defaultLayout = $defaultLayout;
	}

	/**
	 * Returns the default subtemplate name
	 *
	 * @return  string
	 */
	public function getDefaultTpl()
	{
		return $this->defaultTpl;
	}

	/**
	 * Sets the default subtemplate name
	 *
	 * @param  string  $defaultTpl
	 */
	public function setDefaultTpl($defaultTpl)
	{
		$this->defaultTpl = $defaultTpl;
	}

	/**
	 * Returns the "strict view" flag. When the flag is false we will look for the view template in both the
	 * singularised and pluralised view. If it's true we will only look for the view template in the view
	 * specified in getViewTemplateUris.
	 *
	 * @return  boolean
	 */
	public function isStrictView()
	{
		return $this->strictView;
	}

	/**
	 * Sets the "strict view" flag. When the flag is false we will look for the view template in both the
	 * singularised and pluralised view. If it's true we will only look for the view template in the view
	 * specified in getViewTemplateUris.
	 *
	 * @param   boolean  $strictView
	 *
	 * @return  void
	 */
	public function setStrictView($strictView)
	{
		$this->strictView = $strictView;
	}

	/**
	 * Returns the "strict template" flag. When the flag is false we will look for a view template with or without the
	 * subtemplate defined in getViewTemplateUris. If it's true we will only look for the subtemplate specified.
	 *
	 * @return boolean
	 */
	public function isStrictTpl()
	{
		return $this->strictTpl;
	}

	/**
	 * Sets the "strict template" flag. When the flag is false we will look for a view template with or without the
	 * subtemplate defined in getViewTemplateUris. If it's true we will only look for the subtemplate specified.
	 *
	 * @param   boolean  $strictTpl
	 *
	 * @return  void
	 */
	public function setStrictTpl($strictTpl)
	{
		$this->strictTpl = $strictTpl;
	}

	/**
	 * Returns the "strict layout" flag. When the flag is false we will look for a view template with both the specified
	 * and the default template name in getViewTemplateUris. When true we will only look for the specified view
	 * template.
	 *
	 * @return  boolean
	 */
	public function isStrictLayout()
	{
		return $this->strictLayout;
	}

	/**
	 * Sets the "strict layout" flag. When the flag is false we will look for a view template with both the specified
	 * and the default template name in getViewTemplateUris. When true we will only look for the specified view
	 * template.
	 *
	 * @param   boolean  $strictLayout
	 *
	 * @return  void
	 */
	public function setStrictLayout($strictLayout)
	{
		$this->strictLayout = $strictLayout;
	}
}

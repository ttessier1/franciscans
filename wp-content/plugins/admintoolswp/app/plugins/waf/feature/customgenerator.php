<?php
/**
 * @package   admintoolswp
 * @copyright Copyright (c)2017-2024 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU GPL version 3 or later
 */

defined('ADMINTOOLSINC') or die;

class AtsystemFeatureCustomgenerator extends AtsystemFeatureAbstract
{
	protected $loadOrder = 700;

	/**
	 * Is this feature enabled?
	 *
	 * @return bool
	 */
	public function isEnabled()
	{
		return ($this->cparams->getValue('custgenerator', 0) != 0);
	}

	public function onCustomHooks()
	{
		add_action('wp_head', array($this, 'custom_generator'));
		remove_action('wp_head', 'wp_generator');

		// The generator tag is appended in several different places, so we must remove it from all of them
		add_filter('get_the_generator_html', array($this, 'custom_generator'), 99, 2);
		add_filter('get_the_generator_xhtml', array($this, 'custom_generator'), 99, 2);
		add_filter('get_the_generator_atom', array($this, 'custom_generator'), 99, 2);
		add_filter('get_the_generator_rss2', array($this, 'custom_generator'), 99, 2);
		add_filter('get_the_generator_rdf', array($this, 'custom_generator'), 99, 2);
		add_filter('get_the_generator_comment', array($this, 'custom_generator'), 99, 2);
		add_filter('get_the_generator_export', array($this, 'custom_generator'), 99, 2);
	}

	/**
	 * Replace default generator with our custom text (or remove it entirely)
	 *
	 * @param   string  $orig_generator (not used)
	 * @param   string  $type           Page type, defaults
	 *
	 * @return  string  The new generator text
	 */
	public function custom_generator($orig_generator = '', $type = 'html')
	{
		$generator = $this->cparams->getValue('generator', 'MYOB');

		switch ( $type )
		{
			case 'html':
				// If we handle the HTML generator, we have to output it
				echo '<meta name="generator" content="' . $generator . '">';

				return null;
			case 'xhtml':
				$gen = '<meta name="generator" content="' . $generator . '" />';
				break;
			case 'atom':
				$gen = '<generator>'.$generator.'</generator>';
				break;
			case 'rss2':
				$gen = '<generator>'.$generator.'</generator>';
				break;
			case 'rdf':
				$gen = '<admin:generatorAgent rdf:resource="' . $generator . '" />';
				break;
			case 'comment':
				$gen = '<!-- generator="' . $generator . '" -->';
				break;
			case 'export':
				$gen = '<!-- generator="' . $generator . '" -->';
				break;
			default:
				$gen = '';
		}

		return $gen;
	}
}

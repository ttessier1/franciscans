<?php
/**
 * @package   solo
 * @copyright Copyright (c)2014-2020 Nicholas K. Dionysopoulos / Akeeba Ltd
 * @license   GNU General Public License version 3, or later
 */

defined('WPINC') or die;

use Awf\Document\Document;

/** @var Document $this */

// Script options
$prettyPrint = (defined('AKEEBADEBUG') && AKEEBADEBUG && defined('JSON_PRETTY_PRINT') ? JSON_PRETTY_PRINT : false);
$jsonOptions = json_encode($this->getScriptOptions(), $prettyPrint);
$jsonOptions = $jsonOptions ? $jsonOptions : '{}';

echo "\t<script type=\"application/json\" class=\"akeeba-script-options new\">$jsonOptions</script>\n";

// CSS files
foreach ($this->getStyles() as $url => $params)
{
	$media = ($params['media']) ? "media=\"{$params['media']}\"" : '';
	echo "\t<link rel=\"stylesheet\" type=\"{$params['mime']}\" href=\"$url\" $media></script>\n";
}

// Inline style
foreach ($this->getStyleDeclarations() as $type => $content)
{
	echo "\t<style type=\"$type\">\n$content\n</style>";
}

// JavaScript files
foreach ($this->getScripts() as $url => $params)
{
	echo "\t<script type=\"{$params['mime']}\" src=\"$url\"></script>\n";
}

// Inline script declarations
foreach ($this->getScriptDeclarations() as $type => $content)
{
	echo "\t<script type=\"$type\">\n$content\n</script>";
}

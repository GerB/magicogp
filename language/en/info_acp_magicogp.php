<?php

/**
 *
 * Magic OGP parser. An extension for the phpBB Forum Software package.
 * 
 *
 * @copyright (c) 2017, Ger, https://github.com/GerB
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */
if (!defined('IN_PHPBB'))
{
	exit;
}

if (empty($lang) || !is_array($lang))
{
	$lang = array();
}

$lang = array_merge($lang, array(
    'MISSING_REQUIREMENTS' => 'The magic OGP requires cURL, libXML, DOMDocument and DOMXPath to read the contents of pasted urls. At least one of these is not available on your server and therefore the extension cannot be installed.',
		));    
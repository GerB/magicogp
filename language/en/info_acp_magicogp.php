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
    'MOGP_BLACKLIST'                 => 'Magic OGP Blacklist',
    'MOGP_BLACKLIST_EXPLAIN'         => 'URLs containing words in this list will not be parsed by the Magic OGP parser. Each word must be on a separate line.',
    'MOGP_LOG_MISSING_SERVER_REQ'    => 'The magic OGP requires cURL, libXML, DOMDocument and DOMXPath to read the contents of pasted urls. At least one of these is not available (anymore) on your server.',
    'MOGP_MISSING_REQUIREMENTS'      => 'The magic OGP requires cURL, libXML, DOMDocument and DOMXPath to read the contents of pasted urls. At least one of these is not available on your server and therefore the extension cannot be installed.',
    'MOGP_SETTING_SAVED'             => 'Magic OGP settings saved',
    'MOGP_SETTINGS_TITLE'            => 'Magic OGP settings',
		));    
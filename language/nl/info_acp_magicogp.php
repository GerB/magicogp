<?php

/**
 *
 * Magic OGP parser. An extension for the phpBB Forum Software package.
 * [Dutch]
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
    'MISSING_REQUIREMENTS' => 'De extensie heeft  cURL, libXML, DOMDocument en DOMXPath  nodig om de inhoud van geplakte links op te halen. Minstens een onderdeel mist, waardoor de extensie niet geïnstalleerd kan worden.',
		));    
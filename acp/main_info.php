<?php
/**
 *
 * Magic OGP parser. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2018, Ger, https://github.com/GerB
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace ger\magicogp\acp;

/**
 * Magic OGP ACP module info.
 */
class main_info
{
	public function module()
	{
		return array(
			'filename'	=> '\ger\magicogp\acp\main_module',
			'title'		=> 'MOGP_SETTINGS_TITLE',
			'modes'		=> array(
				'settings'	=> array(
					'title'	=> 'MOGP_SETTINGS_TITLE',
					'auth'	=> 'ext_ger/magicogp && acl_a_board',
					'cat'	=> array('MOGP_SETTINGS_TITLE')
				),
			),
		);
	}
}

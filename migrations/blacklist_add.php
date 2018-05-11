<?php

/**
 *
 * Magic OGP - add blacklist option
 *
 * @copyright (c) 2016 Ger Bruinsma
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace ger\magicogp\migrations;

use phpbb\db\migration\container_aware_migration;

class blacklist_add extends container_aware_migration
{

	static public function depends_on()
	{
		return array('\phpbb\db\migration\data\v320\v320');
	}

	public function update_data()
	{
		return array(
			array('config_text.add', array('ger_magicogp_blacklist', '')),
                        array('module.add', array(
				'acp',
				'ACP_MESSAGES',
                                array(
					'module_basename'	=> '\ger\magicogp\acp\main_module',
					'modes'			=> array('settings'),
				),
			)),
		);
	}
}
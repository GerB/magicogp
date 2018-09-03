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
 * Feed post bot ACP module.
 */
class main_module
{
	public $u_action;

	public function main($id, $mode)
	{
            global $request, $template, $user, $phpbb_container;
            $config_text = $phpbb_container->get('config_text');
            $cache = $phpbb_container->get('cache.driver');
            $this->tpl_name     = 'acp_magicogp_body';
            $this->page_title	= $user->lang('MOGP_SETTINGS_TITLE');
            add_form_key('ger/magicogp');

            if ($request->is_set_post('submit'))
            {
                if (!check_form_key('ger/magicogp'))
                {
                    trigger_error('FORM_INVALID');
                }
                // Store
                $config_text->set('ger_magicogp_blacklist', $request->variable('ger_magicogp_blacklist', '', true));
//                $cache->destroy('_magicogp');
                $cache->purge();
                trigger_error($user->lang('MOGP_SETTING_SAVED') . adm_back_link($this->u_action));
            }
            
            // Show form
            $template->assign_vars(array(
                'U_ACTION'          => $this->u_action,
                'S_BLACKLIST'       => $config_text->get('ger_magicogp_blacklist'),
            ));
            
	}

}
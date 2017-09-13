<?php

namespace ger\magicogp;

class ext extends \phpbb\extension\base
{
    public function is_enableable()
    {
		if (! function_exists('curl_init') || !function_exists('libxml_use_internal_errors')|| !class_exists("\DOMXPath")  || !class_exists("\DOMDocument") )
		{
			$user = $this->container->get('user');
			$user->add_lang_ext('ger/magicogp', 'info_acp_magicogp');
			trigger_error($user->lang('MISSING_REQUIREMENTS'), E_USER_WARNING);
		}
		return true;
    }
}

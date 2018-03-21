<?php
/**
 *
 * Magic OGP parser. An extension for the phpBB Forum Software package.
 *
 * @copyright (c) 2017, Ger, https://github.com/GerB
 * @license GNU General Public License, version 2 (GPL-2.0)
 *
 */

namespace ger\magicogp\event;

/**
 * @ignore
 */
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

/**
 * Magic OGP event listener
 */
class main_listener implements EventSubscriberInterface
{
    protected $log;
    protected $config;
    protected $language;
    
	static public function getSubscribedEvents()
	{
		return array(
            'core.text_formatter_s9e_configure_after'	=> 'add_filterchain',
		);
	}

    public function __construct(\phpbb\log\log $log, \phpbb\config\config $config, \phpbb\language\language $language)
    {
        $this->log = $log;
        $this->config = $config;
        $this->language = $language;
    }
    
    /**
     * Add OGP tags to the filterchain
     * @param array $event
     */
    public function add_filterchain($event)
    {
        if (function_exists('curl_init') && function_exists('libxml_use_internal_errors') && class_exists("\DOMXPath") && class_exists("\DOMDocument") )
        {    
            $acceptLang = $this->buildAcceptLang();
            $tag = $event['configurator']->tags['URL'];
            $tag->filterChain->append('\ger\magicogp\classes\ogpParser::jsonTags')->addParameterByValue($acceptLang);
            $dom = $tag->template->asDOM();
            foreach ($dom->getElementsByTagName('a') as $a)
            {
                $a->setAttribute('data-ogp', '{@ogp}');
            }
            $dom->saveChanges();
        }
        else
        {
            $this->log->add('critical', 0, 0, 'MOGP_LOG_MISSING_SERVER_REQ');
        }
    }
    
    /**
     * Build HTTP header Accept-language based on board and user settings, default add english
     * @return string
     */
    private function buildAcceptLang()
    {      
        $default_lang = $this->config['default_lang'];
        if (empty($default_lang))
        {
            // Poorly configured, just let the remote server decide
            return false;
        }
        if (strpos($default_lang, '-x-') !== false)
        {
            $default_lang = substr($default_lang, 0, strpos($default_lang, '-x-'));
        }
        $user_lang = $this->language->lang('USER_LANG');
        if (strpos($user_lang, '-x-') !== false)
        {
            $user_lang = substr($user_lang, 0, strpos($user_lang, '-x-'));
        }
        $default_lang = trim($default_lang);
        $user_lang = trim($user_lang);
        
        // Board default first, append user lang if it differs and english as last resort
        $accept = $default_lang;
        if ($user_lang != $default_lang)
        {
            $accept .= ', ' . $default_lang . ';q=0.8';
        }
        if (($user_lang != 'en') && ($default_lang != 'en'))
        {
            $accept .= ', en;q=0.6';
        }
        return array('Accept-Language: ' . $accept);
    }
}
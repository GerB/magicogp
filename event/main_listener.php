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
    
    
	static public function getSubscribedEvents()
	{
		return array(
            'core.text_formatter_s9e_configure_after'	=> 'add_filterchain',
		);
	}

    public function __construct(\phpbb\log\log $log)
    {
        $this->log = $log;
    }
    
    /**
     * Add OGP tags to the filterchain
     * @param array $event
     */
    public function add_filterchain($event)
    {
        if (function_exists('curl_init') && function_exists('libxml_use_internal_errors') && class_exists("\DOMXPath") && class_exists("\DOMDocument") )
        {    
            $tag = $event['configurator']->tags['URL'];
            $tag->filterChain->append('\ger\magicogp\classes\ogpParser::jsonTags');
            $dom = $tag->template->asDOM();
            foreach ($dom->getElementsByTagName('a') as $a)
            {
                $a->setAttribute('data-ogp', '{@ogp}');
            }
            $dom->saveChanges();
        }
        else
        {
            $this->log->add('critical', 0, 0, 'LOG_MISSING_SERVER_REQ');
        }
    }
}

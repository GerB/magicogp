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
	static public function getSubscribedEvents()
	{
		return array(
            'core.text_formatter_s9e_configure_after'	=> 'add_filterchain',
		);
	}

    
    public function add_filterchain($event)
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
}

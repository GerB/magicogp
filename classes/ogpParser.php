<?php
namespace ger\magicogp\classes;

class ogpParser
{

    /**
     * Get JSON encoded OGP tags
     * 
     * @param string $tag
     * @return string
     */
    static public function jsonTags($tag)
    {
        // Magic urls should have a taglength of 0
        if ($tag->getLen() > 0)
        {
            return true;
        }
        
        // Since the textformatter requires us to use a static function, we need to call ourselves here         
        $ogpParser = new self();
                
        $ogpFull = $ogpParser->fetch($tag->getAttribute('url'));
        if (isset($ogpFull['image']) && isset($ogpFull['title']) && isset($ogpFull['description']))
        {
            $ogp = ['image' => $ogpFull['image'], 'title' => $ogpFull['title'], 'description' => $ogpFull['description']];
            $tag->setAttribute('ogp', json_encode($ogp));
        }
        return true;
    }

    /**
     * Holds all the OGP values
     * @var array
     */
    private $values = array();

    /**
     * Fetches a URI and parses it for Open Graph data
     *
     * @param $uri    URI to page to parse for Open Graph data
     * @return array|bool array with OGP values or false on error
     */
    public function fetch($uri)
    {
        $curl = curl_init($uri);

        curl_setopt($curl, CURLOPT_FAILONERROR, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($curl, CURLOPT_TIMEOUT, 15);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);

        $response = curl_exec($curl);

        curl_close($curl);

        if (!empty($response)) {
            return $this->_parse($response);
        } else {
            return false;
        }
    }

    /**
     * Parses HTML and extracts Open Graph data, this assumes
     * the document is at least well formed.
     *
     * @param $html    HTML to parse
     * @return array
     */
    private function _parse($html)
    {
        // Prevent errors on bad documents, load HTML and reset error handling
        $old_libxml_error = libxml_use_internal_errors(true);
        $doc = new \DOMDocument();
        $doc->loadHTML($html);
        libxml_use_internal_errors($old_libxml_error);

        $tags = $doc->getElementsByTagName('meta');
        if (!$tags || $tags->length === 0) {
            return false;
        }

        $defaultMetaDesc = null;

        // Loop trough all meta tags
        foreach ($tags AS $tag) {
            // Default has property and content
            if ($tag->hasAttribute('property') &&
                strpos($tag->getAttribute('property'), 'og:') === 0) {
                $key = strtr(substr($tag->getAttribute('property'), 3), '-', '_');
                $this->values[$key] = $tag->getAttribute('content');
            }
            // But some use name and conentinstead
            if ($tag->hasAttribute('name') &&
                strpos($tag->getAttribute('name'), 'og:') === 0) {
                $key = strtr(substr($tag->getAttribute('name'), 3), '-', '_');
                $this->values[$key] = $tag->getAttribute('content');
            }
            // And others use property and value
            if ($tag->hasAttribute('value') && $tag->hasAttribute('property') &&
                strpos($tag->getAttribute('property'), 'og:') === 0) {
                $key = strtr(substr($tag->getAttribute('property'), 3), '-', '_');
                $this->values[$key] = $tag->getAttribute('value');
            }
            // Description might just be in the standard meta tag for it
            if ($tag->hasAttribute('name') && $tag->getAttribute('name') === 'description') {
                $defaultMetaDesc = $tag->getAttribute('content');
            }
        }
        
        // Fetch title from the title tag if none yet
        if (!isset($this->values['title'])) {
            $titles = $doc->getElementsByTagName('title');
            if ($titles->length > 0) {
                $this->values['title'] = $titles->item(0)->textContent;
            }
        }
        // Add default description if none yet
        if (!isset($this->values['description']) && $defaultMetaDesc) {
            $this->values['description'] = $defaultMetaDesc;
        }

        // Search image_src if no img yet
        if (!isset($this->values['image'])) {
            $domxpath = new \DOMXPath($doc);
            $elements = $domxpath->query("//link[@rel='image_src']");

            if ($elements->length > 0) {
                $domattr = $elements->item(0)->attributes->getNamedItem('href');
                if ($domattr) {
                    $this->values['image'] = $domattr->value;
                    $this->values['image_src'] = $domattr->value;
                }
            }
        }
        
        // Still no image? Try fetching the icon
        if (!isset($this->values['image'])) {
            $domxpath = new \DOMXPath($doc);
            $elements = $domxpath->query("//link[@rel='icon']");

            if ($elements->length > 0) {
                $domattr = $elements->item(0)->attributes->getNamedItem('href');
                if ($domattr) {
                    $this->values['image'] = $domattr->value;
                    $this->values['image_src'] = $domattr->value;
                }
            }
        }

        // All done
        return empty($this->values) ? false : $this->values;
    }
}

// EoF
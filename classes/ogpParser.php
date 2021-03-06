<?php
namespace ger\magicogp\classes;

class ogpParser
{
    /**
     * Holds all the OGP values
     * @var array
     */
    public $values = array();
    
    /**
     * Get JSON encoded OGP tags
     * 
     * @param string $tag
     * @param array $acceptLang
     * @param array $blacklist
     * @return string
     */
    static public function jsonTags($tag, $acceptLang, $blacklist)
    {
        // Magic urls should have a taglength of 0
        if ($tag->getLen() > 0)
        {
            return true;
        }
        
        // Since the textformatter requires us to use a static function, we need to call ourselves here   
        $ogpParser = new self();
        if ($ogpParser->blacklistCheck($blacklist, $tag->getAttribute('url')) == false)
        {
            return true;
        }
                
        $ogpParser->fetch($tag->getAttribute('url'), $acceptLang);
        if (!isset($ogpParser->values['image']) || !isset($ogpParser->values['title']) || !isset($ogpParser->values['description']))
        {
            // Try to override the user agent string
            $ogpParser->fetch($tag->getAttribute('url'), $acceptLang, 'Googlebot/2.1 (+http://www.google.com/bot.html)');
        }
        
        // We at least want a title and description
        if (isset($ogpParser->values['title']) && isset($ogpParser->values['description']))
        {
            $ogp = ['title' => $ogpParser->values['title'], 'description' => $ogpParser->values['description']];
            if (isset($ogpParser->values['image']))
            {
                $ogp['image'] = $ogpParser->values['image'];
            }
            $tag->setAttribute('ogp', json_encode($ogp));
        }
        return true;
    }

    /**
     * Check if URL is in Magic OGP blacklist
     * @param array $blacklist
     * @param string $url
     * @return boolean false if found in blacklist, true otherwise
     */
    public function blacklistCheck($blacklist, $url) 
    {
        // ACP entered blacklist
        if (is_array($blacklist))
        {
            foreach ($blacklist as $entry)
            {
                $check = trim($entry);
                if (!empty($check))
                {
                    if (strpos($url, $check) !== FALSE)
                    {
                        return false;
                    }
                }
            }
        }
        // Exlcude media and such
        $exclude = ['aif', 'cda', 'mid', 'midi', 'mp3', 'mpa', 'ogg', 'wav', 'wma', 'wpi', '3g2', '3gp', 'avi', 'flv', 'h264', 'm4v', 'mkv', 'mov', 'mp4', 'mpg', 'mpeg', 'rm', 'swf', 'vob', 'wmv', 
                    '7z', 'arj', 'deb', 'pkg', 'rar', 'rpm', 'tar.gz', 'z', 'zip', 'bin', 'dmg', 'iso', 'vcd', 'csv', 'dat', 'db', 'dbf', 'log', 'mdb', 'sav', 'sql', 'tar', 'xml', 'apk', 'bat', 'bin', 'cgi', 'pl', 'gadget', 'jar', 'py', 'wsf',
                    'fnt', 'fon', 'otf', 'ttf', 'ai', 'bmp', 'gif', 'ico', 'jpeg', 'jpg', 'png', 'ps', 'psd', 'svg', 'tif', 'tiff', 'key', 'odp', 'pps', 'ppt', 'pptx', 'java', 'swift', 'ods', 'xlr', 'xls', 'xlsx',
                    'bak', 'cab', 'cfg', 'cpl', 'cur', 'dll', 'dmp', 'drv', 'icns', 'ini', 'lnk', 'msi', 'sys', 'doc', 'docx', 'odt', 'pdf', 'rtf', 'tex', 'txt', 'wks', 'wps', 'wpd'];
        $path = parse_url($url, PHP_URL_PATH);
        $ext = substr(strrchr($path, "."), 1);
        if (in_array($ext, $exclude)) {
            return false;
        }
        return true;
    }


    /**
     * Fetches a URI and parses it for Open Graph data
     *
     * @param string $uri                   URI to page to parse for Open Graph data
     * @param  array $acceptLang            Header to pass for http-accept-language.
     * @param string $useragentOverride     Useragent string to bypass blocks like cookie walls etc.
     * @return array|bool array with OGP values or false on error
     */
    public function fetch($uri, $acceptLang, $useragentOverride = false)
    {
        $curl = curl_init($url= html_entity_decode($uri));

        curl_setopt($curl, CURLOPT_FAILONERROR, true);
        curl_setopt($curl, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);      
        if ($acceptLang) 
        {
            curl_setopt($curl, CURLOPT_HTTPHEADER, $acceptLang);
        }
        curl_setopt($curl, CURLOPT_TIMEOUT, 15);
        if ($useragentOverride)
        {
            curl_setopt($curl, CURLOPT_USERAGENT, $useragentOverride ); 
        }
        $response = curl_exec($curl);        
        curl_close($curl);

        return empty($response) ? false: $this->parse($response);
    }
    
    /**
     * Parses HTML and extracts Open Graph data
     *
     * @param $html    HTML to parse
     * @return array
     */
    private function parse($html)
    {
        // Init array for this round
        $found = [];
        
        // Prevent errors on bad documents, load HTML and reset error handling
        $old_libxml_error = libxml_use_internal_errors(true);
        $doc = new \DOMDocument('1.0', 'utf-8');
        $doc->loadHTML('<?xml encoding="utf-8"?>' . $html);
        libxml_use_internal_errors($old_libxml_error);

        $tags = $doc->getElementsByTagName('meta');
        if (!$tags || $tags->length === 0) 
            {
            return false;
        }

        $defaultMetaDesc = null;
        
        // Loop trough all meta tags
        foreach ($tags AS $tag) 
        {
            // Default has property and content
            if ($tag->hasAttribute('property') && strpos($tag->getAttribute('property'), 'og:') === 0) 
            {
                $key = strtr(substr($tag->getAttribute('property'), 3), '-', '_');
                $found[$key] = $tag->getAttribute('content');
            }
            // But some use name and conentinstead
            if ($tag->hasAttribute('name') && strpos($tag->getAttribute('name'), 'og:') === 0) 
            {
                $key = strtr(substr($tag->getAttribute('name'), 3), '-', '_');
                $found[$key] = $tag->getAttribute('content');
            }
            // And others use property and value
            if ($tag->hasAttribute('value') && $tag->hasAttribute('property') && strpos($tag->getAttribute('property'), 'og:') === 0) 
            {
                $key = strtr(substr($tag->getAttribute('property'), 3), '-', '_');
                $found[$key] = $tag->getAttribute('value');
            }
            // Description might just be in the standard meta tag for it
            if ($tag->hasAttribute('name') && $tag->getAttribute('name') === 'description') 
            {
                $defaultMetaDesc = $tag->getAttribute('content');
            }
        }
        
        // Fetch title from the title tag if none yet
        if (!isset($found['title'])) 
        {
            $titles = $doc->getElementsByTagName('title');
            if ($titles->length > 0) 
            {
                $found['title'] = $titles->item(0)->textContent;
            }
        }
        // Add default description if none yet
        if (!isset($found['description']) && $defaultMetaDesc) 
        {
            $found['description'] = $defaultMetaDesc;
        }

        // Search image_src if no img yet
        if (!isset($found['image'])) 
        {
            $domxpath = new \DOMXPath($doc);
            $elements = $domxpath->query("//link[@rel='image_src']");
            if ($elements->length > 0) 
            {
                $domattr = $elements->item(0)->attributes->getNamedItem('href');
                if ($domattr) 
                {
                    $found['image'] = $domattr->value;
                }
            }
        }
                
        // Have we been here before?
        if (count($this->values) == 0)
        {
            $this->values = $found;
        }
        else
        {
            foreach($found as $key => $value)
            {
                // Get the longest value... It ain't wisdom but just gambling that longer equals better ;)
                if ((!isset($this->values[$key])) || (strlen($value) > strlen($this->values[$key])) )
                {
                    $this->values[$key] = $value;
                }
            }
        }
        
        // All done
        return empty($this->values) ? false : $this->values;
    }
}
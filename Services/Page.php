<?php

/**
 * Small pageo object that its not necessary to load lw_object.
 * 
 * @author Michael Mandt <michael.mandt@logic-works.de>
 * @package AgentUpdater
 */

namespace AgentUpdater\Services;

class Page
{

    /**
     * Pagereload with certain url.
     * 
     * @param string $url
     */
    public static function reload($url)
    {
        $url = str_replace("&amp;", "&", $url);
        echo '<html>' . PHP_EOL;
        echo '    <head><meta http-equiv="Refresh" content="0;url=' . $url . '" /></head>' . PHP_EOL;
        echo '    <body onload="try {self.location.href=' . "'" . $url . "'" . ' } catch(e) {}"><a href="' . $url . '">Redirect </a></body>' . PHP_EOL;
        echo '</html>' . PHP_EOL;
        exit();
    }

    /**
     * Url builder for this plugin with optional parameter (GET).
     * 
     * @param array $array
     * @return string
     */
    public static function getUrl($array = false)
    {
        return \lw_page::getInstance()->getUrl($array);
    }

}

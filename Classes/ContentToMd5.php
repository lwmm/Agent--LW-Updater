<?php

namespace AgentUpdater\Classes;

class ContentToMd5
{

    public function getMd5FromString($string)
    {
        #die steuerzeichen die trim() am anfang und ende entfernt ( wird auf den gesamten string angewendet )
        $array = array(" ", "\t", "\n", "\r", "\0", "\x0B");
        
        return md5(str_replace($array, "", $string));
    }

    public function getMd5FromFile($path, $charset = false)
    {
        if (!$charset) {
            $charset = "UTF-8";
        }
        $charsetConverter = new \AgentUpdater\Classes\CharsetConverter();

        $path = str_replace("//", "", $path);
        $path = str_replace("..", "", $path);
        if (is_file($path)) {
            $file = fopen($path, "r");
            while (!feof($file)) {
                $content .= fgets($file);
            }
            fclose($file);
            return $this->getMd5FromString($charsetConverter->execute($charset, $content));
        }
        return false;
    }

}

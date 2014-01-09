<?php

namespace AgentUpdater\Classes;

class ContentToMd5
{

    public function getMd5FromString($string)
    {
        return md5(str_replace("\r", "", $string));
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
            return md5($charsetConverter->execute($charset, $content));
        }
        return false;
    }

}

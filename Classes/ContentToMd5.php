<?php

namespace AgentUpdater\Classes;

class ContentToMd5
{
    public function getMd5FromString($string)
    {
        return md5(trim($string));
    }
    
    public function getMd5FromFile($path)
    {
        $path = str_replace("//", "", $path);
        $path = str_replace("..", "", $path);
        if(is_file($path)){
            $file = fopen ($path, "r");
            while (!feof($file)) {
                $content .= fgets($file);
            }
            fclose ($file);
            return $this->getMd5FromString($content);
        }
        return false;
    }
}
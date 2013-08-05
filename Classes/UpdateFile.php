<?php

namespace AgentUpdater\Classes;

class UpdateFile
{
    public function execute($path, $content)
    {
        if(is_file($path)){
            $file = fopen ($path, "w");
            fwrite($file, $content);
            fclose ($file);
            return true;
        }
        return false;
    }
}
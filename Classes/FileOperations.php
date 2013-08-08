<?php

namespace AgentUpdater\Classes;

class FileOperations
{

    protected $config;

    public function __construct($config)
    {
        $this->config = $config;
    }

    public function update($path, $content)
    {
        if (is_file($path)) {
            $file = fopen($path, "w");
            fwrite($file, $content);
            fclose($file);
            return true;
        }
        return false;
    }

    public function addStructure($path, $pathPlaceholder,$content)
    {
        $pathAfterPlaceholder = str_replace("[".$pathPlaceholder."]", "", $path);
        
        $dirLevels = explode("/", $pathAfterPlaceholder);
        unset($dirLevels[count($dirLevels) -1]); # letzte element loeschen -> dateiname

        foreach($dirLevels as $dir){
            $dirPath .= $dir."/";
            if(!is_dir($this->config["path"][$pathPlaceholder] . $dirPath)){
                mkdir($this->config["path"][$pathPlaceholder] . $dirPath);
            }
        }
        
        $file = fopen($this->config["path"][$pathPlaceholder].$pathAfterPlaceholder, "w+");
        fwrite($file, $content);
        fclose($file);
        return true;
    }

}
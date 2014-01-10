<?php

namespace AgentUpdater\Model;

class FileUpdates
{

    protected $config;
    protected $request;

    public function __construct($config, $request)
    {
        $this->config = $config;
        $this->request = $request;
    }

    public function execute($fileArray)
    {
        $fileOperations = new \AgentUpdater\Classes\FileOperations($this->config);
        $charsetConverter = new \AgentUpdater\Classes\CharsetConverter();
        
        foreach ($fileArray as $entry) {

            if (!$entry["fileMd5"]) {
                $fileOperations->addStructure($entry["pathUsedInXml"], $entry["pathPlaceholder"], $charsetConverter->execute($this->request->getRaw("inputCharset"), $entry["content"]));
            } else if ($entry["md5"] == $entry["fileMd5"]) {
                $fileOperations->update($entry["filePath"], $charsetConverter->execute($this->request->getRaw("inputCharset"), $entry["content"]));
            }
        }

        return true;
    }

    protected function update($path, $content)
    {
        if (is_file($path)) {
            $file = fopen($path, "w");
            fwrite($file, $content);
            fclose($file);
            return true;
        }
        return false;
    }

    protected function addStructure($path, $pathPlaceholder, $content)
    {


        $pathAfterPlaceholder = str_replace("[" . $pathPlaceholder . "]", "", $path);

        $dirLevels = explode("/", $pathAfterPlaceholder);
        unset($dirLevels[count($dirLevels) - 1]); # letzte element loeschen -> dateiname

        foreach ($dirLevels as $dir) {
            $dirPath .= $dir . "/";
            if (!is_dir($this->config["path"][$pathPlaceholder] . $dirPath)) {
                mkdir($this->config["path"][$pathPlaceholder] . $dirPath);
            }
        }

        $file = fopen($this->config["path"][$pathPlaceholder] . $pathAfterPlaceholder, "w+");
        fwrite($file, $content);
        fclose($file);
        return true;
    }

}

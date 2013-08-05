<?php

namespace AgentUpdater\Controller;

class Md5CheckerXmlController
{

    protected $request;
    protected $config;
    protected $debug;

    public function __construct($config, $request)
    {
        $this->config = $config;
        $this->request = $request;
        $this->debug = $request->getInt("debug");
    }

    public function execute($array)
    {
        $xmlUploaded = false;
        
        $fileDataArray = $this->request->getFileData("inputXMLFile");
        if (!empty($fileDataArray["name"])) {
            $xmlString = file_get_contents($fileDataArray["tmp_name"]);
            $xmlUploaded = true;
        }
        else if ($this->request->getRaw("inputXML") != "") {
            $xmlString = $this->request->getRaw("inputXML");
            $xmlUploaded = true;
        }

        if($xmlUploaded){
            $array["sent"] = true;
            $array["xmlResults"] = $this->prepareXML($xmlString);
            $array["xmlString"] = $xmlString;
            $array["debug"] = $this->debug;
        }

        return $array;
    }

    private function prepareXML($xmlString)
    {
        $array = array();

        $xml = simplexml_load_string($xmlString);

        foreach ($xml->file as $file) {
            $path = $this->replacePathPlaceholderWithConfigPathEntry($file->path);
            $fileMd5 = $this->getMd5($path, "file");
            $array[] = array(
                "md5"   => $file->md5,
                "fileMd5"   => $fileMd5,
                "filePath"  => $path
            );
            
            if(!$this->debug){
                if($file->md5 == $fileMd5){
                    $fileUpdater = new \AgentUpdater\Classes\UpdateFile();
                    $fileUpdater->execute($path, $file->content);
                }
            }
        }
        
        return $array;
    }

    private function replacePathPlaceholderWithConfigPathEntry($path)
    {
        $plaeholder = substr($path, 0, strpos($path, "]") + 1);
        $plaeholder = str_replace("[", "", str_replace("]", "", $plaeholder));

        $preparedPath = str_replace("[" . $plaeholder . "]", $this->config["path"][$plaeholder], $path);
        $preparedPath = str_replace("//", "", str_replace("..", "", $preparedPath));

        return $preparedPath;
    }

    private function getMd5($string, $type = false)
    {
        $contentToMd5 = new \AgentUpdater\Classes\ContentToMd5();

        if ($type == "file") {
            $md5 = $contentToMd5->getMd5FromFile($string);
        }
        else {
            $md5 = $contentToMd5->getMd5FromString($string);
        }

        return $md5;
    }

}
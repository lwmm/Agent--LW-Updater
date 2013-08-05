<?php

namespace AgentUpdater\Controller;

class Md5CheckerController
{

    protected $request;
    protected $config;

    public function __construct($config, $request)
    {
        $this->config = $config;
        $this->request = $request;
    }

    public function execute($array)
    {
        if ($this->request->getRaw("inputText") != "") {
            $array["sent"] = true;
            $array["inputText"]["formInput"] = $this->request->getRaw("inputText");
            $array["inputText"]["md5"] = $this->getMd5($this->request->getRaw("inputText"));
        }
        if ($this->request->getRaw("path") != "") {
            $array["sent"] = true;
            $array["configPath"]["formInput"] = $this->request->getRaw("configPath");
            $array["path"]["formInput"] = $this->request->getRaw("path");
            $array["path"]["md5"] = $this->getMd5($this->request->getRaw("configPath") . $this->request->getRaw("path"), "file");
        }

        return $array;
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
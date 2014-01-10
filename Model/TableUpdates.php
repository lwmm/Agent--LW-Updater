<?php

namespace AgentUpdater\Model;

class TableUpdates
{

    protected $db;
    protected $config;

    public function __construct($config, $db)
    {
        $this->db = $db;
        $this->config = $config;
    }

    public function tableUpdate($tables)
    {
        foreach ($tables as $table) {
            foreach($table as $update){
                if (strtolower(substr($update["statement"], 0, strlen("ERROR"))) != "error") {
                    $this->db->setStatement($update["statement"]);
                    $this->db->pdbquery();
                }
            }
        }
        return true;
    }

    public function tableCreate($tables)
    {
        foreach($tables as $table){
            if (strtolower(substr($table["statement"], 0, strlen("ERROR"))) != "error") {
                if ($this->config["lwdb"]["type"] == "mysql" || $this->config["lwdb"]["type"] == "mysqli") {
                    $this->db->setStatement($table["statement"]);
                    $this->db->pdbquery();
                } else {
                    foreach ($table["statement"] as $key => $value) {
                        if ($key == "addai") {
                            foreach ($value as $v) {
                                $this->db->setStatement($v);
                                $this->db->pdbquery();
                            }
                        } else {
                            $this->db->setStatement($value);
                            $this->db->pdbquery();
                        }
                    }
                }
            }
        }        
        return true;
    }

}

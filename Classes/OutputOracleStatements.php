<?php

namespace AgentUpdater\Classes;

class OutputOracleStatements
{

    protected $db;

    public function __construct($db)
    {
        $this->db = $db;
    }

    private function fieldExists($table, $name)
    {
        $this->db->setStatement("SELECT table_name, column_name FROM user_tab_columns WHERE UPPER(table_name) = UPPER(' :table ') AND UPPER(column_name) = UPPER(' :name ') ");
        $this->db->bindParameter("table", "s", $table);
        $this->db->bindParameter("name", "s", $name);
        $erg = $this->db->pselect();

        if ($erg["column_name"] == strtoupper($name) && strlen(trim($name)) > 0)
            return true;
        return false;
    }

    public function addField($table, $name, $type, $size = false, $null = false)
    {
        if ($this->db->tableExists($table)) {
            if (!$this->fieldExists($table, $name)) {
                $field = $this->setField($type, $size);

                if (!$field) {
                    return "ERROR: FIELD NOT AVAILABLE";
                }

                $sql = "ALTER TABLE " . $table . " ADD (" . strtoupper($name) . " " . $field;
                if ($null) {
                    $sql.= " NULL ";
                } else {
                    $sql.= " NOT NULL ";
                }
                $sql.=")";

                return $sql;
            }
            return "ERROR: FIELD EXISTING";
        }
        return "ERROR: TABLE NOT EXISTING";
    }

    private function setField($type, $size)
    {
        switch ($type) {
            case "number":
                return " NUMBER(" . $size . ") ";
                break;

            case "text":
                return " VARCHAR2(" . $size . ") ";
                break;

            case "clob":
                return " CLOB ";
                break;

            case "bool":
                return " NUMBER(1) ";
                break;

            default:
                return false;
        }
    }

    function createTable($tablename, $fields)
    {
        if ($this->db->tableExists($tablename)) {
            return "ERROR: TABLE EXISTING";
        }

        $array = array();

        $main = "";
        $head = "CREATE TABLE " . $this->db->getPrefix() . $tablename . " ( ";
        $this->ai = false;

        foreach ($fields->field as $field) {
            if (strlen($main) > 0) {
                $main.=", ";
            }
            $main.= $this->_buildField($field);
        }
        $main.= ')' . " ";

        $array["create"] = $head . $main;


        if ($fields->pk && strlen($fields->pk) > 0) {
            $array["addpk"] = $this->_addPK($this->db->getPrefix() . $tablename, $fields->pk);
        }
        if ($this->ai == true) {
            $array["addai"] = $this->_addAutoIncrement($this->db->getPrefix() . $tablename);
        }

        return $array;
    }

    private function _buildField($field)
    {
        $out = "";
        $out.= ' ' . strtoupper($field->attributes()->name);
        switch ($field->attributes()->type) {
            case "number":
                $out.=" NUMBER(" . $field->attributes()->size . ") ";
                break;

            case "text":
                $out.= " VARCHAR2(" . $field->attributes()->size . ") ";
                break;

            case "clob":
                $out.= " CLOB ";
                break;

            case "bool":
                $out.= " NUMBER(1) ";
                break;

            default:
                die("field not available");
        }
        if ($field->attributes()->special == 'auto_increment') {
            $this->ai = true;
        }
        return $out;
    }

    private function _addPK($table, $pk)
    {
        return "ALTER TABLE " . $table . " ADD ( PRIMARY KEY ( " . $pk . " ) )";
    }

    private function _addAutoIncrement($table)
    {
        $array = array();

        $array["dropsequence"] = "DROP SEQUENCE " . $table . "_SEQ";

        $array["createsequence"] = "CREATE SEQUENCE " . $table . "_SEQ START WITH 1 INCREMENT BY 1 MAXVALUE 1E27 MINVALUE 1 NOCACHE NOCYCLE ORDER";

        $array["createtrigger"] = "CREATE OR REPLACE TRIGGER " . $table . "_ib BEFORE INSERT ON " . $table . " FOR EACH ROW  BEGIN IF :new.id IS null THEN select " . $table . "_SEQ.nextval into :new.id from dual; END IF; END;";

        return $array;
    }

}

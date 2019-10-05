<?php

class Mysql extends mysqli {

    static public $db_host;
    static public $db_user;
    static public $db_pass;
    static public $db_name;

    const cns_create = 'CREATE TABLE IF NOT EXISTS';
    const cns_prefix = 'Sdy';
    const cns_charset = 'utf8_general_ci';

    public function __construct() {
        parent::__construct(static::$db_host, static::$db_user, static::$db_pass, static::$db_name);
        if (empty($this->connect_errno)) {
            $this->set_charset('utf8');
        } else {
            die('Error Mysql: ' . $this->connect_error);
        }
    }

    public function __destruct() {
        if (empty($this->connect_errno)) {
            $this->close();
        }
    }

    public function Begin() {
        return 'Mysql Ret';
    }

    private function CreateDB() {
        $query = 'CREATE SCHEMA `Sdy` DEFAULT CHARACTER SET utf8 ';
    }

}

<?php

class Mysql extends mysqli {
    static public $db_host;
    static public $db_user;
    static public $db_pass;
    static public $db_name;

    const PREFIX = 'Sdy';

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
    public function ErrorHandling($Mysql) {
        if (!empty($Mysql->error)) {
            echo '<pre>';
            print_r($Mysql->error);
            echo '</pre>';
            exit;
        }
        return true;
    }

    public function CreateOfficeTable() {
        $query = 'CREATE TABLE IF NOT EXISTS `speedy_office` (
            `ID` int(10) UNSIGNED NOT NULL,
            `OfficeID` int(10) UNSIGNED NOT NULL,
            `Title` varchar(200) NOT NULL,
            `Address` varchar(200) DEFAULT NULL,
            `CountryID` int(11) DEFAULT NULL,
            `SiteID` int(11) DEFAULT NULL,
            `PostCode` int(11) DEFAULT NULL,
            `StreetId` int(11) DEFAULT NULL,
            `QuarterId` int(11) DEFAULT NULL,
            `CoordTypeId` int(11) DEFAULT NULL,
            `CoordX` double DEFAULT NULL,
            `CoordY` double DEFAULT NULL,
            `Closed` tinyint(4) NOT NULL
        ) ENGINE = InnoDB DEFAULT CHARSET = utf8;';
        $this->query($query);

        $query = 'ALTER TABLE `speedy_office` ADD PRIMARY KEY (`ID`); ';
        $this->query($query);

        $query = ' ALTER TABLE `speedy_office` MODIFY `ID` int(10) UNSIGNED NOT NULL AUTO_INCREMENT;';
        $this->query($query);

        $this->ErrorHandling($this);

        return true;
    }
}

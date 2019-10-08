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
            `ID` int(10) PRIMARY KEY AUTO_INCREMENT NOT NULL,
            `OfficeID` int(10) UNSIGNED NOT NULL,
            `Title` varchar(200) NOT NULL,
            `Address` varchar(200) DEFAULT NULL,
            `CountryID` int(11) UNSIGNED DEFAULT NULL,
            `SiteID` int(11) UNSIGNED DEFAULT NULL,
            `PostCode` int(11) UNSIGNED DEFAULT NULL,
            `StreetId` int(11) UNSIGNED DEFAULT NULL,
            `QuarterId` int(11) UNSIGNED DEFAULT NULL,
            `CoordX` double DEFAULT NULL,
            `CoordY` double DEFAULT NULL,
            `CoordTypeID` int(11) UNSIGNED DEFAULT NULL,
            `Closed` tinyint(1) NOT NULL
        ) ENGINE = InnoDB DEFAULT CHARSET = utf8;';
        $this->query($query);
        $this->ErrorHandling($this);


        return true;
    }

    public function CreateStreetTable() {
        $query = 'CREATE TABLE IF NOT EXISTS `speedy_street` (
            `ID` int(10) PRIMARY KEY AUTO_INCREMENT NOT NULL,
            `StreetID` int(10) UNSIGNED NOT NULL,
            `Type` varchar(10) NOT NULL,
            `Name` varchar(255) NOT NULL,
            `PostCode` int(11) UNSIGNED NOT NULL,
            `SiteID` int(11) UNSIGNED NOT NULL
        ) ENGINE = InnoDB DEFAULT CHARSET = utf8;';
        $this->query($query);
        $this->ErrorHandling($this);

        return true;
    }

    public function CreateQuarterTable() {
        $query = 'CREATE TABLE IF NOT EXISTS `speedy_quarter` (
            `ID` int(10) PRIMARY KEY AUTO_INCREMENT NOT NULL,
            `QuarterID` int(10) UNSIGNED NOT NULL,
            `Type` varchar(10) NOT NULL,
            `Name` varchar(255) NOT NULL,
            `PostCode` int(11) UNSIGNED NOT NULL,
            `SiteID` int(11) UNSIGNED NOT NULL
        ) ENGINE = InnoDB DEFAULT CHARSET = utf8;';
        $this->query($query);
        $this->ErrorHandling($this);

        return true;
    }

    public function CreateCityTable() {
        $query = 'CREATE TABLE IF NOT EXISTS `speedy_city` (
            `ID` int(10) PRIMARY KEY AUTO_INCREMENT NOT NULL,
            `CityID` int(10) UNSIGNED NOT NULL,
            `Type` varchar(10) NOT NULL,
            `Name` varchar(120) DEFAULT NULL,
            `Municpality` varchar(120) DEFAULT NULL,
            `Region` varchar(120) DEFAULT NULL,
            `PostCode` int(11) UNSIGNED DEFAULT NULL,
            `AddrN` varchar(120) DEFAULT NULL,
            `CountryID` int(11) UNSIGNED DEFAULT NULL,
            `ServingOfficeID` int(3) UNSIGNED DEFAULT NULL,
            `CoordX` double DEFAULT NULL,
            `CoordY` double DEFAULT NULL,
            `CoordTypeID` int(11) UNSIGNED DEFAULT NULL,
            `ServingDays` varchar(7) NOT NULL,
            `Closed` tinyint(1) NOT NULL
        ) ENGINE = InnoDB DEFAULT CHARSET = utf8;';
        $this->query($query);
        $this->ErrorHandling($this);

        return true;
    }
}

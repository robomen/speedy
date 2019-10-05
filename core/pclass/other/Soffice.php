<?php

class Soffice {

    const cns_table = 'Soffice';

    /*
     * country
     * ResultCountry = 100
     * ResultCountry = BG
     *  ResultCountry = БЪЛГАРИЯ
     * 
     * office
     * ResultOfficeEx = id
     * ResultOfficeEx = name
     * ResultOfficeEx = siteId
     * ResultOfficeEx = address
     */

    public function Install() {
        //create table 
    }

    private function DbTableCreate() {
        $Mysql = new Mysql();
        $query = $Mysql::cns_create . ' `' . $Mysql::cns_prefix . self::cns_table . '` (';
        $query .= ' `id` int(11) NOT NULL AUTO_INCREMENT, ';
        $query .= ' `OfficeId` int(11) NOT NULL DEFAULT "0", ';
        $query .= ' `Title` varchar(200) NOT NULL DEFAULT "", ';
        $query .= ' `Address` varchar(200) NOT NULL DEFAULT "", ';
        $query .= ' `Countr` varchar(200) NOT NULL DEFAULT "", ';
        $query .= ' `City` varchar(200) NOT NULL DEFAULT "", ';
        $query .= ' `comment` text NOT NULL, ';
        $query .= ' `reg_date` int(11) NOT NULL DEFAULT "0", ';
        $query .= ' `id` int(11) NOT NULL, ';
        $query .= ' PRIMARY KEY (`abuse_id`), ';
        $query .= 'KEY `reg_date` (`reg_date`)';
        $query .= ') ENGINE=MyISAM  DEFAULT CHARSET=utf8_general_ci COMMENT="Table with abuse reports" AUTO_INCREMENT=1 ;';
    }

    public function Working() {
        $username = Sdy::$login;
        $password = Sdy::$password;
        $server = Sdy::$server;
        $eps = new EPSFacade(new EPSSOAPInterfaceImpl($server), $username, $password);
//  $arrResultOfficesEx = $eps->listOfficesEx($name, $siteId);
//$cou;
        $name = 'pleven';
        $name = null;
        $siteId = null;
        $language = 'BG';
        $countryId = 100;
        $arrResultOfficesEx = $eps->listOfficesEx($name, $siteId, $language, $countryId);
        $arrJsonResult = array();
        $count = count($arrResultOfficesEx);
        if (!empty($count)) {
            $Mysql = new Mysql();
            $Mysql->query('truncate ' . self::cns_table);
            for ($i = 0; $i < count($arrResultOfficesEx); $i++) {
                $query = 'INSERT INTO ' . self::cns_table;
                $query .= ' ( OfficeID, Title, Address, CountryID, SiteID, ';
                $query .= ' PostCode, StreetId, QuarterId, CoordTypeId, CoordX, CoordY ) VALUES ';
                $resultOfficeEx = $arrResultOfficesEx[$i];
                $resultOfficeAddressEx = $resultOfficeEx->getAddress();
                $resultOfficeSite = $resultOfficeAddressEx->getResultSite();
                $commit = ' ';
                $of_title = $resultOfficeEx->getName();
                $of_id = $resultOfficeEx->getId();
                $of_siteid = $resultOfficeAddressEx->getResultSite()->getId();
                $of_full_address = $resultOfficeAddressEx->getFullAddressString();
                $of_full_address = $Mysql->escape_string($of_full_address);
                $of_postcode = $resultOfficeAddressEx->getPostCode();
                $of_StreetId = $resultOfficeAddressEx->getStreetId();
                $of_StreetId = (empty($of_StreetId)) ? 0 : $of_StreetId;
                $of_QuarterId = $resultOfficeAddressEx->getQuarterId();
                $of_QuarterId = (empty($of_QuarterId)) ? 0 : $of_QuarterId;
                $of_CoordX = $resultOfficeAddressEx->getCoordX();
                $of_CoordY = $resultOfficeAddressEx->getCoordY();
                $of_CoordTypeId = $resultOfficeAddressEx->getCoordTypeId();

                $query .= ' ( "' . $of_id . '", "' . $of_title . '", "' . $of_full_address . '", ';
                $query .= ' "' . $countryId . '", "' . $of_siteid . '",';
                $query .= ' "' . $of_postcode . '", "' . $of_StreetId . '", ';
                $query .= ' "' . $of_QuarterId . '", "' . $of_CoordTypeId . '", ';
                $query .= ' "' . $of_CoordX . '", "' . $of_CoordY . '" )';
//                echo "\n" . $query . "\n";
                $Mysql->query($query);
                echo $Mysql->error;
//                break;
            }
            unset($Mysql);
        }

//listOfficesEx($eps);
        return true;
    }

    public function listOfficesEx($eps) {
//    $siteId = $username = $_REQUEST['siteId'];
        $siteId = $username = null;
//    $name = $_REQUEST['name'];
        $name = 'PLEVEN';
        try {
            $arrResultOfficesEx = $eps->listOfficesEx($name, $siteId);
            $arrJsonResult = array();
            for ($i = 0; $i < count($arrResultOfficesEx); $i++) {
                $resultOfficeEx = $arrResultOfficesEx[$i];
                Dump($resultOfficeEx);
                break;
                $resultOfficeAddressEx = $resultOfficeEx->getAddress();
                $resultOfficeSite = $resultOfficeAddressEx->getResultSite();
                $arrJsonResult[$i] = array(
                    "id" => $resultOfficeEx->getId(),
                    "name" => $resultOfficeEx->getName(),
                    "workingTimeFrom" => $resultOfficeEx->getWorkingTimeFrom(),
                    "workingTimeTo" => $resultOfficeEx->getWorkingTimeTo(),
                    "workingTimeHalfFrom" => $resultOfficeEx->getWorkingTimeHalfFrom(),
                    "workingTimeHalfTo" => $resultOfficeEx->getWorkingTimeHalfTo(),
                    "address" => array(
                        "postCode" => $resultOfficeAddressEx->getPostCode(),
                        "streetId" => $resultOfficeAddressEx->getStreetId(),
                        "streetType" => $resultOfficeAddressEx->getStreetType(),
                        "streetName" => $resultOfficeAddressEx->getStreetName(),
                        "streetNo" => $resultOfficeAddressEx->getStreetNo(),
                        "quarterId" => $resultOfficeAddressEx->getQuarterId(),
                        "quarterType" => $resultOfficeAddressEx->getQuarterType(),
                        "quarterName" => $resultOfficeAddressEx->getQuarterName(),
                        "blockNo" => $resultOfficeAddressEx->getBlockNo(),
                        "entranceNo" => $resultOfficeAddressEx->getEntranceNo(),
                        "floorNo" => $resultOfficeAddressEx->getFloorNo(),
                        "apartmentNo" => $resultOfficeAddressEx->getApartmentNo(),
                        "commonObjectId" => $resultOfficeAddressEx->getCommonObjectId(),
                        "commonObjectName" => $resultOfficeAddressEx->getCommonObjectName(),
                        "addressNote" => $resultOfficeAddressEx->getAddressNote(),
                        "coordX" => $resultOfficeAddressEx->getCoordX(),
                        "coordY" => $resultOfficeAddressEx->getCoordY(),
                        "coordTypeId" => $resultOfficeAddressEx->getCoordTypeId(),
                        "fullAddressString" => $resultOfficeAddressEx->getFullAddressString(),
                        "site" => ($resultOfficeSite == null) ?
                        array(
                    "id" => 0,
                    "type" => "",
                    "name" => "",
                    "municipality" => "",
                    "region" => "",
                    "postCode" => "",
                    "addrNomen" => ""
                        ) :
                        array(
                    "id" => $resultOfficeAddressEx->getResultSite()->getId(),
                    "type" => $resultOfficeAddressEx->getResultSite()->getType(),
                    "name" => $resultOfficeAddressEx->getResultSite()->getName(),
                    "municipality" => $resultOfficeAddressEx->getResultSite()->getMunicipality(),
                    "region" => $resultOfficeAddressEx->getResultSite()->getRegion(),
                    "postCode" => $resultOfficeAddressEx->getResultSite()->getPostCode(),
                    "addrNomen" => $resultOfficeAddressEx->getResultSite()->getAddrNomen()->getValue()
                        )
                    )
                );
            }
            $arrJson = array(
                "status" => 0,
                "message" => "OK",
                "exception" => "",
                "result" => $arrJsonResult
            );
        } catch (Exception $sf) {
            $arrJson = array(
                "status" => 1,
                "message" => "Invalid user or communication error",
                "exception" => $sf->getMessage(),
                "result" => array()
            );
        }
        Dump($arrJson);
        exit;
        return $arrJson;
    }

    private function QuaryCreateTable() {
        $query = 'CREATE TABLE `Soffice` (
  `id` int(10) unsigned NOT NULL AUTO_INCREMENT,
  `OfficeID` int(10) unsigned NOT NULL,
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
  PRIMARY KEY (`id`)
) ENGINE=InnoDB AUTO_INCREMENT=1 DEFAULT CHARSET=utf8;
';
    }

}

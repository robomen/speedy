<?php

class SExtractData {
    const BDOFFICE = 'speedy_office';
    const DBCITY = 'speedy_city';

    public function Working() {
        $Mysql = new MySQL();
        $Mysql->CreateOfficeTable();
        // $Mysql->CreateDB($this->DBCITY);

        $username = Sdy::$login;
        $password = Sdy::$password;
        $server = Sdy::$server;
        $eps = new EPSFacade(new EPSSOAPInterfaceImpl($server), $username, $password);
        $name = null;

        // list countries
        $countryList = $eps->listCountriesEx($name);
        echo '<pre>';
        print_r($countryList);
        echo '</pre>';
        exit;
        // Austria, no results for any country
        $arrResultOfficesEx = $eps->listOfficesEx($name, null, null, 40);

        $arrResultOfficesEx = $eps->listOfficesEx($name);
        $arrJsonResult = array();
        $count = count($arrResultOfficesEx);
        if (!empty($count)) {
            // default all offices to be closed - not working anymore, but keep in the DB
            $Mysql->query('UPDATE ' . self::BDOFFICE . ' SET Closed = 1');
            for ($i = 0; $i < count($arrResultOfficesEx); $i++) {
                $resultOfficeEx = $arrResultOfficesEx[$i];
                $query = 'SELECT * FROM ' . self::BDOFFICE . ' WHERE OfficeID = ' . $resultOfficeEx->getId();
                $officeDB = $Mysql->query($query);
                $this->AddOrUpdateOffice($Mysql, $officeDB, $resultOfficeEx);
            }
            unset($Mysql);
        }
        return true;
    }

    private function AddOrUpdateOffice($Mysql, $officeDB, $record) {
        $countryId = 100; // Bulgaria
        $of_id = $record->getId();

        if ($officeDB->num_rows > 0) {
            $query = 'UPDATE ' . self::BDOFFICE . ' SET Closed = 0 WHERE OfficeID = ' . $of_id;
        } else {
            $query = 'INSERT INTO ' . self::BDOFFICE;
            $query .= ' (OfficeID, Title, Address, CountryID, SiteID, PostCode, StreetId, QuarterId, CoordTypeId, CoordX, CoordY, Closed) VALUES ';
            $resultOfficeAddressEx = $record->getAddress();
            $resultOfficeSite = $resultOfficeAddressEx->getResultSite();
            $of_title = $record->getName();
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
            $of_closed = 0;

            $query .= '("' . $of_id . '", "' . $of_title . '", "' . $of_full_address . '", ';
            $query .= '"' . $countryId . '", "' . $of_siteid . '", ';
            $query .= '"' . $of_postcode . '", "' . $of_StreetId . '", ';
            $query .= '"' . $of_QuarterId . '", "' . $of_CoordTypeId . '", ';
            $query .= '"' . $of_CoordX . '", "' . $of_CoordY . '", "' . $of_closed . '")';
        }
        $Mysql->query($query);
        $Mysql->ErrorHandling($Mysql);

        return true;
    }
}

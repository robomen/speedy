<?php

class SExtractData {
    const BDOFFICE = 'speedy_office';
    const DBSTREET = 'speedy_street';
    const DBQUARTER = 'speedy_quarter';
    const DBCITY = 'speedy_city';

    public function Working() {
        $Mysql = new MySQL();
        $Mysql->CreateOfficeTable();
        $Mysql->CreateStreetTable();
        $Mysql->CreateQuarterTable();
        $Mysql->CreateCityTable();

        // clear street table
        $Mysql->query('TRUNCATE ' . self::DBSTREET);
        $Mysql->query('TRUNCATE ' . self::DBQUARTER);

        $username = Sdy::$login;
        $password = Sdy::$password;
        $server = Sdy::$server;
        $eps = new EPSFacade(new EPSSOAPInterfaceImpl($server), $username, $password);
        $name = null;

        // list countries
        // $countryList = $eps->listCountriesEx($name);
        // echo '<pre>';
        // print_r($countryList);
        // echo '</pre>';
        // exit;
        // Austria, no results for any country
        // $arrResultOfficesEx = $eps->listOfficesEx($name, null, null, 40);

        // add cities
        $cities = $eps->listAllSites('BG', 100);
        if (!empty($cities)) {
            foreach ($cities as $city) {
                $query = 'SELECT * FROM ' . self::DBCITY . ' WHERE CityID = ' . $city->getId();
                $cityDB = $Mysql->query($query);
                $this->AddOrUpdateCity($cityDB, $city);
                $citySiteID = $city->getId();
                $cityPostCode = $city->getPostCode();
                // add streets
                $this->AddStreetOrQuarter($eps, $citySiteID, $cityPostCode, 'street');
                // add quarters
                $this->AddStreetOrQuarter($eps, $citySiteID, $cityPostCode, 'quarter');
            }
        }

        // add offices
        $resultOffices = $eps->listOfficesEx($name);
        if (!empty($resultOffices)) {
            // default all offices to be closed - not working anymore, but keep in the DB
            $Mysql->query('UPDATE ' . self::BDOFFICE . ' SET Closed = 1');
            foreach ($resultOffices as $office) {
                $query = 'SELECT * FROM ' . self::BDOFFICE . ' WHERE OfficeID = ' . $office->getId();
                $officeDB = $Mysql->query($query);
                $this->AddOrUpdateOffice($officeDB, $office);
            }
            unset($Mysql);
        }
        return true;
    }

    private function AddOrUpdateOffice($officeDB, $record) {
        $Mysql = new MySQL();
        $countryId = 100; // Bulgaria
        $ofID = $record->getId();

        if ($officeDB->num_rows > 0) {
            $query = 'UPDATE ' . self::BDOFFICE . ' SET Closed = 0 WHERE OfficeID = ' . $ofID;
        } else {
            $query = 'INSERT INTO ' . self::BDOFFICE;
            $query .= ' (OfficeID, Title, Address, CountryID, SiteID, PostCode, StreetId, QuarterId, CoordX, CoordY, CoordTypeID, Closed) VALUES ';
            $resultOfficeAddressEx = $record->getAddress();
            $ofTitle = $record->getName();
            $ofSiteid = $resultOfficeAddressEx->getResultSite()->getId();
            $ofFullAddress = $resultOfficeAddressEx->getFullAddressString();
            $ofFullAddress = $Mysql->escape_string($ofFullAddress);
            $ofPostcode = $resultOfficeAddressEx->getPostCode();
            $ofStreetId = $resultOfficeAddressEx->getStreetId();
            $ofStreetId = (empty($ofStreetId)) ? 0 : $ofStreetId;
            $ofQuarterId = $resultOfficeAddressEx->getQuarterId();
            $ofQuarterId = (empty($ofQuarterId)) ? 0 : $ofQuarterId;
            $ofCoordX = $resultOfficeAddressEx->getCoordX();
            $ofCoordY = $resultOfficeAddressEx->getCoordY();
            $ofCoordTypeId = $resultOfficeAddressEx->getCoordTypeId();
            $ofClosed = 0;

            $query .= '("' . $ofID . '", "' . $ofTitle . '", "' . $ofFullAddress . '", "' . $countryId . '", "' . $ofSiteid . '", "' . $ofPostcode . '", "' . $ofStreetId . '", "' . $ofQuarterId . '", "' . $ofCoordTypeId . '", "' . $ofCoordX . '", "' . $ofCoordY . '", "' . $ofClosed . '")';
        }
        $Mysql->query($query);
        $Mysql->ErrorHandling($Mysql);

        return true;
    }

    private function AddStreetOrQuarter($eps, $siteId, $postCode, $table) {
        $Mysql = new MySQL();
        $name = null;

        if ($table == 'street') {
            $tableDB = self::DBSTREET;
            $columns = '(StreetID, Type, Name, PostCode, SiteID)';
            $records = $eps->listStreets($name, $siteId);
        } elseif ($table == 'quarter') {
            $tableDB = self::DBQUARTER;
            $columns = '(QuarterID, Type, Name, PostCode, SiteID)';
            $records = $eps->listQuarters($name, $siteId);
        } else {
            echo '<pre>';
            print_r('error table name');
            echo '</pre>';
            exit;
        }

        foreach ($records as $record) {
            $id = $record->getId();
            $type = $record->getType();
            $name = $record->getName();
            // $stActualName = $record->getActualName();
            if ($table == 'street') {
                $select = 'SELECT * FROM ' . self::DBSTREET . ' WHERE StreetID = ' . $id;
            } elseif ($table == 'quarter') {
                $select = 'SELECT * FROM ' . self::DBQUARTER . ' WHERE QuarterID = ' . $id;
            }
            $row = $Mysql->query($select);

            if ($row->num_rows == 0) {
                $query = 'INSERT INTO ' . $tableDB . ' ' . $columns . ' VALUES ';
                $query .= '("' . $id . '", "' . $type . '", "' . $name . '", "' . $postCode . '", "' . $siteId . '")';

                $Mysql->query($query);
                $Mysql->ErrorHandling($Mysql);
            }

            $query = null;
        }

        return true;
    }

    private function AddOrUpdateCity($cityDB, $record) {
        $Mysql = new MySQL();
        $id = $record->getId();

        if ($cityDB->num_rows > 0) {
            $query = 'UPDATE ' . self::DBCITY . ' SET Closed = 0 WHERE CityID = ' . $id;
        } else {
            $query = 'INSERT INTO ' . self::DBCITY;
            $query .= ' (CityID, Type, Name, Municpality, Region, PostCode, AddrN, CountryID, ServingOfficeID, CoordX, CoordY, CoordTypeID, ServingDays, Closed) VALUES ';
            $type = $record->getType();
            $name = $record->getName();
            $municipality = $record->getMunicipality();
            $region = $record->getRegion();
            $postCode = $record->getPostCode();
            $addrNomen = $record->getAddrNomen()->getValue();
            $countryId = $record->getCountryId();
            $servingOfficeId = $record->getServingOfficeId();
            $coordX = $record->getCoordX();
            $coordy = $record->getCoordY();
            $coordtype = $record->getCoordType();
            $servingdays = $record->getServingDays();
            $closed = 0;

            if (empty($postCode)) {
                $postCode = 0;
            }

            $query .= '("' . $id . '", "' . $type . '", "' . $name . '", "' . $municipality . '", "' . $region . '", "' . $postCode . '", "' . $addrNomen . '", "' . $countryId . '", "' . $servingOfficeId . '", "' . $coordX . '", "' . $coordy . '", "' . $coordtype . '", "' . $servingdays . '", "' . $closed . '")';
        }
        $Mysql->query($query);
        $Mysql->ErrorHandling($Mysql);

        return true;
    }
}

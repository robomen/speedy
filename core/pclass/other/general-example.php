<?php

// Utility methods
require_once __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'speedy-eps-lib'.DIRECTORY_SEPARATOR.'util'.DIRECTORY_SEPARATOR.'Util.class.php';

// Facade class
require_once __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'speedy-eps-lib'.DIRECTORY_SEPARATOR.'ver01'.DIRECTORY_SEPARATOR.'EPSFacade.class.php';

// Implementation class
require_once __DIR__.DIRECTORY_SEPARATOR.'..'.DIRECTORY_SEPARATOR.'speedy-eps-lib'.DIRECTORY_SEPARATOR.'ver01'.DIRECTORY_SEPARATOR.'soap'.DIRECTORY_SEPARATOR.'EPSSOAPInterfaceImpl.class.php';



// В този пример приемаме, че подателят е този клиент на EPS, който съотвества на профила за автентикация предоставен от Speedy.
// Данните необходими за подател (адрес) ще извличаме от конфигурацията на потребителя в системата на Speedy.
// Ако е необходимо да се използва различен подател, то данните за него се определят по начина, по който се извличат данните за получателя

// Клиентска конфигурация.
$clientConfiguration = new StdClass();
$clientConfiguration->userName = 'XXXXXXXXXXXX';                 // Конфигурирайте името на потребителя преодставен за вас от Speedy
$clientConfiguration->userPassword = 'YYYYYYYYYYYY';             // Конфигурирайте паролата за потребителя преодставен за вас от Speedy
$clientConfiguration->arrEnabledServices = array(0=>505);  // Конфигурирайте ограничен списък от услуги на Speedy, с които клиентът ще работи
$clientConfiguration->contactName='ПЕТЪР ПЕТРОВ';                // Конфигурирайте име за контакт на подателя при откриване на товарителници и заявки за куриер
$clientConfiguration->contactPhone='0888 888 888';               // Конфигурирайте телефон за контакт на подателя при откриване на товарителници и заявки за куриер

// Данни за получателя - помощна структура с примерни данни
$recieverData = new StdClass();
$recieverData->address = new StdClass();
$recieverData->address->siteType    = 'гр';
$recieverData->address->siteName    = 'БУРГАС';
$recieverData->address->quarter     = 'СЛАВЕЙКОВ';
$recieverData->address->blockNo     = '62';
$recieverData->address->street      = null;
$recieverData->address->streetNo    = null;
$recieverData->address->entranceNo  = '2';
$recieverData->address->floorNo     = '4';
$recieverData->address->apartmentNo = '12';
$recieverData->partnerName          = 'ИВАНОВИ ООД';
$recieverData->contactName          = 'TEST TEST TEST';
$recieverData->contactPhone         = '7001 7001';

// Данни за пратката - помощна структура с примерни данни
$pickingData = new StdClass();
$pickingData->weightDeclared   = 5.25; // Декларирано тегло (например 5.25 кг)
$pickingData->bringToOfficeId  = null; // Офис, в който подателя ще достави пратката. Ако е null, куриер ще я вземе от адреса на подателя
$pickingData->takeFromOfficeId = 119; // Офис, от който получателя ще вземе пратката. Ако е null, куриер ще я достави до адреса на получателя
$pickingData->parcelsCount     = 1; // Брой пакети
$pickingData->documents        = false; // Флаг дали пратката се състои от документи
$pickingData->palletized       = false; // Флаг дали пратката се състои от палети
$pickingData->fragile          = false; // Флаг дали пратката се състои от палети
$pickingData->payerType        = ParamCalculation::PAYER_TYPE_RECEIVER; // Платецът е получателят
$pickingData->amountCODBase    = 25; // Наложен платеж 25 лв.
$pickingData->backDocumentReq  = true; // Заявка за обратни документи
$pickingData->backReceiptReq   = false; // Заявка за обратна разписка
$pickingData->contents         = 'Дрехи'; // Съдържание на пратката
$pickingData->packing          = 'ПАКЕТ'; // Опаковка на пратката
$pickingData->autoAdjust       = true;    // Auto-adjust флаг при определяне на датата на вземане

$outputPDFFolder = __DIR__.'\\'; // Директория, където се записват pdf файловете с товарителници и етикети за печат

try {

    header("Content-Type: text/html; charset=utf-8");

    // Иницализация на времевата зона.
    // Препоръчва се параметрите и аргументите от тип datetime да са форматирани във времевата зона на Спиди, 
    // поради специфики при определяне на датата и времето в някои от подаваните стойности
    if (function_exists("date_default_timezone_set")) {
        date_default_timezone_set(Util::SPEEDY_TIME_ZONE);
        $timeZone = date_default_timezone_get();
    } else {
        putenv("TZ=".Util::SPEEDY_TIME_ZONE);
        $timeZone = getenv("TZ");
    }


    echo "<pre>";
    echo "<br><br>";
    echo "=========================================================================================================<br>";
    echo "НАЧАЛО<br>";
    echo "=========================================================================================================<br>";
    echo "<i><small><strong>(PHP Version: ".phpversion().", PHP EPS Client Library Version: ".Util::getLibVersion().", Time zone: ".$timeZone.")</strong></small></i><br><br><br>";
    echo "--------------------------------------------------------------------------------------------------------<br>";
    echo "USER NAME: ".$clientConfiguration->userName."<br>";
    echo "--------------------------------------------------------------------------------------------------------<br><br>";

    //-------------------------------------------------------------------------------------------------------------------
    // ИНИЦИАЛИЗАЦИЯ И ПРЕДВАРИТЕЛНА КАЛКУЛАЦИЯ НА ЦЕНА
    //-------------------------------------------------------------------------------------------------------------------

    // Инициализация на интерфейса към EPS на Спиди със SOAP имплементация
    $eps = new EPSFacade(new EPSSOAPInterfaceImpl(), $clientConfiguration->userName,  $clientConfiguration->userPassword);

    // Установяване на сесия
    echo "Установяване на сесия [login]<br>";
    $resultLogin = $eps->getResultLogin();
    echo "--------------------------------------------------------------------------------------------------------<br>";
    echo "TIMESTAMP: ".date('y-m-d H:i:s T', time())."  CLIENTID: ".$resultLogin->getClientId()."  SESSIONID: ".$resultLogin->getSessionId()."<br>";
    echo "--------------------------------------------------------------------------------------------------------<br>";

    // Извличане данните на регистрирания потребител на EPS (в този пример - ПОДАТЕЛЯ)
    echo "<br><br><br><br>";
    echo "------------------------------------------------------------------------------------------------<br>";
    echo "Извличане на данни за подателя (клиента на EPS) от профила в системата на Speedy [getClientById]<br>";
    echo "------------------------------------------------------------------------------------------------<br>";
    echo "Параметри:<br>";
    echo "    {clientId}: ".$resultLogin->getClientId()."<br>";
    $senderClientData = $eps->getClientById($resultLogin->getClientId());
    echo "<br><br>";
    echo "Резултат:<br>";
    echo "---------<br>";
    var_dump($senderClientData);

    // Идентификатор на населеното място на подателя
    $senderSiteId = $senderClientData->getAddress()->getSiteId();

    echo "<br><br><br><br>";
    echo "Идентификатор на населено място на подателя (от номенклатурата на Speedy):<br>";
    echo "--------------------------------------------------------------------------<br>";
    echo "".$senderSiteId."<br>";

    // Определяме населеното място на получателя
    echo "<br><br><br><br>";
    echo "--------------------------------------------------------------------------------------------------<br>";
    echo "Извличане на населени места на получателя по тип и име (от номенклатурата на Speedy) [listSitesEx]<br>";
    echo "--------------------------------------------------------------------------------------------------<br>";
    echo "Параметри:<br>";
    $paramFilterSite = new ParamFilterSite();
    $paramFilterSite->setType($recieverData->address->siteType);
    $paramFilterSite->setName($recieverData->address->siteName);
    var_dump($paramFilterSite);
    $arrResultSiteEx = $eps->listSitesEx($paramFilterSite);
    var_dump($arrResultSiteEx);
    if (count($arrResultSiteEx) == 0) {
        // Населеното място на получателя не е намерено
        throw new ClientException("Населеното място на получателя не е намерено.");
    } else if (count($arrResultSiteEx) == 1) {
        // Населеното място на получателя е намерено и еднозначно определено
        echo "<br>";
        echo "Намерено е точно едно възможно населено място на получателя с посочения критерий.<br>";
        $resultSiteEx = $arrResultSiteEx[0];
    } else {
        // Намерени са няколко населени места с това име. Потребителят e необходимо да избере от списъка
        echo "<br>";
        echo "Намерени са няколко възможни населени места на получателя с посочения критерий. Необходимо е да се избере една от възможностите.<br>";
        echo "За целите на този пример е избрано първото населено място от резултатния списък.<br>";
        $resultSiteEx = $arrResultSiteEx[0];
    }
    $resultSite = $resultSiteEx->getSite();
    $receiverSiteId = $resultSite->getId();
    echo "<br><br>";
    echo "Идентификатор на населено място на получателя (от номенклатурата на Speedy):<br>";
    echo "----------------------------------------------------------------------------<br>";
    echo "".$receiverSiteId."<br>";
    
    // Задаване на дата/време на вземане на пратката от куриер.
    // Обикновено се използва дата (без времеви компонент във времевата зона на Спиди), но е възможно да се зададе и времеви компонент,
    // като в този случай, това обикновено е времето когато пратката ще бъде окомплектована и готова за вземане.
    // Трябва да се има в предвид, че датата на вземане се използва в методите за определяне на наличните услуги за населените места,
    // и влияе на стойността на калкулацията и на куриерската услуга. С други думи, една и съща пратка може да има различна цена
    // на куриерската услуга в зависимост от деня на нейното вземане от куриер
    $pickingData->takingDate = strtotime(date("Y-m-d")); // За простота използваме настоящия ден (без времеви компонент във времевата зона на Спиди)

    // Датата на вземане може да се определи с използване на метод getAllowedDaysForTaking за определена услуга, както е показано
    // в коментирания код по-долу. Клиентът обикновено използва свой собствен график за вземане на пратките , но той трябва да бъде
    // синхронизиран с този на Speedy.
    // Методът връща нареден списък от възможни времена считано от настоящия момент на запитването в рамките на следващите 5 работни дни,
    // като този списък може да бъде допълнително филтриран, чрез подаване на стойност в параметъра minDate.
    // При minDate=null, се приема че запитването е за възможни дати за вземане от момента на запитването
    // Ако minDate е инициализиран с време преди времето на настоящия момент отново се счита, 
    // че запитването е за възможни дати за вземане от момента на запитването
    // Ако minDate е инициализиран с време, съотвестващо на бъдещ момент спрямо момента на запитването, то списъка се филтирира допълнително, 
    // като отпадат тези дати, които са предишни календарни дати спрямо minDate. При наличие на времеви компонент в minDate, 
    // допълнително се определя дали подадения час на вземане е в рамките на работното време на Спиди за този ден и 
    // съответно денят може да отпадне от възможните дни за вземане при негативен резултат.
    // Необходимо е да се отбележи, че наличието на времеви компонент (ненулева стойност за час, минута, секунда или милисекунда) се определя
    // спрямо часовата зона на Спиди.
/*
    $serviceTypeID = 3; // Целим да определим дата на вземане за услуга 3 от номенклатурата на Спиди
    echo "<br><br><br><br>";
    echo "Определяне на времето за вземане считано от настоящия момент [getAllowedDaysForTaking]:<br>";
    echo "---------------------------------------------------------------------------------------<br>";
    echo "Параметри:<br>";
    echo "    {serviceTypeID} : ".$serviceTypeID."<br>";
    echo "    {senderSiteId}  : ".(is_null($pickingData->bringToOfficeId) ? "".$senderSiteId : "null")."<br>";
    echo "    {senderOfficeId}: ".(is_null($pickingData->bringToOfficeId) ? "null" : "".$pickingData->bringToOfficeId)."<br>";
    echo "    {minDate}       : null<br>";
    $arrTakingDates = $eps->getAllowedDaysForTaking(
    $serviceTypeID,
        is_null($pickingData->bringToOfficeId) ? $senderSiteId : null,
        $pickingData->bringToOfficeId,
        null
    );
    echo "<br><br>";
    echo "Резултат:<br>";
    echo "---------<br>";
    var_dump($arrTakingDates);
    if (count($arrTakingDates) == 0) {
        throw new ClientException("Не са налични дати за вземане на пратката.");
    } else if (count($arrTakingDates) == 1) {
        // Датата за вземане е една и е единствено възможна
        $takingDate = $arrTakingDates[0];
    } else {
        // Възможни са няколко дати за вземане. Потребителят избира една от възможните дати (например първата от тях)
        $takingDate = $arrTakingDates[0];
    }
*/

    echo "<br><br><br><br>";
    echo "Време на вземане:<br>";
    echo "-----------------<br>";
    echo (is_null($pickingData->takingDate) ? "null" : date('Y-m-d H:i:s T', $pickingData->takingDate))."<br>";


    // Извличане на списъка от възможните услуги за избрания маршрут и определяне на сечението им с избраните от клиента
    echo "<br><br><br><br>";
    echo "-------------------------------------------------------------------------------------------------------<br>";
    echo "Извличане на списъка от възможните услуги за избрания маршрут и време на вземане [listServicesForSites]<br>";
    echo "-------------------------------------------------------------------------------------------------------<br>";
    echo "Параметри:<br>";
    echo "    {date}          : ".(is_null($pickingData->takingDate) ? "null" : date("Y-m-d H:i:s T", $pickingData->takingDate))."<br>";
    echo "    {senderSiteId}  : ".$senderSiteId."<br>";
    echo "    {receiverSiteId}: ".$receiverSiteId."<br>";
    $arrAvailableServices = $eps->listServicesForSites($pickingData->takingDate, $senderSiteId, $receiverSiteId);
    echo "<br><br>";
    echo "Резултат:<br>";
    echo "---------<br>";
    var_dump($arrAvailableServices);

    // Определеляне на сечението между възможните услуги и конфигурираните за клиента услуги (списъка с услуги, с които клиента работи)
    echo "<br><br>";
    echo "-----------------------------------------------------------------------------------<br>";
    echo "Филтриране на списъка от възможните услуги според конфигурираните за клиента услуги<br>";
    echo "-----------------------------------------------------------------------------------<br>";
    $arrSelectedServices = Util::serviceIntersection($arrAvailableServices, $clientConfiguration->arrEnabledServices);
    echo "<br><br>";
    echo "Резултат:<br>";
    echo "---------<br>";
    var_dump($arrSelectedServices);

    // Филтриране на списъка от възможни услуги според възможните стойности за тегло
    // Извличане на списъка от възможните услуги за избрания маршрут и определяне на сечението им с избраните от клиента
    echo "<br><br>";
    echo "---------------------------------------------------------------------------------------------------<br>";
    echo "Филтриране на списъка от възможните услуги според възможните стойности за тегло [getWeightInterval]<br>";
    echo "---------------------------------------------------------------------------------------------------<br>";
    echo "Параметри:<br>";
    echo "    {weightDeclared}: ".$pickingData->weightDeclared."<br>";
    echo "    {senderSiteId}  : ".$senderSiteId."<br>";
    echo "    {receiverSiteId}: ".$receiverSiteId."<br>";
    echo "    {date}          : ".(is_null($pickingData->takingDate) ? "null" : date("Y-m-d H:i:s T", $pickingData->takingDate))."<br>";
    echo "    {documents}     : ".($pickingData->documents ? "true" : "false")."<br>";
    $arrFinalServices = Util::filterServicesByWeightIntervals(
            $arrSelectedServices, $pickingData->weightDeclared, $eps, $senderSiteId, $receiverSiteId, $pickingData->takingDate, $pickingData->documents
    );
    echo "<br><br>";
    echo "Резултат:<br>";
    echo "---------<br>";
    var_dump($arrFinalServices);

    if (count($arrFinalServices) == 0) {
        // Нямаме услуга за тази пратка и тя не може да бъде изпълнена
        throw new ClientException("Не е налична услуга за тази пратка.");
    }

    // Параметри за калкулация
    $paramCalculation = new ParamCalculation();
    $paramCalculation->setBroughtToOffice(!is_null($pickingData->bringToOfficeId));
    $paramCalculation->setToBeCalled(!is_null($pickingData->takeFromOfficeId));
    $paramCalculation->setParcelsCount($pickingData->parcelsCount );
    $paramCalculation->setWeightDeclared($pickingData->weightDeclared);
    $paramCalculation->setDocuments($pickingData->documents);
    $paramCalculation->setPalletized($pickingData->palletized);
    $paramCalculation->setFragile($pickingData->fragile);

    //При изпозване на $paramCalculation->setSenderSiteId($senderSiteId) вместо paramCalculation.setSenderId($senderClientData->getClientId())
    //не се използват преференциите на подателя по договор
    //$paramCalculation->setSenderSiteId($senderSiteId);
    $paramCalculation->setSenderId($senderClientData->getClientId());

    $paramCalculation->setReceiverSiteId($receiverSiteId);
    $paramCalculation->setPayerType($pickingData->payerType);
    $paramCalculation->setAmountCodBase($pickingData->amountCODBase);
    $paramCalculation->setTakingDate($pickingData->takingDate);
    $paramCalculation->setAutoAdjustTakingDate($pickingData->autoAdjust); // Adjust to first allowed taking date

    if (count($arrFinalServices) == 1) {

        // Имаме точно една налична услуга
        $serviceTypeID = $arrFinalServices[0];

        // Задаване на услугата в параметрите за калкулация
        $paramCalculation->setServiceTypeId($serviceTypeID);

        echo "<br><br>";
        echo "----------------------<br>";
        echo "Калкулация [calculate]<br>";
        echo "----------------------<br>";
        echo "Параметри на калкулацията:<br>";
        var_dump($paramCalculation);
         // Калкулация. Резултатът съдържа цена и компоненти на ценоообразуването, както и срокове за вземане и доставка.
        $resultCalculation = $eps->calculate($paramCalculation);
        echo "<br><br>";
        echo "Резултат:<br>";
        echo "---------<br>";
        var_dump($resultCalculation);

        // От резултата можем да извлечем първата възможна дата определена от Спиди (само ако сме задали в заявката $paramCalculation->setAutoAdjustTakingDate(true) )
        if ($pickingData->autoAdjust) {
            $pickingData->takingDate = $resultCalculation->getTakingDate();
        }

    } else {

        // Имаме няколко възможни услуги. Потребителят трябва да избере една от наличните услуги.
        // Можем да направим калкулация на всички възможни услуги и потребителя да избере като сравнява цени и крайна дата на доставка
        echo "<br><br>";
        echo "---------------------------------------------------------------------<br>";
        echo "Едновременна калкулация на няколко услуги [calculateMultipleServices]<br>";
        echo "---------------------------------------------------------------------<br>";
        echo "Параметри на калкулацията:<br>";
        var_dump($paramCalculation);
        $arrResultCalculationMS = $eps->calculateMultipleServices($paramCalculation, $arrFinalServices);

        echo "<br><br>";
        echo "Резултат:<br>";
        echo "---------<br>";
        var_dump($arrResultCalculationMS);

        if (count($arrResultCalculationMS) > 0) {
            // Потребителят прави избор според резултатите от калкулацията (цена, време на доставка и т.н.)
            echo "<br>";
            echo "След сравнение на калкулациите е необходимо да се избере куриерската услуга.<br>";
            echo "За целите на този пример е избрана първата услуга от списъка с възможности.<br>";
            $resultCalculationMS = $arrResultCalculationMS[0];
            $serviceTypeID = $resultCalculationMS->getServiceTypeId();

            // От резултата можем да извлечем първата възможна дата определена от Спиди (само ако сме задали в заявката $paramCalculation->setAutoAdjustTakingDate(true) )
            if ($pickingData->autoAdjust) {
                $pickingData->takingDate = $resultCalculationMS->getResultInfo()->getTakingDate();
            }
        } else {
            throw new ClientException("Липса на резултат от паралелна калкулация за множество услуги.");
        }
    }

    echo "<br><br><br><br>";
    echo "Избрана услуга и дата на вземане:<br>";
    echo "---------------------------------<br>";
    echo "    Идентификатор на услуга: ".$serviceTypeID."<br>";
    echo "    Дата на вземане        : ".$pickingData->takingDate."<br>";

    //-------------------------------------------------------------------------------------------------------------------

    //-------------------------------------------------------------------------------------------------------------------
    // ОПРЕДЕЛЯНЕ АДРЕС НА ПОЛУЧАТЕЛ
    //-------------------------------------------------------------------------------------------------------------------

    // Примерен адрес на получател: гр.БУРГАС, жк. СЛАВЕЙКОВ, бл.62, вх.2, ет.4, ап.12

    // Задаване на адрес на получател
    $receiverAddress = new ParamAddress();
    $receiverAddress->setSiteId($receiverSiteId);

    // При разбит адрес определяме компонентите на адреса
    // Ако адресът не е разбит на компоненти е позволено подаването на целия адрес в полето addressNote
    //     $receiverAddress->setAddressNote("к-с СЛАВЕЙКОВ, бл.62, вх.2, ет.4, ап.12, МЕТАЛНАТА РЕШЕТКА СРЕЩУ АСАНСЬОРА")
    // В addressNote се попълва само адреса в рамките на населеното място (т.е. без самото наименование на населеното място)
    // Използването на този метод на работа не се препоръчва, защото при послдващата обработка на пратката може да се получи
    // нееднозначност на адреса, която от своя страна може да доведе до евентуално забавяне на доставката

    // Определяне на квартал
    if (!is_null($recieverData->address->quarter)) {
        echo "<br><br>";
        echo "Извличане на квартали [listQuarters]:<br>";
        echo "-------------------------------------<br>";
        echo "Параметри:<br>";
        echo "    {name}  : ".$recieverData->address->quarter."<br>";
        echo "    {siteId}: ".$receiverSiteId."<br>";
        $arrQuarters = $eps->listQuarters($recieverData->address->quarter, $receiverSiteId);
        echo "<br><br>";
        echo "Резултат:<br>";
        echo "---------<br>";
        var_dump($arrQuarters);
        if (count($arrQuarters) == 0) {
            throw new ClientException("Не е намерен квартал/комплекс");
        } else if (count($arrQuarters) == 1) {
            // Комплексът/кварталът е еднозначно определен
            echo "<br>";
            echo "Намерен e точно един квартал с посочения критерий.<br>";
            $receiverAddress->setQuarterId($arrQuarters[0]->getId());
        } else {
            // Комплексът/кварталът не е еднозначно определен и потребителят е необходимо да избере от възможностите
            echo "<br>";
            echo "Намерени са няколко квартала с посочения критерий. Необходимо е да се избере една от възможностите.<br>";
            echo "За целите на този пример е избран първия квартал от резултатния списък.<br>";
            $receiverAddress->setQuarterId($arrQuarters[0]->getId());
        }
    }
    // Определяне на улица (в нашия пример нямаме улица)
    if (!is_null($recieverData->address->street)) {
        echo "<br><br>";
        echo "Извличане на улици [listStreets]:<br>";
        echo "---------------------------------<br>";
        echo "Параметри:<br>";
        echo "    {name}  : ".$recieverData->address->street."<br>";
        echo "    {siteId}: ".$receiverSiteId."<br>";
        $arrStreets = $eps->listStreets($recieverData->address->street, $receiverSiteId);
        echo "<br><br>";
        echo "Резултат:<br>";
        echo "---------<br>";
        var_dump($arrStreets);
        if (count($arrStreets) == 0) {
            throw new ClientException('Не е намерена улица');
        } else if (count($arrStreets) == 1) {
            // Улицата е еднозначно определена
            echo "<br>";
            echo "Намерен e точно една улица с посочения критерий.<br>";
            $receiverAddress->setStreetId($arrStreets[0]->getId());
        } else {
            // Улицата не е еднозначно определена и потребителят е необходимо да избере от възможностите
            echo "<br>";
            echo "Намерени са няколко улици с посочения критерий. Необходимо е да се избере една от възможностите.<br>";
            echo "За целите на този пример е избрана първата улица от резултатния списък.<br>";
            $receiverAddress->setStreetId($arrStreets[0]->getId());
        }
    }
    $receiverAddress->setBlockNo($recieverData->address->blockNo);
    $receiverAddress->setStreetNo($recieverData->address->streetNo);
    $receiverAddress->setEntranceNo($recieverData->address->entranceNo);
    $receiverAddress->setFloorNo($recieverData->address->floorNo);
    $receiverAddress->setApartmentNo($recieverData->address->apartmentNo);

    echo "<br><br><br><br>";
    echo "Адрес на получател:<br>";
    echo "-------------------<br>";
    var_dump($receiverAddress);

    //-------------------------------------------------------------------------------------------------------------------

    //-------------------------------------------------------------------------------------------------------------------
    // ОПРЕДЕЛЯНЕ НА ДАННИ ЗА ПОДАТЕЛ И ПОЛУЧАТЕЛ И ОТКРИВАНЕ НА ТОВАРИТЕЛНИЦА
    // ПОДАТЕЛ: Клиент на EPS
    // ПОЛУЧАТЕЛ: ИВАНОВИ ООД, с лице за контакт ИВАН ИВАНОВ, тел. 0888 888 888
    //-------------------------------------------------------------------------------------------------------------------

    // Данни за подател
    $sender = new ParamClientData();
    $sender->setClientId($senderClientData->getClientId());
    $sender->setContactName($clientConfiguration->contactName);
    $senderPhoneNumber = new ParamPhoneNumber();
    $senderPhoneNumber->setNumber($clientConfiguration->contactPhone);
    $sender->setPhones(array(0 => $senderPhoneNumber));

    echo "<br><br><br><br>";
    echo "Данни за подател:<br>";
    echo "-----------------<br>";
    var_dump($sender);

    // Данни за получател
    $receiver = new ParamClientData();
    //Предвиждаме пратката да е до поискване, в този случай не подаваме адрес на получател,
    //защото адреса на получателя е адреса на офиса до пискване
    //receiver.setAddress(receiverAddress);
    $receiver->setPartnerName($recieverData->partnerName);
    $paramPhoneNumber = new ParamPhoneNumber();
    $paramPhoneNumber->setNumber($recieverData->contactPhone);
    $receiver->setPhones(array(0 => $paramPhoneNumber));
    $receiver->setContactName($recieverData->contactName);

    echo "<br><br>";
    echo "Данни за получател:<br>";
    echo "-------------------<br>";
    var_dump($receiver);

/*
    // Данни за товарителница
    echo "<br><br><br><br>";
    echo "Откриване на товарителница за пратка 1 [createBillOfLading]...<br>";
    echo "--------------------------------------------------------------<br>";
    $picking = new ParamPicking();
    $picking->setServiceTypeId($serviceTypeID);
    $picking->setBackDocumentsRequest($pickingData->backDocumentReq);
    $picking->setBackReceiptRequest($pickingData->backReceiptReq);
    //$picking->setWillBringToOffice(!is_null($pickingData->bringToOfficeId));
    $picking->setWillBringToOfficeId($pickingData->bringToOfficeId);
    $picking->setParcelsCount($pickingData->parcelsCount);
    $picking->setWeightDeclared($pickingData->weightDeclared);
    $picking->setContents($pickingData->contents);
    $picking->setPacking($pickingData->packing);
    $picking->setDocuments($pickingData->documents);
    $picking->setPalletized($pickingData->palletized);
    $picking->setFragile($pickingData->fragile);
    $picking->setSender($sender);
    $picking->setReceiver($receiver);
    $picking->setPayerType($pickingData->payerType);
    $picking->setAmountCodBase($pickingData->amountCODBase);
    $picking->setOfficeToBeCalledId($pickingData->takeFromOfficeId);
    $picking->setTakingDate($pickingData->takingDate);

    echo "Данни на товарителница за пратка 1:<br>";
    var_dump($picking);
    // Откриване на товарителница. Откриването е правилно да се прави след окомплектоване на пратката, а не при поръчка (в онлайн магазина)
    $resultBOL = $eps->createBillOfLading($picking);
    echo "<br><br>";
    echo "Резултат:<br>";
    echo "---------<br>";
    var_dump($resultBOL);

    // Идентификатор на откритата товарителница
    $arrParcels = $resultBOL->getGeneratedParcels();
    $pickingId  = $arrParcels[0]->getParcelId();

    echo "<br><br>";
    echo "Товарителницата за пратка 1 е открита с No.".$pickingId."<br>";

    // Печат на товарителница
    $paramPDF = new ParamPDF();
    $paramPDF->setIds(array(0=>$pickingId));
    $paramPDF->setType(ParamPDF::PARAM_PDF_TYPE_BOL);
    $paramPDF->setIncludeAutoPrintJS(true);
    echo "<br><br>";
    echo "Печат на товарителница No.".$pickingId." за пратка 1 [createPDF]...<br>";
    echo "<br><br>";
    echo "Параметри:<br>";
    echo "----------<br>";
    var_dump($paramPDF);
        // Запис на pdf-а на товарителницата във файл
    $fileNameOnly = $eps->getUsername()."_picking_".$pickingId."_".time().".pdf";
    $fileName = $outputPDFFolder.$fileNameOnly;
    file_put_contents($fileName, $eps->createPDF($paramPDF), FILE_APPEND | LOCK_EX);
    echo "<br>";
    echo "Tоварителница No.".$pickingId." за пратка 1 е съхранена във файл: ".$fileName."<br>";
*/
    //------------------------------------------------------------------------------------------------------

    //------------------------------------------------------------------------------------------------------
    // Коментираният в секцията по-долу код може да се ползва за печат на етикет за пратка 1,
    // вместо печат на товарителница
    //------------------------------------------------------------------------------------------------------
/*
    // Печат на етикет
    $paramPDF = new ParamPDF();
    $paramPDF->setIds(array(0=>$pickingId));
    $paramPDF->setType(ParamPDF::PARAM_PDF_TYPE_LBL);
    $paramPDF->setIncludeAutoPrintJS(true);
    echo "<br><br>";
    echo "Печат на етикет за пратка 1 с товарителница No.".$pickingId." [createPDF]...<br>";
    echo "<br><br>";
    echo "Параметри:<br>";
    echo "----------<br>";
    var_dump($paramPDF);

    // Запис на pdf-а на етикет във файл
    $fileNameOnly = $eps->getUsername()."_lbl_".$pickingId."_".time().".pdf";
    $fileName = $outputPDFFolder.$fileNameOnly;
    file_put_contents($fileName, $eps->createPDF($paramPDF), FILE_APPEND | LOCK_EX);

    echo "<br>";
    echo "Етикет за пратка 1 с товарителница No.".$pickingId." е съхранен във файл: ".$fileName."<br>";
*/
    //-------------------------------------------------------------------------------------------------------------------


    //-------------------------------------------------------------------------------------------------------------------
    // ОТКРИВАНЕ НА ВТОРА ТОВАРИТЕЛНИЦА (ПО-КОМПЛЕКСЕН ВАРИАНТ - ТРИПАКЕТНА СЪС ЗАСТРАХОВКА)
    //-------------------------------------------------------------------------------------------------------------------
/*
    //Предвиждаме пратката да е до адрес на получателя, в този случай е адрес на получател е необхдим.
    $receiver->setAddress($receiverAddress);
    // Данни за товарителница
    echo "<br><br><br><br>";
    echo "Откриване на товарителница за пратка 2 [createBillOfLading]...<br>";
    echo "--------------------------------------------------------------<br>";
    $picking2 = new ParamPicking();
    $picking2->setServiceTypeId($serviceTypeID);
    $picking2->setBackDocumentsRequest($pickingData->backDocumentReq);
    $picking2->setBackReceiptRequest($pickingData->backReceiptReq);
    $picking2->setWillBringToOffice(!is_null($pickingData->bringToOfficeId));

    $picking2->setWeightDeclared($pickingData->weightDeclared);
    $picking2->setContents($pickingData->contents);
    $picking2->setPacking($pickingData->packing);
    $picking2->setDocuments($pickingData->documents);
    $picking2->setPalletized($pickingData->palletized);
    $picking2->setFragile($pickingData->fragile);
    $picking2->setSender($sender);
    $picking2->setReceiver($receiver);
    $picking2->setPayerType($pickingData->payerType);
    $picking2->setTakingDate($pickingData->takingDate);
    $picking2->setAmountInsuranceBase(20);
    $picking2->setPayerTypeInsurance(ParamCalculation::PAYER_TYPE_SENDER);
    $picking2->setFragile(true);
    $picking2->setParcelsCount(3); // Пратка с 3 пакета

    echo "Данни на товарителница за пратка 2:<br>";
    var_dump($picking2);
    // Откриване на товарителница. Откриването се прави след окомплектоване на пратката, а не при поръчка (в онлайн магазина)
    $resultBOL2 = $eps->createBillOfLading($picking2);
    echo "<br><br>";
    echo "Резултат:<br>";
    echo "---------<br>";
    var_dump($resultBOL2);

    // Идентификатор на откритата товарителница. Идентификаторът на откритата товарителница е и идентификатор на първия пакет
    $arrParcels = $resultBOL2->getGeneratedParcels();
    $pickingId2 = $arrParcels[0]->getParcelId();

    $pickingId2Parcel1Id = $pickingId2; // Същото като $arrParcels[0]->getParcelId();
    $pickingId2Parcel2Id = $arrParcels[1]->getParcelId();
    $pickingId2Parcel3Id = $arrParcels[2]->getParcelId();

    echo "<br><br>";
    echo "Товарителницата за пратка 2 е открита с No.".$pickingId2."<br>";
*/
    //-------------------------------------------------------------------------------------------------------------------


    //------------------------------------------------------------------------------------------------------
    // Коментираният в секцията по-долу код може да се ползва за печат на окритата товарителница за пратка 2
    //------------------------------------------------------------------------------------------------------
/*
    $paramPDF = new ParamPDF();
    $paramPDF->setIds(array(0=>$pickingId2));
    $paramPDF->setType(ParamPDF::PARAM_PDF_TYPE_BOL);
    $paramPDF->setIncludeAutoPrintJS(true);
    echo "<br><br>";
    echo "Печат на товарителница No.".$pickingId2." за пратка 2 [createPDF]...<br>";
    echo "<br><br>";
    echo "Параметри:<br>";
    echo "----------<br>";
    var_dump($paramPDF);

    // Запис на pdf-а на товарителницата във файл
    $fileNameOnly = $eps->getUsername()."_picking_".$pickingId2."_".time().".pdf";
    $fileName = $outputPDFFolder.$fileNameOnly;
    file_put_contents($fileName, $eps->createPDF($paramPDF), FILE_APPEND | LOCK_EX);

    echo "<br>";
    echo "Tоварителница No.".$pickingId2." за пратка 2 е съхранена във файл: ".$fileName."<br>";
*/
    //-------------------------------------------------------------------------------------------------------


    // ------------------------------------------------------------------------------------------------------
    // Групов печат на етикети за всеки пакет по окритата товарителница за пратка 2.
    // (Етикетите могат да се печат и последователно един по един, по същия начин,
    // с подаване на списък с един идентификатор на сътоветния пакет в аргументите на метода за печат)
    // ------------------------------------------------------------------------------------------------------
/*
    $paramPDF = new ParamPDF();
    $paramPDF->setIds(array(0=>$pickingId2Parcel1Id, 1=>$pickingId2Parcel2Id, 2=>$pickingId2Parcel3Id));
    $paramPDF->setType(ParamPDF::PARAM_PDF_TYPE_LBL);
    $paramPDF->setIncludeAutoPrintJS(true);
    echo "<br><br>";
    echo "Групов печат на етикети за пратка 2 с товарителница No.".$pickingId2." [createPDF]...<br>";
    echo "<br><br>";
    echo "Параметри:<br>";
    echo "----------<br>";
    var_dump($paramPDF);

    // Запис на pdf-а на етикетите във файл
    $fileNameOnly = $eps->getUsername()."_lbl_".$pickingId2."_".time().".pdf";
    $fileName = $outputPDFFolder.$fileNameOnly;
    file_put_contents($fileName, $eps->createPDF($paramPDF), FILE_APPEND | LOCK_EX);

    echo "<br>";
    echo "Етикетите за пратка 2 с товарителница No.".$pickingId2." са съхранени във файл: ".$fileName."<br>";
*/
    //-----------------------------------------------------------------------------------------------------


    //-------------------------------------------------------------------------------------------------------------------
    // ЗАЯВКА ЗА КУРИЕР
    // Заявката се прави в края на работния ден - за предпочитане веднъж дневно, като включва всички окомплектовани пратки за деня
    // За целта се подава списък от всички пратки, които са за този ден.
    //-------------------------------------------------------------------------------------------------------------------
/*
    // Данни за заявка за куриер
    // ReadinessTime не може да бъде време преди текущото време на генериране на заявката
    echo "<br><br><br><br>";
    echo "Заявка за куриер за двете окомплектовани пратки [createOrder]...<br>";
    echo "----------------------------------------------------------------<br>";
    $order = new ParamOrder();
    $order->setBillOfLadingsList(array(0 => $pickingId, 1 => $pickingId2));             // Списък от товарителници
    $order->setBillOfLadingsToIncludeType(ParamOrder::ORDER_BOL_INCLUDE_TYPE_EXPLICIT); // Заявка за куриер за списъка
    $order->setPickupDate($pickingData->takingDate);                                    // Дата на вземане на пратката от куриер
    $order->setReadinessTime(1730);                                                     // Пакетите са готови за вземане след 17:30
    $order->setContactName($clientConfiguration->contactName);                          // Име за контакт
    $paramPhoneNumber = new ParamPhoneNumber();
    $paramPhoneNumber->setNumber($clientConfiguration->contactPhone);
    $order->setPhoneNumber($paramPhoneNumber);                                          // Тел. номер за контакт
    $order->setWorkingEndTime(1800);                                                    // Край на работното време на подателя - 18:00

    echo "<br><br>";
    echo "Данни на заявката за куриер за двете окомплектовани пратки:<br>";
    var_dump($order);

    // Създаване на заявка
    $arrResultOrderPickingInfo = $eps->createOrder($order);

    echo "<br>";
    echo "Заявката за куриер е направена.<br>";
    echo "<br><br>";
    echo "Резултат:<br>";
    echo "---------<br>";
    var_dump($arrResultOrderPickingInfo);
    echo "<br><br>";

    // Проверка за успешна заявка
    for ($i = 0; $i < count($arrResultOrderPickingInfo); ++$i) {
        $arrErrorDescriptions = $arrResultOrderPickingInfo[$i]->getErrorDescriptions();
        if (count($arrErrorDescriptions) > 0) {
            // Неуспешна заявка. Грешките се съдържат в масива. Обработка на грешките
            echo " Грешки при заявка за куриер за пратка с товарителница ".$arrResultOrderPickingInfo[$i]->getBillOfLading().".<br>";
            for ($j = 0; $j < count($arrErrorDescriptions); ++$j) {
                echo "    Грешкa ".($j + 1).": ".$arrErrorDescriptions[$j]."<br>";
            }
        } else {
            // Успешна заявка за куриер
            echo "<br>";
            echo "Товарителница ".$arrResultOrderPickingInfo[$i]->getBillOfLading()." е успешно заявена.<br>";
        }
    }
*/
    //-------------------------------------------------------------------------------------------------------------------

    echo "<br><br>";
    echo "=========================================================================================================<br>";
    echo "КРАЙ<br>";
    echo "=========================================================================================================<br>";


} catch (Exception $ex) {
    // Обработка на грешка
    echo "<br><br>";
    echo "=========================================================================================================<br>";
    echo "ГРЕШКА: ".$ex->getMessage()."<br>";
    $resultLogin = $eps->getResultLogin(false);
    if (isset($resultLogin) && $resultLogin != null) {
        echo "<br>";
        echo "--------------------------------------------------------------------------------------------------------<br>";
        echo "TIMESTAMP: ".date("Y-m-d H:i:s T", time())."  CLIENTID: ".$resultLogin->getClientId()."  SESSIONID: ".$resultLogin->getSessionId()."<br>";
        echo "--------------------------------------------------------------------------------------------------------<br><br>";
    } else {
        echo "<br>";
        echo "--------------------------------------------------------------------------------------------------------<br>";
        echo "TIMESTAMP: ".date("Y-m-d H:i:s T", time())."  НЕВАЛИДНА СЕСИЯ.<br>";
        echo "--------------------------------------------------------------------------------------------------------<br><br>";
    }
    echo "=========================================================================================================<br>";
}

?>
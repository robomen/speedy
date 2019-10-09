<?php

/*
 * Ако се направи в клас може само после да се интегрира и да подаде новия адрес
 */

class SGeneral {

    private function NimaClient() {
        /*
         * Версия едно е тук да ста статични данни както са тук 
         * Вариант две да се извят като stаtic и да ги има в конфиг файл 
         */
        // Клиентска конфигурация .
        $clientConfiguration = new StdClass();
        $clientConfiguration->userName = 'XXXXXXXXXXXX';                 // Конфигурирайте името на потребителя преодставен за вас от Speedy
        $clientConfiguration->userPassword = 'YYYYYYYYYYYY';             // Конфигурирайте паролата за потребителя преодставен за вас от Speedy
        $clientConfiguration->arrEnabledServices = array(0 => 505);  // Конфигурирайте ограничен списък от услуги на Speedy, с които клиентът ще работи
        $clientConfiguration->contactName = 'ПЕТЪР ПЕТРОВ';                // Конфигурирайте име за контакт на подателя при откриване на товарителници и заявки за куриер
        $clientConfiguration->contactPhone = '0888 888 888';               // Конфигурирайте телефон за контакт на подателя при откриване на товарителници и заявки за куриер
    }

    private function DeliveryClient() {
        /*
         * Тук да са public $varial или да се подават като set 
         * после да може да преминават през валидация с локалната база да реални адреси 
         */
        // Данни за получателя - помощна структура с примерни данни
        $recieverData = new StdClass();
        $recieverData->address = new StdClass();
        $recieverData->address->siteType = 'гр';
        $recieverData->address->siteName = 'БУРГАС';
        $recieverData->address->quarter = 'СЛАВЕЙКОВ';
        $recieverData->address->blockNo = '62';
        $recieverData->address->street = null;
        $recieverData->address->streetNo = null;
        $recieverData->address->entranceNo = '2';
        $recieverData->address->floorNo = '4';
        $recieverData->address->apartmentNo = '12';
        $recieverData->partnerName = 'ИВАНОВИ ООД';
        $recieverData->contactName = 'TEST TEST TEST';
        $recieverData->contactPhone = '7001 7001';
    }

    private function PacketData() {
        /*
         * Тук неща пак може да са почти статични 
         * една две опции да са динамични 
         */
        // Данни за пратката - помощна структура с примерни данни
        $pickingData = new StdClass();
        $pickingData->weightDeclared = 5.25; // Декларирано тегло (например 5.25 кг)
        $pickingData->bringToOfficeId = null; // Офис, в който подателя ще достави пратката. Ако е null, куриер ще я вземе от адреса на подателя
        $pickingData->takeFromOfficeId = 119; // Офис, от който получателя ще вземе пратката. Ако е null, куриер ще я достави до адреса на получателя
        $pickingData->parcelsCount = 1; // Брой пакети
        $pickingData->documents = false; // Флаг дали пратката се състои от документи
        $pickingData->palletized = false; // Флаг дали пратката се състои от палети
        $pickingData->fragile = false; // Флаг дали пратката се състои от палети
        $pickingData->payerType = ParamCalculation::PAYER_TYPE_RECEIVER; // Платецът е получателят
        $pickingData->amountCODBase = 25; // Наложен платеж 25 лв.
        $pickingData->backDocumentReq = true; // Заявка за обратни документи
        $pickingData->backReceiptReq = false; // Заявка за обратна разписка
        $pickingData->contents = 'Дрехи'; // Съдържание на пратката
        $pickingData->packing = 'ПАКЕТ'; // Опаковка на пратката
        $pickingData->autoAdjust = true;    // Auto-adjust флаг при определяне на датата на вземане
    }

    private function CreatePdf() {
        $outputPDFFolder = __DIR__ . '\\'; // Директория, където се записват pdf файловете с товарителници и етикети за печат
    }

}

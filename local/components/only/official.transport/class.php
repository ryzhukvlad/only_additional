<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) {
    die();
}

class OfficialTransport extends CBitrixComponent
{
    public function executeComponent()
    {
        if (!CModule::includeModule("iblock")) {
            ShowError("Не установлен модуль \"Информационные блоки\"");
            return;
        }
        /* Проверка наличия инфоблоков с указанными кодами */
        $rsBlock = CIBlock::GetList([], ["CODE" => $this->arParams["OFFICIAL_AUTO"]]);
        if (!$rsBlock->GetNext()) {
            ShowError('Неверно указан код инфоблока "Служебные авто"');
            return;
        }
        $rsBlock = CIBlock::GetList([], ["CODE" => $this->arParams["OFFICIAL_TRIPS"]]);
        if (!$rsBlock->GetNext()) {
            ShowError('Неверно указан код инфоблока "Служебные поездки"');
            return;
        }
        $rsBlock = CIBlock::GetList([], ["CODE" => $this->arParams["OFFICIAL_COMFORT"]]);
        if (!$rsBlock->GetNext()) {
            ShowError('Неверно указан код инфоблока "Уровни комфорта"');
            return;
        }
        global $USER;
        if(!$USER->IsAuthorized()) {
            ShowError('Вы не авторизованы!');
            return;
        }

        /*Проверка формата данных, полученных из GET параметров*/
        $sTimeStr = $_GET['start_time'];
        $fTimeStr = $_GET['end_time'];
        if (!$sTime = DateTime::createFromFormat('d.m.Y H:i:s', $sTimeStr)) {
            echo "Укажите дату и время начала поездки в формате 01.01.2022 13:00:00";
            return;
        }
        if (!$fTime = DateTime::createFromFormat('d.m.Y H:i:s', $fTimeStr)) {
            echo "Укажите дату и время окончания поездки в формате 01.01.2022 13:00:00";
            return;
        }
        if ($fTime < $sTime) {
            echo "Время окончания поездки не может стоять раньше времени начала";
            return;
        }
        $tInt = $fTime->diff($sTime);
        if ($tInt->h < 1 || $tInt->h > 8) {
            echo "Поездка должна длиться от 1 до 8 часов";
            return;
        }

        if ($this->startResultCache()) {
            /*Получаем информацию о текущем пользователе*/
            $userInfo = CUser::GetByID($USER->GetID())->Fetch();

            /*Запись запланированных поездок в массив*/
            $rsEl = CIBlockElement::GetList([], [
                'IBLOCK_CODE' => $this->arParams['OFFICIAL_TRIPS'],
                ],
                false,
                false,
                [
                    'NAME',
                    'ACTIVE_FROM',
                    'ACTIVE_TO',
                    'PROPERTY_AUTO',
                    'PROPERTY_STAFF'
                ]
            );
            $arTrips = [];
            while ($arTrips[] = $rsEl->GetNext()) {
            }
            array_pop($arTrips);

            /*Получаем автомобили из инфоблока*/
            $rsEl = CIBlockElement::GetList([], [
                'IBLOCK_CODE' => $this->arParams['OFFICIAL_AUTO']
                ],
                false,
                false,
                [
                    'ID',
                    'NAME',
                    'PROPERTY_COMFORT_LEVEL',
                    'PROPERTY_DRIVER_ID'
                ]
            );

            /*Проверяем и выводим доступные автомобили для пользователя*/
            $isFound = false;
            while ($arEl = $rsEl->GetNext()) {
                $rsCom = CIBlockElement::GetList([], [
                    'ID' => $arEl['PROPERTY_COMFORT_LEVEL_VALUE']
                ]);
                $obCom = $rsCom->GetNextElement();
                $arCom = $obCom->GetFields();
                $arCom += $obCom->GetProperties();
                /*Проверка по должности/комфорту*/
                $bFlag = false;
                foreach ($arCom['POSITIONS']['VALUE'] as $value) {
                    if ($value == $userInfo['WORK_POSITION']) {
                        $bFlag = true;
                       break;
                    }
                }
                if (!$bFlag) {
                    continue;
                }
                /*Проверка по времени поездок*/
                foreach ($arTrips as $arTrip) {
                    if ($arTrip['PROPERTY_AUTO_VALUE'] != $arEl['ID']) {
                        continue;
                    }
                    $sTripTime = DateTime::createFromFormat('d.m.Y H:i:s', $arTrip['ACTIVE_FROM']);
                    $fTripTime = DateTime::createFromFormat('d.m.Y H:i:s', $arTrip['ACTIVE_TO']);
                    if ($fTime <= $sTripTime || $sTime >= $fTripTime) {
                        continue;
                    } else {
                        $bFlag = false;
                        break;
                    }
                }
                if (!$bFlag) {
                    continue;
                }
                $drInfo = CUser::GetByID($arEl['PROPERTY_DRIVER_ID_VALUE'])->Fetch();
                /*Для шаблона
                $this->arResult["ITEMS"][] = [
                    'NAME' => $arEl['NAME'],
                    'COMFORT' => $arCom['NAME'],
                    'DRIVER' => $drInfo['NAME'] . ' ' . $drInfo['LAST_NAME']
                ];
                */
                $isFound = true;
                printf('<b>Автомобиль:</b> %s | <b>Категория комфорта:</b> %s | <b>Водитель:</b> %s <br>',
                        $arEl['NAME'], $arCom['NAME'], $drInfo['NAME'] . ' ' . $drInfo['LAST_NAME']);
            }
            if (!$isFound) {
                echo 'На указанное время нет доступных автомобилей';
            }
        }
    }
}
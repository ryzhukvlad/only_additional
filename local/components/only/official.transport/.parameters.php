<?php
/** @var array $arCurrentValues */
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) {
    die();
}

$arComponentParameters = array(
    "PARAMETERS" => array(
        "OFFICIAL_AUTO" => array(
            "PARENT" => "BASE",
            "NAME" => "Код инфоблока \"Служебные авто\"",
            "TYPE" => "STRING",
            "DEFAULT" => "OFFICIAL_AUTO",
            "REFRESH" => "Y",
        ),
        "OFFICIAL_TRIPS" => array(
            "PARENT" => "BASE",
            "NAME" => "Код инфоблока \"Служебные поездки\"",
            "TYPE" => "STRING",
            "DEFAULT" => "OFFICIAL_TRIPS",
            "REFRESH" => "Y",
        ),
        "OFFICIAL_COMFORT" => array(
            "PARENT" => "BASE",
            "NAME" => "Код инфоблока \"Уровень комфорта\"",
            "TYPE" => "STRING",
            "DEFAULT" => "OFFICIAL_COMFORT",
            "REFRESH" => "Y",
        ),
    )
);
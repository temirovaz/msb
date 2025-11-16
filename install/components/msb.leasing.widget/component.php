<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

// Инициализируем параметры
if (!isset($arParams) || !is_array($arParams)) {
    $arParams = array();
}

// Подключаем класс компонента
if (file_exists(__DIR__."/class.php")) {
    require_once(__DIR__."/class.php");
    
    // Создаём экземпляр компонента
    $component = new MsbLeasingWidgetComponent();
    
    // Инициализируем компонент (стандартный способ Bitrix)
    $component->InitComponentVars();
    $component->arParams = $arParams;
    
    // Выполняем компонент
    $component->executeComponent();
} else {
    // Fallback: если класс не найден, используем простой вариант
    echo "<!-- Компонент МСБ Лизинг: класс не найден, используется упрощённый режим -->";
    
    // Минимальная функциональность без класса
    $componentPath = "/bitrix/components/msb.leasing.widget";
    \Bitrix\Main\Page\Asset::getInstance()->addCss($componentPath."/templates/.default/style.css");
    \Bitrix\Main\Page\Asset::getInstance()->addJs($componentPath."/templates/.default/widget.js");
    
    $module_id = 'msb_leasing';
    $arResult = array();
    $arResult['DADATA_API_KEY'] = COption::GetOptionString($module_id, 'DADATA_API_KEY', '');
    $arResult['N8N_URL'] = COption::GetOptionString($module_id, 'N8N_URL', '');
    $arResult['N8N_LOGIN'] = COption::GetOptionString($module_id, 'N8N_LOGIN', '');
    $arResult['N8N_PASSWORD'] = COption::GetOptionString($module_id, 'N8N_PASSWORD', '');
    $arResult['PARTNER_ID'] = COption::GetOptionString($module_id, 'PARTNER_ID', 'partner_001');
    $arResult['PRODUCT_ID'] = 0;
    $arResult['PRODUCT_NAME'] = '';
    $arResult['PRODUCT_PRICE'] = 0;
    $arResult['PRODUCT_ARTICLE'] = '';
    
    $templatePath = $_SERVER["DOCUMENT_ROOT"]."/bitrix/components/msb.leasing.widget/templates/.default";
    if (file_exists($templatePath."/template.php")) {
        include($templatePath."/template.php");
    }
}

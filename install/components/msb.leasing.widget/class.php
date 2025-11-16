<?php
if(!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true)die();

use Bitrix\Main\Page\Asset;
use Bitrix\Main\Loader;

class MsbLeasingWidgetComponent extends CBitrixComponent
{
    public function executeComponent()
    {
        // Проверяем настройки (на каких товарах показывать)
        $arOption = COption::GetOptionString('msb_leasing','PRODUCTS_SHOW',serialize([]));
        $ids = @unserialize($arOption);
        if (!is_array($ids)) $ids=[];
        $showAll = in_array('ALL', $ids);
        
        global $APPLICATION;
        $isProduct = false;
        $productId = 0;
        
        // Пробуем получить ID текущего товара разными способами (стандартные для Bitrix)
        if ($this->arParams['PRODUCT_ID']) {
            $productId = (int)$this->arParams['PRODUCT_ID'];
        } elseif (isset($GLOBALS['arResult']['ID'])) {
            // Стандартная переменная компонента детального просмотра (приоритет)
            $productId = (int)$GLOBALS['arResult']['ID'];
        } elseif (isset($_REQUEST['ELEMENT_ID'])) {
            $productId = (int)$_REQUEST['ELEMENT_ID'];
        } elseif (isset($_REQUEST['ID'])) {
            $productId = (int)$_REQUEST['ID'];
        } elseif (isset($GLOBALS['ELEMENT_ID'])) {
            $productId = (int)$GLOBALS['ELEMENT_ID'];
        } elseif (defined('ELEMENT_ID')) {
            $productId = (int)ELEMENT_ID;
        } elseif (isset($GLOBALS['APPLICATION']) && method_exists($GLOBALS['APPLICATION'], 'GetProperty')) {
            // Через свойство страницы
            $productId = (int)$GLOBALS['APPLICATION']->GetProperty('ELEMENT_ID');
        } else {
            // Пытаемся извлечь ID из URL (например, /products/4/26/)
            $requestUri = $_SERVER['REQUEST_URI'] ?? '';
            if (preg_match('#/(?:products|catalog|element)/(?:[^/]+/)*(\d+)/?$#', $requestUri, $matches)) {
                $productId = (int)$matches[1];
            }
        }
        
        // Если выбрано "ALL" - показываем на всех страницах (даже если ID не определён)
        // Иначе показываем только если товар в списке
        if ($showAll) {
            $isProduct = true;
        } elseif ($productId && in_array($productId, $ids)) {
            $isProduct = true;
        }
        
        if (!$isProduct) return; // не показываем компонент
        Asset::getInstance()->addCss($this->getPath()."/templates/.default/style.css");
        Asset::getInstance()->addJs($this->getPath()."/templates/.default/widget.js");
        
        // Берём настройки из опций модуля (приоритет над параметрами компонента)
        $module_id = 'msb_leasing';
        $this->arResult['DADATA_API_KEY'] = COption::GetOptionString($module_id, 'DADATA_API_KEY', $this->arParams['DADATA_API_KEY'] ?? '');
        $this->arResult['N8N_URL'] = COption::GetOptionString($module_id, 'N8N_URL', $this->arParams['N8N_URL'] ?? '');
        $this->arResult['N8N_LOGIN'] = COption::GetOptionString($module_id, 'N8N_LOGIN', $this->arParams['N8N_LOGIN'] ?? '');
        $this->arResult['N8N_PASSWORD'] = COption::GetOptionString($module_id, 'N8N_PASSWORD', $this->arParams['N8N_PASSWORD'] ?? '');
        $this->arResult['PARTNER_ID'] = COption::GetOptionString($module_id, 'PARTNER_ID', $this->arParams['PARTNER_ID'] ?? 'partner_001');
        
        // Передаём данные о товаре (если доступны)
        $this->arResult['PRODUCT_ID'] = $productId;
        $this->arResult['PRODUCT_NAME'] = $this->arParams['PRODUCT_NAME'] ?? '';
        $this->arResult['PRODUCT_PRICE'] = $this->arParams['PRODUCT_PRICE'] ?? 0;
        $this->arResult['PRODUCT_ARTICLE'] = $this->arParams['PRODUCT_ARTICLE'] ?? '';
        
        // Если данные не переданы, пытаемся получить из глобальных переменных
        if (empty($this->arResult['PRODUCT_NAME']) && isset($GLOBALS['arResult']['NAME'])) {
            $this->arResult['PRODUCT_NAME'] = $GLOBALS['arResult']['NAME'];
        }
        if (empty($this->arResult['PRODUCT_PRICE']) && isset($GLOBALS['arResult']['PRICE'])) {
            $this->arResult['PRODUCT_PRICE'] = $GLOBALS['arResult']['PRICE'];
        }
        
        $this->includeComponentTemplate();
    }
}

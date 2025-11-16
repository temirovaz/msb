<?php
require($_SERVER["DOCUMENT_ROOT"]."/bitrix/header.php");
$APPLICATION->SetTitle('Демо: msb.leasing.widget');

// Настройки теперь берутся автоматически из опций модуля (admin/settings.php)
// Параметры можно передать для переопределения, но по умолчанию используются настройки модуля
$APPLICATION->IncludeComponent('msb.leasing.widget','',[
    // Опционально: можно переопределить настройки модуля через параметры
    // 'DADATA_API_KEY' => '...',
    // 'N8N_URL' => '...',
    // 'PARTNER_ID' => '...',
]);

require($_SERVER["DOCUMENT_ROOT"]."/bitrix/footer.php");

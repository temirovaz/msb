<?php if(!defined('B_PROLOG_INCLUDED') || B_PROLOG_INCLUDED!==true) die(); ?>
<div id="msb-leasing-widget-root"></div>
<button id="msb-leasing-btn" style="padding: 12px 28px; font-size: 18px; border: none; border-radius: 6px; background: #368ee0; color: #fff; cursor: pointer; margin: 20px 0;">
    Купить в лизинг
</button>
<script>
window.DADATA_API_KEY           = <?=json_encode($arResult['DADATA_API_KEY'])?>;
window.N8N_URL                  = <?=json_encode($arResult['N8N_URL'])?>;
window.N8N_LOGIN                = <?=json_encode($arResult['N8N_LOGIN'])?>;
window.N8N_PASSWORD             = <?=json_encode($arResult['N8N_PASSWORD'])?>;
window.MSB_LEASING_PARTNER_ID   = <?=json_encode($arResult['PARTNER_ID'])?>;

// Получаем данные о товаре из страницы
var productData = {
    article: '<?=htmlspecialchars($arResult['PRODUCT_ARTICLE'] ?? '')?>',
    name: '<?=htmlspecialchars($arResult['PRODUCT_NAME'] ?? 'Товар')?>',
    price: <?=(int)($arResult['PRODUCT_PRICE'] ?? 0)?>
};

// Если данные не переданы, пытаемся получить из DOM
if (!productData.article && !productData.price) {
    // Пытаемся найти цену на странице
    var priceEl = document.querySelector('.price, [data-price], .product-price');
    if (priceEl) {
        var priceText = priceEl.textContent || priceEl.innerText;
        var priceMatch = priceText.match(/[\d\s]+/);
        if (priceMatch) {
            productData.price = parseInt(priceMatch[0].replace(/\s/g, '')) || 0;
        }
    }
    // Пытаемся найти название товара
    var nameEl = document.querySelector('h1, .product-name, .product-title');
    if (nameEl) {
        productData.name = nameEl.textContent || nameEl.innerText || 'Товар';
    }
}

// Обработчик кнопки
document.getElementById('msb-leasing-btn').onclick = function() {
    if (window.LeasingWidget) {
        window.LeasingWidget.showWidget(productData);
    } else {
        alert('Виджет лизинга не загружен. Проверьте подключение скриптов.');
    }
};
</script>

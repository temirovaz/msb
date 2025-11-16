<?php
require_once($_SERVER['DOCUMENT_ROOT'].'/bitrix/modules/main/include/prolog_admin_before.php');
require_once($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/prolog_admin_after.php");

use Bitrix\Main\Loader;

$module_id = 'msb_leasing';
CModule::IncludeModule('iblock');

// Получить текущие настройки
$currentIds = (array)unserialize(COption::GetOptionString($module_id, 'PRODUCTS_SHOW', serialize([])));
if (!is_array($currentIds)) $currentIds = [];
$allSelected = in_array('ALL', $currentIds);
if ($allSelected) $currentIds = array_diff($currentIds, ['ALL']);

$dadataKey = COption::GetOptionString($module_id, 'DADATA_API_KEY', '');
$n8nUrl = COption::GetOptionString($module_id, 'N8N_URL', '');
$n8nLogin = COption::GetOptionString($module_id, 'N8N_LOGIN', '');
$n8nPassword = COption::GetOptionString($module_id, 'N8N_PASSWORD', '');
$partnerId = COption::GetOptionString($module_id, 'PARTNER_ID', 'partner_001');

// Обработка сохранения настроек API
if ($_SERVER['REQUEST_METHOD']=='POST' && isset($_REQUEST['SAVE_API'])) {
    COption::SetOptionString($module_id, 'DADATA_API_KEY', $_REQUEST['DADATA_API_KEY'] ?? '');
    COption::SetOptionString($module_id, 'N8N_URL', $_REQUEST['N8N_URL'] ?? '');
    COption::SetOptionString($module_id, 'N8N_LOGIN', $_REQUEST['N8N_LOGIN'] ?? '');
    COption::SetOptionString($module_id, 'N8N_PASSWORD', $_REQUEST['N8N_PASSWORD'] ?? '');
    COption::SetOptionString($module_id, 'PARTNER_ID', $_REQUEST['PARTNER_ID'] ?? 'partner_001');
    LocalRedirect("?lang=ru&success=api");
}

// Обработка добавления товара
if ($_SERVER['REQUEST_METHOD']=='POST' && $_REQUEST['ADD_PRODUCT']) {
    $id = (int)$_REQUEST['NEW_PRODUCT_ID'];
    if ($id>0 && !in_array($id,$currentIds)) $currentIds[] = $id;
    COption::SetOptionString($module_id, 'PRODUCTS_SHOW', serialize($currentIds));
    LocalRedirect("?lang=ru&success=add");
}

// Обработка удаления товара
if ($_SERVER['REQUEST_METHOD']=='POST' && $_REQUEST['REMOVE_ID']) {
    $id = (int)$_REQUEST['REMOVE_ID'];
    $currentIds = array_diff($currentIds, [$id]);
    COption::SetOptionString($module_id, 'PRODUCTS_SHOW', serialize($currentIds));
    LocalRedirect("?lang=ru&success=del");
}

// Выбрать все товары
if ($_SERVER['REQUEST_METHOD']=='POST' && isset($_REQUEST['ALL_PRODUCTS'])) {
    COption::SetOptionString($module_id, 'PRODUCTS_SHOW', serialize(['ALL']));
    LocalRedirect("?lang=ru&success=all");
}

// Очистить все товары
if ($_SERVER['REQUEST_METHOD']=='POST' && isset($_REQUEST['CLEAR_ALL'])) {
    COption::SetOptionString($module_id, 'PRODUCTS_SHOW', serialize([]));
    LocalRedirect("?lang=ru&success=clear");
}

$APPLICATION->SetTitle('Настройки модуля МСБ Лизинг');
?>
<style>
.settings-section { margin: 20px 0; padding: 15px; border: 1px solid #ddd; border-radius: 5px; background: #f9f9f9; }
.settings-section h3 { margin-top: 0; }
.form-group { margin: 10px 0; }
.form-group label { display: inline-block; width: 200px; font-weight: bold; }
.form-group input[type="text"], .form-group input[type="password"] { width: 400px; padding: 5px; }
.product-list { list-style: none; padding: 0; }
.product-list li { padding: 8px; margin: 5px 0; background: white; border: 1px solid #ccc; border-radius: 3px; }
.product-list li form { display: inline; margin-left: 10px; }
</style>

<h2>Настройки модуля МСБ Лизинг</h2>

<?php if ($_REQUEST['success']): ?>
<div style="padding: 10px; background: #d4edda; border: 1px solid #c3e6cb; border-radius: 5px; margin: 10px 0;">
    Настройки сохранены!
</div>
<?php endif; ?>

<!-- Секция: Настройки API -->
<div class="settings-section">
    <h3>Настройки подключения</h3>
    <form method="post" action="">
        <div class="form-group">
            <label>DaData API ключ:</label>
            <input type="text" name="DADATA_API_KEY" value="<?=htmlspecialchars($dadataKey)?>" />
        </div>
        <div class="form-group">
            <label>n8n Webhook URL:</label>
            <input type="text" name="N8N_URL" value="<?=htmlspecialchars($n8nUrl)?>" />
        </div>
        <div class="form-group">
            <label>n8n Логин:</label>
            <input type="text" name="N8N_LOGIN" value="<?=htmlspecialchars($n8nLogin)?>" />
        </div>
        <div class="form-group">
            <label>n8n Пароль:</label>
            <input type="password" name="N8N_PASSWORD" value="<?=htmlspecialchars($n8nPassword)?>" />
        </div>
        <div class="form-group">
            <label>Partner ID:</label>
            <input type="text" name="PARTNER_ID" value="<?=htmlspecialchars($partnerId)?>" />
        </div>
        <button name="SAVE_API" value="1" type="submit" style="padding: 8px 15px; background: #0066cc; color: white; border: none; border-radius: 3px; cursor: pointer;">Сохранить настройки API</button>
    </form>
</div>

<!-- Секция: Управление товарами -->
<div class="settings-section">
    <h3>Управление товарами для отображения виджета</h3>
    <form method="post" action="" style="margin-bottom: 15px;">
        <div class="form-group">
            <label>Добавить товар (ID):</label>
            <input type="text" name="NEW_PRODUCT_ID" placeholder="Введите ID товара" />
            <button name="ADD_PRODUCT" value="1" type="submit" style="margin-left: 10px; padding: 5px 15px;">Добавить</button>
        </div>
    </form>
    <div style="margin: 10px 0;">
        <form method="post" style="display: inline;">
            <button name="ALL_PRODUCTS" value="1" type="submit" style="padding: 8px 15px; background: #28a745; color: white; border: none; border-radius: 3px; cursor: pointer;">Выбрать все товары</button>
        </form>
        <form method="post" style="display: inline; margin-left: 10px;">
            <button name="CLEAR_ALL" value="1" type="submit" style="padding: 8px 15px; background: #dc3545; color: white; border: none; border-radius: 3px; cursor: pointer;">Очистить всё</button>
        </form>
    </div>
    
    <h4>Товары, для которых будет отображён модуль:</h4>
    <ul class="product-list">
    <?php
    if ($allSelected):
        echo "<li><b>✓ На всех товарах каталога</b></li>";
    else:
        if (empty($currentIds)):
            echo "<li><em>Нет выбранных товаров</em></li>";
        else:
            foreach($currentIds as $pid) {
                $el = CIBlockElement::GetByID($pid)->GetNext();
                $name = $el ? $el['NAME'] : "ID: $pid";
                echo "<li><strong>$name</strong> (ID: $pid) ";
                echo "<form style='display:inline;' method='post'><input type='hidden' name='REMOVE_ID' value='$pid'/><button type='submit' style='padding: 3px 10px; background: #dc3545; color: white; border: none; border-radius: 3px; cursor: pointer;'>Убрать</button></form></li>";
            }
        endif;
    endif;
    ?>
    </ul>
</div>

<?php require($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/main/include/epilog_admin.php"); ?>

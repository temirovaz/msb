<?php
if (!check_bitrix_sessid()) return;

use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

echo CAdminMessage::ShowNote(Loc::getMessage("MSB_LEASING_UNINSTALL_SUCCESS"));
?>

<form action="<?=$APPLICATION->GetCurPage()?>">
    <?=bitrix_sessid_post()?>
    <input type="hidden" name="lang" value="<?=LANGUAGE_ID?>">
    <input type="submit" name="" value="Вернуться к списку модулей">
</form>


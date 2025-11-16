<?php
if (!check_bitrix_sessid()) return;

use Bitrix\Main\Localization\Loc;
Loc::loadMessages(__FILE__);

echo CAdminMessage::ShowNote(Loc::getMessage("MSB_LEASING_INSTALL_SUCCESS"));
?>

<form action="<?=$APPLICATION->GetCurPage()?>">
    <?=bitrix_sessid_post()?>
    <input type="hidden" name="lang" value="<?=LANGUAGE_ID?>">
    <input type="hidden" name="id" value="msb_leasing">
    <input type="hidden" name="install" value="Y">
    <input type="hidden" name="step" value="2">
    <input type="submit" name="" value="Вернуться к списку модулей">
</form>


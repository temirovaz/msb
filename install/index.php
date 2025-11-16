<?php
use Bitrix\Main\Localization\Loc;

Loc::loadMessages(__FILE__);

class msb_leasing extends CModule
{
    public function __construct()
    {
        $arModuleVersion = array();
        include(__DIR__."/version.php");

        $this->MODULE_ID = "msb_leasing";
        $this->MODULE_VERSION = $arModuleVersion["VERSION"];
        $this->MODULE_VERSION_DATE = $arModuleVersion["VERSION_DATE"];
        $this->MODULE_NAME = Loc::getMessage("MSB_LEASING_MODULE_NAME");
        $this->MODULE_DESCRIPTION = Loc::getMessage("MSB_LEASING_MODULE_DESCRIPTION");
        $this->PARTNER_NAME = Loc::getMessage("MSB_LEASING_PARTNER_NAME");
        $this->PARTNER_URI = Loc::getMessage("MSB_LEASING_PARTNER_URI");
    }

    public function DoInstall()
    {
        global $APPLICATION;
        $this->InstallFiles();
        $this->InstallDB();
        RegisterModule("msb_leasing");
        $APPLICATION->IncludeAdminFile(
            Loc::getMessage("MSB_LEASING_INSTALL_TITLE"),
            $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/msb_leasing/install/step.php"
        );
    }

    public function DoUninstall()
    {
        global $APPLICATION;
        $this->UnInstallDB();
        $this->UnInstallFiles();
        UnRegisterModule("msb_leasing");
        $APPLICATION->IncludeAdminFile(
            Loc::getMessage("MSB_LEASING_UNINSTALL_TITLE"),
            $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/msb_leasing/install/unstep.php"
        );
    }

    public function InstallDB()
    {
        // Здесь можно создать таблицы БД, если нужно
        // Например, для хранения заявок
        return true;
    }

    public function UnInstallDB()
    {
        // Удаление таблиц БД, если были созданы
        return true;
    }

    public function InstallFiles()
    {
        // Копирование компонентов
        CopyDirFiles(
            $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/msb_leasing/install/components",
            $_SERVER["DOCUMENT_ROOT"]."/bitrix/components",
            true,
            true
        );
        
        // Копирование админ-страниц
        CopyDirFiles(
            $_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/msb_leasing/admin",
            $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin",
            true,
            true
        );
        
        return true;
    }

    public function UnInstallFiles()
    {
        // Удаление компонентов
        DeleteDirFilesEx("/bitrix/components/msb.leasing.widget");
        
        // Удаление админ-страниц
        DeleteDirFilesEx("/bitrix/admin/msb_leasing_settings.php");
        DeleteDirFiles($_SERVER["DOCUMENT_ROOT"]."/bitrix/modules/msb_leasing/admin/.menu.php", $_SERVER["DOCUMENT_ROOT"]."/bitrix/admin/.menu.php");
        
        return true;
    }
}

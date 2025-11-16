<?php
if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED!==true) die();

$arComponentParameters = array(
    "GROUPS" => array(),
    "PARAMETERS" => array(
        "PRODUCT_ID" => array(
            "PARENT" => "BASE",
            "NAME" => "ID товара",
            "TYPE" => "STRING",
            "DEFAULT" => "",
        ),
        "PRODUCT_NAME" => array(
            "PARENT" => "BASE",
            "NAME" => "Название товара",
            "TYPE" => "STRING",
            "DEFAULT" => "",
        ),
        "PRODUCT_PRICE" => array(
            "PARENT" => "BASE",
            "NAME" => "Цена товара",
            "TYPE" => "STRING",
            "DEFAULT" => "0",
        ),
        "PRODUCT_ARTICLE" => array(
            "PARENT" => "BASE",
            "NAME" => "Артикул товара",
            "TYPE" => "STRING",
            "DEFAULT" => "",
        ),
    ),
);


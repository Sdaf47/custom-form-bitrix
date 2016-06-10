<?php

use Bitrix\Highloadblock\HighloadBlockTable;
use CUserTypeEntity;


CModule::IncludeModule("highloadblock");

/**
 * Class FormAjax
 *
 * PREFIX_OF_EVENT - prefix for name mail event before ID. (example: EVENT_FORM_23)
 *
 */
class FormFaqAjax extends CBitrixComponent {

	const TYPE_ERROR_NO_VALID = 1;
	const TYPE_ERROR_EMPTY = 2;
	const PREFIX_OF_EVENT = "EVENT_FORM_IBLOCK_";
	const PREFIX_OF_DIRECTORY_FILES = "/form_files/";

	public $arResult = array();
	public $arParams = array();
	public $arResponse = array();
	public $arErrors = array();
	public $arMessage = array();

	/**
	 * @throws \Bitrix\Main\DB\Exception
     */
	public function executeComponent() {

		$this->createFieldsFromHeightLoad();
		if ($_REQUEST["submit"]) {
			$this->checkFieldsIblock();
			if (!empty($this->arErrors)) {
				$this->arResult["ERRORS"] = $this->arErrors;
			} else {
				$this->saveFieldsToIblock();
			}
		}

		$this->arParams;
		$this->IncludeComponentTemplate();
	}

	/**
	 * Create fields by settings fields
	 */
	private function createFieldsFromHeightLoad() {
		$arFields = array(
			"PROPERTY_THEME" => array(
				"TYPE" => "string",
				"MANDATORY" => true,
				"NAME" => "PROPERTY_THEME",
				"PLACEHOLDER" => "Betreff",
				"VALUE" => ""
			),
			"NAME" => array(
				"TYPE" => "text",
				"MANDATORY" => true,
				"NAME" => "NAME",
				"PLACEHOLDER" => "Nachricht",
				"VALUE" => ""
			),
			"PROPERTY_NAME" => array(
				"TYPE" => "string",
				"MANDATORY" => true,
				"NAME" => "PROPERTY_NAME",
				"PLACEHOLDER" => "Name",
				"VALUE" => ""
			),
			"PROPERTY_EMAIL" => array(
				"TYPE" => "string",
				"MANDATORY" => true,
				"NAME" => "PROPERTY_EMAIL",
				"PLACEHOLDER" => "E-mail",
				"VALUE" => "",
				"REGEXP" => "/^([a-z0-9_.-]{1,20})@([a-z0-9.-]{1,20})\.([a-z]{2,6})$/is"
			)
		);
		$this->arResult["FIELDS"] = $arFields;
	}

	private function saveFieldsToIblock() {
		$cIblockElement = new CIBlockElement();
		$arProps = array(
			"EMAIL" => $this->arResponse["PROPERTY_EMAIL"],
			"NAME" => $this->arResponse["PROPERTY_NAME"],
			"THEME" => $this->arResponse["PROPERTY_THEME"]
		);
		$arFields = array(
			"IBLOCK_SECTION_ID" => WS_PSettings::getFieldValue("IBLOCK_SECTION_ID_NEW"),
			"IBLOCK_ID" => $this->arParams["IBLOCK_ID"],
			"PROPERTY_VALUES" => $arProps,
			"NAME" => $this->arResponse["NAME"],
			"ACTIVE" => "N",
			"CODE" => CUtil::translit($this->arResponse["NAME"], 'en')
		);
		$result = $cIblockElement->Add($arFields);
		if(!$result) {
			echo $cIblockElement->LAST_ERROR;
		} else {
			$this->sendMessage();
			$this->arResult["SUCCESS"] = true;
			$this->arResult["ID_RESPONSE"] = $result;
		}
	}

	/**
	 * Filling array of errors
	 *
	 * @param $type
	 * @param $field
     */
	private function setResponseErrors ($type, $field) {
		if ($type == self::TYPE_ERROR_NO_VALID) {
			$message = "Falsch eingegebene Wert: ";
		} elseif ($type == self::TYPE_ERROR_EMPTY) {
			$message = "Es ist nicht Feld ausgefüllt: ";
		}
		$this->arErrors[$field] = [
			"MESSAGE" => $message,
			"NAME" => $field
		];
	}

	/**
	 * Send mail to admin
     */
	private function sendMessage() {
		$cEvent = new CEvent();
		$arFields = array(
			"FORM_TITLE" => $this->arParams["FORM_TITLE"]
		);
		$arFields = array_merge($arFields, $this->arMessage);
		$cEvent->Send(self::PREFIX_OF_EVENT.$this->arParams["IBLOCK_ID"], $this->arParams["SITE_ID"], $arFields);
	}

	private function putToMessage($field, $value) {
		$this->arMessage[$field] = $value;
	}

	private function checkFieldsIblock() {
		foreach ($this->arResult["FIELDS"] as $arField) {
			$response = $_REQUEST[$arField["NAME"]];
			if ($arField["REGEXP"] && $arField["MANDATORY"] && !preg_match($arField["REGEXP"], $response)) {
				$this->setResponseErrors(self::TYPE_ERROR_NO_VALID, $arField["NAME"]);
			} else {
				$this->putToMessage($arField["NAME"], $response);
			}
			$this->arResponse[$arField["NAME"]] = $response;
		}
		$this->arResult["RESPONSE"] = $this->arResponse;
	}
}

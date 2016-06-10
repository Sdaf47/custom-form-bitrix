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
class FormAjax extends CBitrixComponent {

	const TYPE_ERROR_NO_VALID = 1;
	const TYPE_ERROR_EMPTY = 2;
	const PREFIX_OF_EVENT = "EVENT_FORM_";
	const PREFIX_OF_DIRECTORY_FILES = "/form_files/";

	public $arResult = array();
	public $arParams = array();
	public $arResponse = array();
	public $arErrors = array();
	public $arMessage = array();
	public $allowFiles = array(
		"pdf",
		"doc",
		"docx",
		"txt",
		"rtf"
	);

	/**
	 * @throws \Bitrix\Main\DB\Exception
     */
	public function executeComponent() {
		if (!$this->arParams["LANG_ID"]) {
			$this->arParams["LANG_ID"] = LANGUAGE_ID?LANGUAGE_ID:'en';
		}

		if ($_REQUEST["submit"] == $this->arParams["SUBMIT_NAME"]) {
			if ($this->checkFieldsFromHeightLoad()) {
				if (empty($this->arErrors)) {
					$this->saveFields();
				} else {
					$this->arResult["ERRORS"] = $this->arErrors;
				}
			}
		}
		$this->createFieldsFromHeightLoad();

		$this->IncludeComponentTemplate();
	}

	/**
	 * Create fields by settings fields
	 */
	private function createFieldsFromHeightLoad() {
		if ($arUserFields = $this->getFieldsFromHeightLoad()) {
			foreach($arUserFields as $userField) {
				$this->arResult["FIELDS"][] = array(
					"ID" => $userField["ID"],
					"AUTO_TEXT" => $userField["SETTINGS"]["DEFAULT_VALUE"],
					"CODE" => $userField["FIELD_NAME"],
					"NAME" => $userField["EDIT_FORM_LABEL"][$this->arParams["LANG_ID"]],
					"TYPE" => $userField["USER_TYPE_ID"],
					"SORT" => $userField["SORT"],
					"MULTIPLE" => $userField["MULTIPLE"],
					"MANDATORY" => ($userField["MANDATORY"]=="Y")?true:false,
					"SHOW_FILTER" => $userField["SHOW_FILTER"],
					"EDIT_IN_LIST" => $userField["EDIT_IN_LIST"],
					"SHOW_IN_LIST" => array(
						"SIZE" => $userField["SHOW_IN_LIST"],
						"ROWS" => $userField["ROWS"],
						"REGEXP" => $userField["REGEXP"],
						"MIN_LENGTH" => $userField["MIN_LENGTH"],
						"MAX_LENGTH" => $userField["MAX_LENGTH"],
						"DEFAULT_VALUE" => $userField["DEFAULT_VALUE"],
					)

				);
			}
		}
	}

	/**
	 * Save response to HLTable
	 *
	 * @throws Exception
	 * @throws \Bitrix\Main\DB\Exception
	 * @throws \Bitrix\Main\SystemException
     */
	private function saveFields() {

		$block = HighloadBlockTable::getById($this->arParams["TABLE_ID"])->fetch();
		$entity = HighloadBlockTable::compileEntity($block);
		$entity_data_class = $entity->getDataClass();

		$result = $entity_data_class::add($this->arResponse);
		if(!$result->isSuccess()) {
			throw new \Bitrix\Main\DB\Exception("Unknown error. Please try again.");
		} else {
			$this->sendMessage();
			$this->arResult["SUCCESS"] = true;
			$this->arResult["ID_RESPONSE"] = $result->getId();
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
	 * Checking response with UFFields type
	 *
	 * @return bool
     */
	private function checkFieldsFromHeightLoad() {
		$arUserFields = $this->getFieldsFromHeightLoad();
		foreach ($arUserFields as $userField) {
			$key = $userField["FIELD_NAME"];
			if ($userField["USER_TYPE_ID"] == "string") {
				$response = htmlspecialcharsbx($_REQUEST[$key]);
				if ($userField["SETTINGS"]["REGEXP"] && !preg_match($userField["SETTINGS"]["REGEXP"], $response)) {
					$this->setResponseErrors(self::TYPE_ERROR_NO_VALID, $key);
				}
				$this->putToMessage($key, $response);
			} elseif ($userField["USER_TYPE_ID"] == "file") {
				$cFile = new CFile();
				if (!$_REQUEST[$key]) {
					$arFile = CFile::MakeFileArray($_FILES[$key]["tmp_name"]);
					$arFile["name"] = $_FILES[$key]["name"];
					$extension = preg_replace("/.*?\./", "", $_FILES["UF_RESUME"]["name"]);
					if (in_array($extension, $this->allowFiles)) {
						$response = $cFile->SaveFile($arFile, self::PREFIX_OF_DIRECTORY_FILES);
					} else {
						$this->setResponseErrors(TYPE_ERROR_NO_VALID, $key);
					}
				} else {
					$response = $_REQUEST[$key];
				}
				$this->arResult["FILES"][$key] = $cFile->GetFileArray($response);
				$this->arResult["REPEAT_FILES"][$key] = "<input type='hidden' value='" . $response ."' name='" . $key . "'>";
				$this->putToMessage($key, $cFile->GetPath($response));
			} else {
				$response = htmlspecialcharsbx($_REQUEST[$key]);
				$this->putToMessage($key, $response);
			}
			if ($userField["MANDATORY"] == "Y" && $response == '') {
				$this->setResponseErrors(self::TYPE_ERROR_EMPTY, $key);
			}
			$this->arResponse[$key] = $response;
		}
		$this->arResult["RESPONSE"] = $this->arResponse;
		return true;
	}

	/**
	 * Getter fields from HLTable
	 *
	 * @return array|bool
	 * @throws \Bitrix\Main\SystemException
     */
	private function getFieldsFromHeightLoad() {
		$codeTable = "HLBLOCK_".$this->arParams["TABLE_ID"];
		$this->arResult["SUBMIT_NAME"] = $this->arParams["SUBMIT_NAME"];
		if (!$block = HighloadBlockTable::getById($this->arParams["TABLE_ID"])->fetch()) {
			return false;
		}
		$this->arResult["FORM_TITLE"] = $this->arParams["FORM_TITLE"];
		$this->arResult["METHOD"] = $this->arParams["METHOD"];
		$this->arResult["SUCCESS_MESSAGE"] = $this->arParams["SUCCESS_MESSAGE"];
		$entity = HighloadBlockTable::compileEntity($block);
		$fields = $entity->getFields();

		foreach ($fields as $field) {
			$arFields[] = $field->getName();
		}

		$cUserField = new CUserTypeEntity();

		if (isset($arFields)) {
			$rsUserField = $cUserField->GetList([], ["ENTITY_ID" => $codeTable, "FIELD_NAME" => $arFields]);
			while ($arUserFieldStaging = $rsUserField->GetNext()) {
				$arUserFields[] = $cUserField->GetByID($arUserFieldStaging["ID"]);
			}
		}
		if (!empty($arUserFields)) {
			return $arUserFields;
		}
		return false;
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
		$cEvent->Send(self::PREFIX_OF_EVENT.$this->arParams["TABLE_ID"], $this->arParams["SITE_ID"], $arFields);
	}

	private function putToMessage($field, $value) {
		$this->arMessage[$field] = $value;
	}
}

<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>

<?php if ($arResult["SUCCESS"]): ?>
    <div class="patGreyBg realtySearch">
        <div class="title"><?= $arResult["SUCCESS_MESSAGE"]; ?></div>
    </div>
    <?php return; ?>
<?php endif; ?>
<div class="patGreyBg realtySearch">
    <div><?= GetMessage("SECOND_TITLE"); ?></div>
    <div class="title"><?= $arResult["FORM_TITLE"]; ?></div>
    <form action="<?=$APPLICATION->GetCurPage();?>" method="<?= $arResult["METHOD"] ?>">
        <?= bitrix_sessid_post(); ?>
        <?php foreach ($arResult["FIELDS"] as $arField) : ?>
            <?php if ($arField["EDIT_IN_LIST"] == "N") : ?>
                <input name="<?= $arField["CODE"] ?>"
                       type="hidden"
                       value="<?= $arField["TYPE"]=="string"?$arParams["ELEMENT_NAME"]:$arParams["ELEMENT_ID"]; ?>">
            <?php elseif ($arField["TYPE"] == "string") : ?>
                <div class="whiteInput <?= (!empty($arResult["ERRORS"][$arField["CODE"]])) ? "error" : ""?>">
                    <input name="<?= $arField["CODE"] ?>"
                            <?= ($arField["MANDATORY"])?"required":""?>
                           type="<?= ($arField["NAME"] == "Email")?"email":"text"; ?>"
                           placeholder="<?= $arField["AUTO_TEXT"]?$arField["AUTO_TEXT"]:$arField["NAME"] ?><?= ($arField["MANDATORY"])?" *":""?>"
                           value="<?= !empty($arResult["RESPONSE"])?$arResult["RESPONSE"][$arField["CODE"]]:""?>">
                </div>
            <?php endif; ?>
        <?php endforeach; ?>
        <div class="Button blackTrntButton">
            <input type="submit" name="submit" value="<?= $arResult["SUBMIT_NAME"] ?>">
        </div>
    </form>
</div>
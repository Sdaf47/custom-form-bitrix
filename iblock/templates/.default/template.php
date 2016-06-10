<?php if (!defined("B_PROLOG_INCLUDED") || B_PROLOG_INCLUDED !== true) die(); ?>
<?php if ($arResult["SUCCESS"]) : ?>
    <div class="patThemeBg formBlock" id="form_faq">
        <div class="title"><?= GetMessage("SUCCESS_MESSAGE"); ?></div>
    </div>
    <?php return; ?>
<?php endif; ?>
<div class="greyForm">
    <h3><?= GetMessage("FORM_TITLE"); ?></h3>
    <form action="<?= $APPLICATION->GetCurPage(); ?>" method="POST" enctype="multipart/form-data">
        <?= bitrix_sessid_post(); ?>

        <?php foreach ($arResult["FIELDS"] as $arField) : ?>
            <?php if ($arField["TYPE"] == 'string') : ?>
                <div class="whiteInput <?= $arResult["ERRORS"][$arField["NAME"]]?"error":""?>">
                    <input name="<?= $arField["NAME"]; ?>"
                           <?= ($arField["MANDATORY"])?"required":""?>
                           type="<?= ($arField["NAME"] == "Email")?"email":"text"; ?>"
                           placeholder="<?= $arField["PLACEHOLDER"]; ?> <?= $arField["MANDATORY"]?" *":""; ?>"
                           value="<?= $arResult["RESPONSE"][$arField["NAME"]]; ?>"
                           class="<?= ($arField["MANDATORY"])?"required":""?>">
                </div>
            <?php elseif ($arField["TYPE"] == 'text') :?>
                <div class="whiteArea <?= $arResult["ERRORS"][$arField["NAME"]]?"error":""?>">
                    <textarea name="<?= $arField["NAME"]; ?>"
                              placeholder="<?= $arField["PLACEHOLDER"]; ?> <?= $arField["MANDATORY"]?" *":""; ?>"
                              <?= ($arField["MANDATORY"])?"required":""?>
                    ><?= $arResult["RESPONSE"][$arField["NAME"]]; ?></textarea>
                </div>
            <?php endif; ?>
        <?php endforeach; ?>

        <div class="Button blackTrntButton">
            <input type="submit" name="submit" value="<?= GetMessage("SUBMIT_NAME"); ?>">
        </div>
    </form>
</div>
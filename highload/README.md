# Формы работающая через Highload-блоки

Не знаю, почему я не сделал это через lang-файлы

```php
$APPLICATION->IncludeComponent(
    "custom:form",
    "",
    array(
        "AJAX_MODE" => "Y",
        "AJAX_OPTION_JUMP" => "N",
        "AJAX_OPTION_HISTORY" => "N",
        "AJAX_OPTION_STYLE" => "N",
        "AJAX_OPTION_SHADOW" => "N",
        "FORM_TITLE" => "Заголовок формы",
        "SUCCESS_MESSAGE" => "Сообщение для успешной отправки",
        "SUBMIT_NAME" => "Имя кнопки",
        "METHOD" => "POST",
        "TABLE_ID" => ID таблицы Hightload-блока,
        "SITE_ID" => SITE_ID,
        "LANG_ID" => "de",
    )
);
```


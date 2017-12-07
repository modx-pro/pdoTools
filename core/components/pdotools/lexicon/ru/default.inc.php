<?php
/**
 * Default Russian Lexicon Entries for pdoTools
 *
 * @package pdotools
 * @subpackage lexicon
 * @language ru
 */
$_lang['pdotools'] = 'pdoTools';

$_lang['area_pdotools_main'] = 'Основные';

$_lang['setting_pdoTools.class'] = 'FQN имя класса pdoTools';
$_lang['setting_pdoTools.class_desc'] = 'FQN имя класса pdoTools для загрузки из настройки "pdotools_class_path".';
$_lang['setting_pdotools_class_path'] = 'Путь к классу pdoTools';
$_lang['setting_pdotools_class_path_desc'] = 'Директории с классом pdoTools, из которого он будет загружен, используя FQN имя.';

$_lang['setting_pdoFetch.class'] = 'FQN имя класса pdoFetch';
$_lang['setting_pdoFetch.class_desc'] = 'FQN имя класса pdoTools для загрузки из настройки "pdofetch_class_path".';
$_lang['setting_pdofetch_class_path'] = 'Путь к классу pdoFetch';
$_lang['setting_pdofetch_class_path_desc'] = 'Директории с классом pdoFetch, из которого он будет загружен, используя FQN имя.';

$_lang['setting_pdoParser.class'] = 'FQN имя класса pdoParser';
$_lang['setting_pdoParser.class_desc'] = 'FQN имя класса pdoParser для загрузки из настройки "parser_class_path".';
$_lang['setting_parser_class'] = 'Используемый парсер';
$_lang['setting_parser_class_desc'] = 'Класс парсера, который используется для разбора тегов MODX.';
$_lang['setting_parser_class_path'] = 'Путь к классу парсера';
$_lang['setting_parser_class_path_desc'] = 'Директории с классом парсера, из которого он будет загружен, используя FQN имя.';

$_lang['setting_pdotools_elements_path'] = 'Путь к элементам';
$_lang['setting_pdotools_elements_path_desc'] = 'Директория, в которой хранятся файлы элементов для загрузки через @FILE.';
$_lang['setting_pdotools_fenom_default'] = 'Использовать Fenom в чанках';
$_lang['setting_pdotools_fenom_default_desc'] = 'Сниппеты pdoTools будут использовать шаблонизатор Fenom для обработки чанков.';
$_lang['setting_pdotools_fenom_parser'] = 'Использовать Fenom на страницах';
$_lang['setting_pdotools_fenom_parser_desc'] = 'pdoParser будет использовать шаблонизатор Fenom для обработки страниц и шаблонов сайта. Конечно, он сам должен быть активирован.';
$_lang['setting_pdotools_fenom_php'] = 'Разрешить PHP в Fenom';
$_lang['setting_pdotools_fenom_php_desc'] = 'В чанках и на страницах сайта можно использовать функции PHP для оформления, например {$.php.phpinfo()}.';
$_lang['setting_pdotools_fenom_modx'] = 'Разрешить MODX в Fenom';
$_lang['setting_pdotools_fenom_modx_desc'] = 'Эта опция разрешает доступ к объектам MODX и pdoTools из Fenom через переменные {$modx} и {$pdoTools}.';
$_lang['setting_pdotools_fenom_options'] = 'Настройки Fenom';
$_lang['setting_pdotools_fenom_options_desc'] = 'JSON строка с массивом настроек согласно <a href="https://github.com/fenom-template/fenom/blob/master/docs/ru/configuration.md" target="_blank">официальной документации</a>. Например: {"auto_escape":true,"force_include":true}';
$_lang['setting_pdotools_fenom_cache'] = 'Кэширование скомпилированных чанков';
$_lang['setting_pdotools_fenom_cache_desc'] = 'Если вы используете большие и сложные чанки Fenom, то можно включить кэширование их скомпилированных версий. Они будут обновляться только при очистке системного кэша. Не рекомендуется при разработке сайта.';
$_lang['setting_pdotools_fenom_save_on_errors'] = 'Сохранять ошибки';
$_lang['setting_pdotools_fenom_save_on_errors_desc'] = 'Включите эту опцию, чтобы сохранять ошибки компиляции Fenom в директорию "core/cache/default/pdotools/error" для последующей отладки.';
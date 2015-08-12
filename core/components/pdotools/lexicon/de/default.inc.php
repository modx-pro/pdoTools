<?php
/**
 * Default German Lexicon Entries for pdoTools
 *
 * @package pdotools
 * @subpackage lexicon
 * @language de
 * 
 * pdoTools translated to German by Jan-Christoph Ihrens (enigmatic_user, enigma@lunamail.de)
 */
$_lang['pdotools'] = 'pdoTools';


$_lang['area_pdotools_main'] = 'Grundlegende Einstellungen';
$_lang['setting_pdoTools.class'] = 'FQN (fully qualified name) von pdoTools';
$_lang['setting_pdoTools.class_desc'] = 'Pfad zum Laden der Klasse von "MODX_CORE_PATH . model/modx/".';
$_lang['setting_pdoFetch.class'] = 'FQN (fully qualified name) von pdoFetch';
$_lang['setting_pdoFetch.class_desc'] = 'Pfad zum Laden der Klasse von "MODX_CORE_PATH . model/modx/".';
$_lang['setting_pdoParser.class'] = 'FQN (fully qualified name) von pdoParser';
$_lang['setting_pdoParser.class_desc'] = 'Pfad zum Laden der Klasse von "MODX_CORE_PATH . model/modx/".';
$_lang['setting_parser_class'] = 'Parser-Klasse';
$_lang['setting_parser_class_desc'] = 'Die Parser-Klasse, die verwendet wird, um die MODX-Tags zu verarbeiten.';
$_lang['setting_parser_class_path'] = 'Der Pfad zum Parser';
$_lang['setting_parser_class_path_desc'] = 'Der Pfad, der die Datei mit dem Parser enthält.';

$_lang['setting_pdotools_fenom_default'] = 'Verwenden Sie Fenom für chunks';
$_lang['setting_pdotools_fenom_default_desc'] = 'pdoTools snippets verwenden Sie die Template-engine Fenom für chunk-Verarbeitung.';
$_lang['setting_pdotools_fenom_parser'] = 'Verwenden Sie Fenom für Seiten';
$_lang['setting_pdotools_fenom_parser_desc'] = 'pdoParser verwenden Sie die Template-engine Fenom bei der Verarbeitung Seiten und Website-Vorlagen. Es muss natürlich aktiviert sein.';
$_lang['setting_pdotools_fenom_php'] = 'PHP in Fenom';
$_lang['setting_pdotools_fenom_php_desc'] = 'Wenn aktiviert, können Sie PHP-Funktionen, die in den Vorlagen und Stücke, zum Beispiel {$.php.phpinfo()}.';
$_lang['setting_pdotools_fenom_modx'] = 'Erlaube MODX in Fenom';
$_lang['setting_pdotools_fenom_modx_desc'] = 'Diese Option ermöglicht die Verwendung von MODX und pdoTools in Fenom über {$modx} und {$pdoTools} Variablen.';
$_lang['setting_pdotools_fenom_options'] = 'Fenom Optionen';
$_lang['setting_pdotools_fenom_options_desc'] = 'JSON-string-array mit der beschriebenen Einstellungen auf <a href="https://github.com/fenom-Vorlage/fenom/blob/master/docs/de/Konfiguration.md" target="_blank">offiziellen Dokumentation</a>. Beispiel: {"auto_escape":true,"force_include":true}';
$_lang['setting_pdotools_fenom_cache'] = 'Caching zusammengestellt Brocken';
$_lang['setting_pdotools_fenom_cache_desc'] = 'Wenn Sie große und komplexe Fenom Brocken, können Sie die Zwischenspeicherung aktivieren der die kompilierten Versionen. Sie wird nur aktualisiert werden, wenn Sie die system-cache. Nicht empfohlen für die Entwicklung der Website.';
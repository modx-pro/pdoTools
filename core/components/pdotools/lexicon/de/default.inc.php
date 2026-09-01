<?php
/**
 * Default German Lexicon Entries for pdoTools
 *
 * @package pdotools
 * @subpackage lexicon
 * @language de
 */
$_lang['pdotools'] = 'pdoTools';

$_lang['area_pdotools_main'] = 'Grundeinstellungen';

$_lang['setting_pdotools_elements_path'] = 'Pfad zu den Elementen';
$_lang['setting_pdotools_elements_path_desc'] = 'Verzeichnis mit Datei-Elementen, die mittels @FILE-Bindung geladen werden können.';
$_lang['setting_pdotools_fenom_default'] = 'Fenom für Chunks verwenden';
$_lang['setting_pdotools_fenom_default_desc'] = 'pdoTools-Snippets verwenden die Template-Engine Fenom für die Verarbeitung von Chunks.';
$_lang['setting_pdotools_fenom_parser'] = 'Fenom für Seiten verwenden';
$_lang['setting_pdotools_fenom_parser_desc'] = 'pdoParser verwendet die Template-Engine Fenom bei der Verarbeitung von Seiten und Templates. Sie muss dafür natürlich aktiviert sein.';
$_lang['setting_pdotools_fenom_php'] = 'Erlaube PHP in Fenom';
$_lang['setting_pdotools_fenom_php_desc'] = 'Wenn diese Einstellung aktiviert ist, können Sie PHP-Funktionen in Templates und Chunks nutzen, zum Beispiel {$.php.phpinfo()}.';
$_lang['setting_pdotools_fenom_modx'] = 'Erlaube MODX in Fenom';
$_lang['setting_pdotools_fenom_modx_desc'] = 'Diese Option ermöglicht die Verwendung von MODX und pdoTools in Fenom über die Variablen {$modx} und {$pdoTools}.';
$_lang['setting_pdotools_fenom_options'] = 'Fenom-Optionen';
$_lang['setting_pdotools_fenom_options_desc'] = 'JSON-String mit einem Array von Einstellungen, die in der <a href="https://github.com/fenom-Vorlage/fenom/blob/master/docs/de/Konfiguration.md" target="_blank">offiziellen Dokumentation</a> beschrieben werden. Beispiel: {"auto_escape": true, "force_include": true}';
$_lang['setting_pdotools_fenom_cache'] = 'Caching verarbeiteter Chunks';
$_lang['setting_pdotools_fenom_cache_desc'] = 'Wenn Sie große und komplexe Fenom-Chunks verwenden, können Sie das Caching der verarbeiteten Versionen aktivieren. Diese werden nur aktualisiert, wenn Sie den System-Cache leeren. Nicht empfohlen während der Entwicklung der Website.';
$_lang['setting_pdotools_fenom_save_on_errors'] = 'Fehlermeldungen speichern';
$_lang['setting_pdotools_fenom_save_on_errors_desc'] = 'Aktivieren Sie diese Einstellung, um die Fenom-Compiler-Fehlermeldungen im Verzeichnis "core/cache/default/pdotools/error" zum späteren Debuggen zu speichern.';
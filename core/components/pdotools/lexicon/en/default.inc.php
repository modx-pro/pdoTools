<?php
/**
 * Default English Lexicon Entries for pdoTools
 *
 * @package pdotools
 * @subpackage lexicon
 * @language en
 */
$_lang['pdotools'] = 'pdoTools';

$_lang['area_pdotools_main'] = 'Main';

$_lang['setting_pdotools_elements_path'] = 'Path to elements';
$_lang['setting_pdotools_elements_path_desc'] = 'Directory with file elements to load via @FILE binding.';
$_lang['setting_pdotools_fenom_default'] = 'Use Fenom for chunks';
$_lang['setting_pdotools_fenom_default_desc'] = 'pdoTools snippets will use the templating engine Fenom for chunk processing.';
$_lang['setting_pdotools_fenom_parser'] = 'Use Fenom for pages';
$_lang['setting_pdotools_fenom_parser_desc'] = 'pdoParser will use the templating engine Fenom when processing pages and site templates. Of course, it must be activated.';
$_lang['setting_pdotools_fenom_php'] = 'Allow PHP in Fenom';
$_lang['setting_pdotools_fenom_php_desc'] = 'If enabled, you can use PHP functions in templates and chunks, for example {$.php.phpinfo()}.';
$_lang['setting_pdotools_fenom_modx'] = 'Allow MODX in Fenom';
$_lang['setting_pdotools_fenom_modx_desc'] = 'This options allows you to use MODX and pdoTools in Fenom via {$modx} and {$pdoTools} variables.';
$_lang['setting_pdotools_fenom_options'] = 'Fenom options';
$_lang['setting_pdotools_fenom_options_desc'] = 'JSON string with array of settings described on <a href="https://github.com/fenom-template/fenom/blob/master/docs/en/configuration.md" target="_blank">official documentation</a>. For example: {"auto_escape":true,"force_include":true}';
$_lang['setting_pdotools_fenom_cache'] = 'Caching compiled chunks';
$_lang['setting_pdotools_fenom_cache_desc'] = 'If you use large and complex Fenom chunks, you can enable caching of its compiled versions. They will be updated only when you clear the system cache. Not recommended for the development of the site.';
$_lang['setting_pdotools_fenom_save_on_errors'] = 'Save errors';
$_lang['setting_pdotools_fenom_save_on_errors_desc'] = 'Enable this option to save Fenom compilation errors to the "core/cache/default/pdotools/error" directory for later debugging.';

$_lang['setting_pdotools_filter_path'] = 'Filter the file element\'s path.';
$_lang['setting_pdotools_filter_path_desc'] = 'Removes the "../" construction from the path to the file element.';
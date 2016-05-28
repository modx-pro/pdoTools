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

$_lang['setting_pdoTools.class'] = 'FQN of pdoTools';
$_lang['setting_pdoTools.class_desc'] = 'Path for loading class from system setting "pdotools_class_path".';
$_lang['setting_pdotools_class_path'] = 'Base path to pdoTools class';
$_lang['setting_pdotools_class_path_desc'] = 'Base path to pdoTools class from which it will be loaded with FQN.';

$_lang['setting_pdoFetch.class'] = 'FQN of pdoFetch';
$_lang['setting_pdoFetch.class_desc'] = 'Path for loading class from system setting "pdofetch_class_path".';
$_lang['setting_pdofetch_class_path'] = 'Base path to pdoFetch class';
$_lang['setting_pdofetch_class_path_desc'] = 'Base path to pdoFetch class from which it will be loaded with FQN.';

$_lang['setting_pdoParser.class'] = 'FQN of pdoParser';
$_lang['setting_pdoParser.class_desc'] = 'Path for loading class from system setting "parser_class_path".';
$_lang['setting_parser_class'] = 'Parser class';
$_lang['setting_parser_class_desc'] = 'Parser class that will be used to process the MODX tags.';
$_lang['setting_parser_class_path'] = 'The path to the parser';
$_lang['setting_parser_class_path_desc'] = 'Base path to parser class from which it will be loaded with FQN.';

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
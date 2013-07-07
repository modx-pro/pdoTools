<?php
/**
 * Properties English Lexicon Entries for pdoTools
 *
 * @package pdotools
 * @subpackage lexicon
 */
$_lang['pdotools_prop_context'] = 'Which Context should be searched in.';
$_lang['pdotools_prop_depth'] = 'Integer value indicating depth to search for resources from each parent. First level of resources beneath parent is depth.';
$_lang['pdotools_prop_fastMode'] = 'Fast chunks processing. If true, MODX parser will not be used and unprocessed tags will be cut.';
$_lang['pdotools_prop_first'] = 'Define the idx which represents the first resource.';
$_lang['pdotools_prop_hideContainers'] = 'If set, will not show any Resources marked as a container (isfolder).';
$_lang['pdotools_prop_idx'] = 'You can define the starting idx of the resources, which is an property that is incremented as each resource is rendered.';
$_lang['pdotools_prop_includeContent'] = 'Indicates if the content of each resource should be returned in the results.';
$_lang['pdotools_prop_includeTVs'] = 'An optional comma-delimited list of TemplateVar names to include.';
$_lang['pdotools_prop_last'] = 'Define the idx which represents the last resource. Default is # of resources being summarized + first - 1.';
$_lang['pdotools_prop_limit'] = 'Limits the number of resources returned.  Use `0` for unlimited results.';
$_lang['pdotools_prop_offset'] = 'An offset of resources returned by the criteria to skip.';
$_lang['pdotools_prop_outputSeparator'] = 'An optional string to separate each tpl instance.';
$_lang['pdotools_prop_parents'] = 'Comma-delimited list of ids serving as parents. Use -1 to ignore parents when specifying resources to include.';
$_lang['pdotools_prop_resources'] = 'Comma-delimited list of ids to include in the results. Prefix an id with a dash to exclude the resource from the result.';
$_lang['pdotools_prop_returnIds'] = 'If true, snippet will return comma separated list of ids instead of results.';
$_lang['pdotools_prop_showDeleted'] = 'If true, will also show Resources regardless if they are deleted.';
$_lang['pdotools_prop_showHidden'] = 'If true, will show Resources regardless if they are hidden from the menus.';
$_lang['pdotools_prop_showLog'] = 'If true, snippet will add detailed log of query for managers.';
$_lang['pdotools_prop_showUnpublished'] = 'If true, will also show Resources if they are unpublished.';
$_lang['pdotools_prop_sortby'] = 'Any Resource Field (including Template Variables if it was included) to sort by. Some common fields to sort on are publishedon, menuindex, pagetitle etc, but see the Resources documentation for all fields. Specify fields with the name only, not using the tag syntax. Note that when using fields like template, publishedby and the likes for sorting, it will be sorted on the raw values, so the template or user ID, and NOT their names. You can also sort randomly by specifying RAND().';
$_lang['pdotools_prop_sortdir'] = 'Order which to sort by.';
$_lang['pdotools_prop_toPlaceholder'] = 'If set, will assign the result to this placeholder instead of outputting it directly.';
$_lang['pdotools_prop_totalVar'] = 'Define the key of a placeholder set by getResources indicating the total number of Resources that would be selected not considering the limit value.';
$_lang['pdotools_prop_tpl'] = 'Name of a chunk serving as a resource template. If not provided, properties are dumped to output for each resource.';
$_lang['pdotools_prop_tplFirst'] = 'Name of a chunk serving as resource template for the first resource.';
$_lang['pdotools_prop_tplLast'] = 'Name of a chunk serving as resource template for the last resource.';
$_lang['pdotools_prop_tplOdd'] = 'Name of a chunk serving as resource template for resources with an odd idx value (see idx property).';
$_lang['pdotools_prop_tplWrapper'] = 'Name of a chunk serving as a wrapper template for the output.';
$_lang['pdotools_prop_tvPrefix'] = 'The prefix for TemplateVar properties.';
$_lang['pdotools_prop_where'] = 'A JSON-style expression of criteria to build any additional where clauses from.';
$_lang['pdotools_prop_wrapIfEmpty'] = 'If true, will output the wrapper specified in &tplWrapper even if the output is empty.';

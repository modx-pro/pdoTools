<?php
/**
 * Properties English Lexicon Entries for pdoTools
 *
 * @package pdotools
 * @subpackage lexicon
 * @language en
 */
$_lang['pdotools_prop_context'] = 'Which Context should be searched in.';
$_lang['pdotools_prop_field_context'] = 'Context of resource for selecting its parents. Needed for work of parameters "&top" and "&topLevel".';
$_lang['pdotools_prop_depth'] = 'Integer value indicating depth to search for resources from each parent. First level of resources below parent has a depth of 1.';
$_lang['pdotools_prop_fastMode'] = 'Fast chunks processing. If true, MODX parser will not be used and unprocessed tags will be cut.';
$_lang['pdotools_prop_first'] = 'Define the idx which represents the first resource.';
$_lang['pdotools_prop_hideContainers'] = 'If set, will not show any Resources marked as a container (isfolder).';
$_lang['pdotools_prop_idx'] = 'You can define the starting idx of the resources, which is a property that is incremented as each resource is rendered.';
$_lang['pdotools_prop_includeContent'] = 'Indicates if the content of each resource should be returned in the results.';

$_lang['pdotools_prop_includeTVs'] = 'An optional comma-delimited list of TemplateVar names to include.';
$_lang['pdotools_prop_prepareTVs'] = 'Comma-separated list of TV names that need to be prepared. By default it is set to "1", so all TVs in "&includeTVs=``" will be prepared.';
$_lang['pdotools_prop_processTVs'] = 'Comma-separated list of TV names that need to be processed. If you set it to "1" - all TVs in "&includeTVs=``" will be processed. By default it is empty.';
$_lang['pdotools_prop_tvFilters'] = 'Delimited list of TemplateVar values to filter resources by. Supports two delimiters and two value search formats. The first delimiter || represents a logical OR and the primary grouping mechanism.  Within each group you can provide a comma-delimited list of values. These values can be either tied to a specific TemplateVar by name, e.g. myTV==value, or just the value, indicating you are searching for the value in any TemplateVar tied to the Resource. An example would be &tvFilters=`filter2==one,filter1==bar%||filter1==foo`. <br />NOTE: filtering by values uses a LIKE query and % is considered a wildcard. <br />ANOTHER NOTE: This only looks at the raw value set for specific Resource, i. e. there must be a value specifically set for the Resource and it is not evaluated.';
$_lang['pdotools_prop_tvFiltersAndDelimiter'] = 'The delimiter to use to separate logical AND expressions in "&tvFilters". Default is ",".';
$_lang['pdotools_prop_tvFiltersOrDelimiter'] = 'The delimiter to use to separate logical OR expressions in "&tvFilters". Default is "||".';

$_lang['pdotools_prop_last'] = 'Define the idx which represents the last resource. Default is # of resources being summarized + first - 1.';
$_lang['pdotools_prop_neighbors_limit'] = 'The number of neighboring documents on the right and left. The default is 1.';
$_lang['pdotools_prop_limit'] = 'Limits the number of resources returned.  Use "0" for unlimited results.';
$_lang['pdotools_prop_offset'] = 'An offset of resources returned by the criteria to skip.';
$_lang['pdotools_prop_outputSeparator'] = 'An optional string to separate each tpl instance.';
$_lang['pdotools_prop_parents'] = 'Comma-delimited list of ids serving as parents. Use "0" to ignore parents when specifying resources to include. Prefix an id of parent with a dash to exclude it and its children from the result.';
$_lang['pdotools_prop_resources'] = 'Comma-delimited list of ids to include in the results. Prefix an id with a dash to exclude the resource from the result.';
$_lang['pdotools_prop_templates'] = 'Comma-delimited list of templates to filter the results. Prefix an id of template with a dash to exclude the resource with it from the result.';
$_lang['pdotools_prop_from'] = 'Resource id from which breadcrumb is created. Usually it is root of site, e.g. "0".';
$_lang['pdotools_prop_to'] = 'Resource id whose breadcrumb is created. By default it is id of the current resource.';
$_lang['pdotools_prop_users'] = 'Comma-separated list of users for output. You can use the usernames and ids. If the value starts with a dash, this user is excluded from the query.';
$_lang['pdotools_prop_groups'] = 'Comma-separated list of users groups. You can use the names and ids. If the value starts with a dash, so the user should not be in this group.';
$_lang['pdotools_prop_roles'] = 'Comma-separated list of users roles. You can use the names and ids. If the value starts with a dash, then this role of user should not exist.';
$_lang['pdotools_prop_exclude'] = 'Comma-separated list of resource ids that need to be excluded from the query.';
$_lang['pdotools_prop_returnIds'] = 'If true, snippet will return comma separated list of ids instead of results.';
$_lang['pdotools_prop_showBlocked'] = 'If true, will show users regardless if they are blocked.';
$_lang['pdotools_prop_showInactive'] = 'If true, will show users regardless if they are inactive.';
$_lang['pdotools_prop_showDeleted'] = 'If true, will show Resources regardless if they are deleted.';
$_lang['pdotools_prop_showHidden'] = 'If true, will show Resources regardless if they are hidden from the menus.';
$_lang['pdotools_prop_showLog'] = 'If true, snippet will add detailed log of query for developers.';
$_lang['pdotools_prop_showUnpublished'] = 'If true, will also show Resources regardless if they are unpublished.';
$_lang['pdotools_prop_showAtHome'] = 'Show bread crumbs on the main page.';
$_lang['pdotools_prop_showHome'] = 'Display a link to the main at the beginning of navigation.';
$_lang['pdotools_prop_showCurrent'] = 'Display the current document in the navigation.';
$_lang['pdotools_prop_hideSingle'] = 'Do not display the result if it consists of only one item.';
$_lang['pdotools_prop_hideUnsearchable'] = 'Do not display resources that are not searchable.';

$_lang['pdotools_prop_sortby'] = 'Any Resource Field (including Template Variables if they have been included) to sort by. Some common fields to sort on are publishedon, menuindex, pagetitle etc., but see the Resources documentation for all fields. Specify fields with the name only, not using the tag syntax. Note that when using fields like template, publishedby and the likes for sorting, it will be sorted on the raw values, so the template or user ID, and NOT their names. You can also sort randomly by specifying RAND().';
$_lang['pdotools_prop_sortbyTV'] = 'Sort by the TV. If it is not specified in &includeTVs, it will be included automatically.';
$_lang['pdotools_prop_sortbyTVType'] = 'Sort by TV. The options are: string, integer, decimal, and datetime. If empty, then the TV will be sorted depending on its type: text, number or date.';
$_lang['pdotools_prop_sortdir'] = 'Order which to sort by: descending or ascending';
$_lang['pdotools_prop_sortdirTV'] = 'Sort direction of TV: descending or ascending. If not specified, it will be equal to the parameter &sortdir.';
$_lang['pdotools_prop_toPlaceholder'] = 'If set, will assign the result to this placeholder instead of outputting it directly.';
$_lang['pdotools_prop_toSeparatePlaceholders'] = 'If set, will assign EACH result to a separate placeholder named by this param suffixed with a sequential number (starting from 0).';
$_lang['pdotools_prop_totalVar'] = 'Define the key of a placeholder set by getResources indicating the total number of Resources that would be selected not considering the limit value.';  // getResources???

$_lang['pdotools_prop_tpl'] = 'Name of a chunk serving as a resource template. If not provided, properties are dumped to output for each resource.';
$_lang['pdotools_prop_tplFirst'] = 'Name of a chunk serving as resource template for the first resource.';
$_lang['pdotools_prop_tplLast'] = 'Name of a chunk serving as resource template for the last resource.';
$_lang['pdotools_prop_tplOdd'] = 'Name of a chunk serving as resource template for resources with an odd idx value (see idx property).';
$_lang['pdotools_prop_tplWrapper'] = 'Name of a chunk serving as a wrapper template for the output. This does not work with toSeparatePlaceholders.';
$_lang['pdotools_prop_neighbors_tplWrapper'] = 'Name of a chunk serving as a wrapper template for the output. This does not work with toSeparatePlaceholders.';
$_lang['pdotools_prop_tvPrefix'] = 'The prefix for TemplateVar properties.';
$_lang['pdotools_prop_where'] = 'A JSON-style expression of criteria to build any additional where clauses from.';
$_lang['pdotools_prop_wrapIfEmpty'] = 'If true, will output the wrapper specified in &tplWrapper even if the output is empty.';
$_lang['pdotools_prop_tplOperator'] = 'An optional operator to use for the tplCondition when comparing against the conditionalTpls operands. Default is == (equals).';
$_lang['pdotools_prop_tplCondition'] = 'A condition to compare against the conditionalTpls property to map Resources to different tpls based on custom conditional logic.';
$_lang['pdotools_prop_conditionalTpls'] = 'A JSON map of conditional operands and tpls to compare against the tplCondition property using the specified tplOperator.';
$_lang['pdotools_prop_tplCurrent'] = 'Сhunk of the current document in navigation.';
$_lang['pdotools_prop_tplHome'] = 'Сhunk of the link to the main page.';
$_lang['pdotools_prop_tplMax'] = 'Сhunk which is added to the beginning of the results if there are more items than allowed by "&limit".';
$_lang['pdotools_prop_tplPrev'] = 'Сhunk with link to previous document.';
$_lang['pdotools_prop_tplUp'] = 'Сhunk with link to the parent document.';
$_lang['pdotools_prop_tplNext'] = 'Сhunk with link to the following document.';

$_lang['pdotools_prop_select'] = 'Comma-separated list of columns for select from database. You can specify JSON string with array, for example {"modResource":"id,pagetitle,content"}.';
$_lang['pdotools_prop_loadModels'] = 'Comma-separated list of 3rd party components that are needed for the query. For example: "&loadModels=`ms2gallery,msearch2`".';
$_lang['pdotools_prop_direction'] = 'Direction or breadcrumb: Left To Right (ltr) or Right To Left (rtl) for Arabic language for example.';
$_lang['pdotools_prop_id'] = 'Id of the resource.';
$_lang['pdotools_prop_field'] = 'Field of the resource.';
$_lang['pdotools_prop_top'] = 'Selects parent of specified "&id" on level "&top".';
$_lang['pdotools_prop_topLevel'] = 'Selects parent of specified "&id" on level "&topLevel" from root of context.';

$_lang['pdotools_prop_forceXML'] = 'Force the output page as xml.';
$_lang['pdotools_prop_sitemapSchema'] = 'Schema of sitemap.';
$_lang['pdotools_prop_scheme'] = 'Scheme of generation of links: "uri" for the substitution of document uri (very fast) or a parameter to modX::makeUrl().';

$_lang['pdotools_prop_field_default'] = 'Specify an additional resource field the content of which will be returned if the field specified in "&field" is empty.';
$_lang['pdotools_prop_field_output'] = 'This string will be returned if the the fields specified in "&default" and "&field" are empty.';

$_lang['pdotools_prop_cache'] = 'Caching the results of the snippet.';
$_lang['pdotools_prop_cachePageKey'] = 'The name of the key cache.';
$_lang['pdotools_prop_cacheTime'] = 'Time until the cache expires, in seconds.';
$_lang['pdotools_prop_cacheKey'] = 'Cache key. Stored in "core/cache/default/yourkey"';
$_lang['pdotools_prop_cacheAnonymous'] = 'Enable caching only for unauthorized visitors.';
$_lang['pdotools_prop_element'] = 'The name of the snippet to run.';
$_lang['pdotools_prop_maxLimit'] = 'The maximum limit of the query. Overrides the limit specified by the user via a url.';
$_lang['pdotools_prop_page'] = 'Number of page for output. Overlaps number specified by the user via the url.';
$_lang['pdotools_prop_pageLimit'] = 'Number of links on a pages. If is 7 or more turns on the advanced mode.';
$_lang['pdotools_prop_pageNavVar'] = 'Name of placeholder for output pagination.';
$_lang['pdotools_prop_pageCountVar'] = 'Name of placeholder for output number of pages.';
$_lang['pdotools_prop_pageVarKey'] = 'The Name of the variable to search for the page number in the url.';
$_lang['pdotools_prop_pageLinkScheme'] = 'Scheme of generation link to page. You can use placeholders [[+pageVarKey]] and [[+page]]';
$_lang['pdotools_prop_plPrefix'] = 'Prefix for output placeholders, by default is "wf.".';

$_lang['pdotools_prop_tplPage'] = 'Chunk of registration of the normal link to the page.';
$_lang['pdotools_prop_tplPageActive'] = 'Chunk of the link to the current page.';
$_lang['pdotools_prop_tplPageFirst'] = 'Chunk of the link to the first page.';
$_lang['pdotools_prop_tplPagePrev'] = 'Chunk of the link to the previous page.';
$_lang['pdotools_prop_tplPageLast'] = 'Chunk of the link to the last page.';
$_lang['pdotools_prop_tplPageNext'] = 'Chunk of the link to the next page.';
$_lang['pdotools_prop_tplPageFirstEmpty'] = 'Chunk output if no link to the first page.';
$_lang['pdotools_prop_tplPagePrevEmpty'] = 'Chunk output if no link to the previous page.';
$_lang['pdotools_prop_tplPageLastEmpty'] = 'Chunk output if no link to the last page.';
$_lang['pdotools_prop_tplPageNextEmpty'] = 'Chunk output if no link to the next page.';
$_lang['pdotools_prop_tplPageSkip'] = 'Chunk clearance missing pages in advanced mode, the display (&pageLimit >= 7).';
$_lang['pdotools_prop_tplPageWrapper'] = 'Chunk of the decoration of the block pagination, as you could see placeholders contains pages.';

$_lang['pdotools_prop_previewUnpublished'] = 'Optional. If set to Yes, if you are logged into the mgr and have the view_unpublished permission, it will allow previewing of unpublished resources in your menus in the front-end.';
$_lang['pdotools_prop_checkPermissions'] = 'Comma-separated list of permissions to check when building the menu.';
$_lang['pdotools_prop_displayStart'] = 'Show the document as referenced by startId in the menu.';  // What is startId?
$_lang['pdotools_prop_hideSubMenus'] = 'The hideSubMenus parameter will remove all non-active submenus from the script output if set to 1. This parameter only works if multiple levels are being displayed.';
$_lang['pdotools_prop_useWeblinkUrl'] = 'If WebLinks are used in the output, script will output the link specified in the WebLink instead of the normal MODX link. To use the standard display of WebLinks (like any other Resource) set this to 0.';
$_lang['pdotools_prop_rowIdPrefix'] = 'If set, script will replace the id placeholder with a unique id consisting of the specified prefix plus the Resource id.';
$_lang['pdotools_prop_level'] = 'Depth (number of levels) to build the menu from. 0 goes through all levels.';
$_lang['pdotools_prop_hereId'] = 'Optional. If set, will change the "here" Resource to this ID. Defaults to the currently active Resource.';

$_lang['pdotools_prop_webLinkClass'] = 'CSS class for weblink items.';
$_lang['pdotools_prop_firstClass'] = 'CSS class for the first item at a given menu level.';
$_lang['pdotools_prop_hereClass'] = 'CSS class for the items showing where you are, all the way up the chain.';
$_lang['pdotools_prop_innerClass'] = 'CSS class for the inner template.';
$_lang['pdotools_prop_lastClass'] = 'CSS class for the last item at a given menu level.';
$_lang['pdotools_prop_levelClass'] = 'CSS class denoting every output row level. The level number will be added to the specified class (level1, level2, level3 etc. if you specified "level").';
$_lang['pdotools_prop_outerClass'] = 'CSS class for the outer template.';
$_lang['pdotools_prop_parentClass'] = 'CSS class for menu items that are a container and have children.';
$_lang['pdotools_prop_rowClass'] = 'CSS class denoting each output row.';
$_lang['pdotools_prop_selfClass'] = 'CSS class for the current item.';

$_lang['pdotools_prop_tplCategoryFolder'] = 'Name of the chunk containing the template for the outer most container; if not included, a string including "&lt;ul&gt;[[+wf.wrapper]]&lt;/ul&gt;" is assumed.';  // Why "+wf."? This is pdoMenu, not Wayfinder...  ;-)
$_lang['pdotools_prop_tplHere'] = 'Name of the chunk containing the template for the current Resource if it is a container and has children. Remember the [[+wf.wrapper]] placeholder to output the children documents.';
$_lang['pdotools_prop_tplInner'] = 'Name of the chunk containing the template for each submenu. If no innerTpl is specified the outerTpl is used in its place.';
$_lang['pdotools_prop_tplInnerHere'] = 'Name of the chunk containing the template for the current Resource if it is in a subfolder.';
$_lang['pdotools_prop_tplInnerRow'] = 'Name of the chunk containing the template for the current Resource if it is in a subfolder.';  // Really the current one??? See above!
$_lang['pdotools_prop_tplOuter'] = 'Name of the chunk containing the template for the outer most container; if not included, a string including "&lt;ul&gt;[[+wrapper]]&lt;/ul&gt;" is assumed.';  // Same as pdotools_prop_tplCategoryFolder apart from the default HTML
$_lang['pdotools_prop_tplParentRow'] = 'Name of the chunk containing the template for any Resource that is a container and has children. Remember the [[+wrapper]] placeholder to output the children documents.';
$_lang['pdotools_prop_tplParentRowActive'] = 'Name of the chunk containing the template for items that are containers, have children and are currently active in the tree.';
$_lang['pdotools_prop_tplParentRowHere'] = 'Name of the chunk containing the template for the current Resource if it is a container and has children. Remember the [[+wf.wrapper]] placeholder to output the children documents.';
$_lang['pdotools_prop_tplStart'] = 'Name of the chunk containing the template for the start item, if enabled via the &displayStart parameter. Note: the default template shows the start item but does not link it. If you do not need a link, a class can be applied to the default template using the parameter &firstClass=`className`.';

$_lang['pdotools_prop_ultimate'] = 'Parameters &top and &topLevel works as in snippet UltimateParent.';
$_lang['pdotools_prop_loop'] = 'Loop the links. If there no link to the next page, make the link to the first page and vice versa';

$_lang['pdotools_prop_countChildren'] = 'Display the exact number of active descendants of the document in placeholder [[+children]].';

$_lang['pdotools_prop_ajax'] = 'Enable support of ajax requests.';
$_lang['pdotools_prop_ajaxMode'] = 'Ajax pagination out of the box. Available in 3 modes: "default", "button" and "scroll".';
$_lang['pdotools_prop_ajaxElemWrapper'] = 'jQuery selector for wrapper element with the results and pagination.';
$_lang['pdotools_prop_ajaxElemRows'] = 'jQuery selector for element with results.';
$_lang['pdotools_prop_ajaxElemPagination'] = 'jQuery selector for element with pagination.';
$_lang['pdotools_prop_ajaxElemLink'] = 'jQuery selector for pagination links.';
$_lang['pdotools_prop_ajaxElemMore'] = 'jQuery selector for "load more" button in ajaxMode = button.';
$_lang['pdotools_prop_ajaxTplMore'] = 'Chunk for templating "more button" when ajaxMode = button. Must include a selector specified in "ajaxElemMore".';
$_lang['pdotools_prop_ajaxHistory'] = 'Save the page number in the url when working in ajax mode.';

$_lang['pdotools_prop_frontend_js'] = 'Link on javascript for loading by the snippet.';
$_lang['pdotools_prop_frontend_css'] = 'Link on css styles for loading by the snippet.';

$_lang['pdotools_prop_setMeta'] = 'Registration of meta tags with links to previous and next page.';

$_lang['pdotools_prop_title_limit'] = 'The limit of a query for parents of the resource.';
$_lang['pdotools_prop_title_cache'] = 'Enable cache of resource parents for the page title.';
$_lang['pdotools_prop_title_outputSeparator'] = 'String to separate elements in the page title.';
$_lang['pdotools_prop_registerJs'] = 'Insert to page the javascript variables for support &ajaxMode of snippet pdoPage.';
$_lang['pdotools_prop_tplPages'] = 'Template of pagination in the page title.';
$_lang['pdotools_prop_tplSearch'] = 'Template of search query in the page title.';
$_lang['pdotools_prop_minQuery'] = 'The minimum length of the search query to be displayed in the page title.';
$_lang['pdotools_prop_queryVarKey'] = 'The name of variable for the search query in the url.';
$_lang['pdotools_prop_titleField'] = 'Field of the current resource to be displayed in the page title.';
$_lang['pdotools_prop_strictMode'] = 'Strict mode. pdoPage do redirects when loading non-existent pages.';

$_lang['pdotools_prop_tplYear'] = 'Template for the year';
$_lang['pdotools_prop_tplMonth'] = 'Template for the month';
$_lang['pdotools_prop_tplDay'] = 'Template for the day';
$_lang['pdotools_prop_dateField'] = 'The field of resource for obtaining document date: createdon, publishedon, or editedon.';
$_lang['pdotools_prop_dateFormat'] = 'The date format for the function strftime()';

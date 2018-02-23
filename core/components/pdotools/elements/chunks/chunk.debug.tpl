{if $replace}
<html>
<head>
    <meta charset="{'modx_charset' | config}"/>
    <title>{'site_name' | config} - {$_modx->resource.pagetitle}</title>
</head>
<body>
{/if}

<link rel="stylesheet" type="text/css" href="{'assets_url' | config}components/pdotools/css/debug.css"/>
<div id="pdotools-debug">
    <table class="debug">
        <thead>
        <tr>
            <th>#</th>
            <th>Tag</th>
            <th>Queries</th>
            <th>Queries time, s</th>
            <th>Parse Time, s</th>
        </tr>
        </thead>
        {foreach $entries as $i => $entry}
            {if $top && $i >= $top}
                {break}
            {/if}
            <tr>
                <td>{$entry.idx}</td>
                <td class="tag">{$entry.tag | esc}</td>
                <td>{$entry.queries}</td>
                <td>{$entry.queries_time}</td>
                <td>{$entry.parse_time}</td>
            </tr>
        {/foreach}
    </table>

    <table class="info">
        <tr>
            <th>Total parse time</th>
            <th>{$total_parse_time} s</th>
        </tr>
        <tr>
            <th>Total queries</th>
            <td>{$total_queries}</td>
        </tr>
        <tr>
            <th>Total queries time</th>
            <td>{$total_queries_time} s</td>
        </tr>
    </table>
    <table class="info">
        <tr>
            <th>Memory peak usage</th>
            <td>{$memory_peak} Mb</td>
        </tr>
        <tr>
            <th>MODX version</th>
            <td>{$modx_version}</td>
        </tr>
        <tr>
            <th>PHP version</th>
            <td>{$php_version}</td>
        </tr>
        <tr>
            <th>Database version</th>
            <td>{$database_type} {$database_version}</td>
        </tr>
        <tr>
            <th>From cache</th>
            <td>{$from_cache}</td>
        </tr>
    </table>
</div>

{if $replace}
</body>
</html>
{/if}
<?php
/**
 * Properties German Lexicon Entries for pdoTools
 *
 * @package pdotools
 * @subpackage lexicon
 * @language de
 *
 * pdoTools translated to German by Jan-Christoph Ihrens (enigmatic_user, enigma@lunamail.de)
 */
$_lang['pdotools_prop_context'] = 'Gibt an, in welchem Kontext gesucht werden soll.';
$_lang['pdotools_prop_field_context'] = 'Kontext der Ressource zum Ermitteln ihrer Elternelemente. Wird bei Verwendung der Eigenschaften "top" und "topLevel" benötigt.';
$_lang['pdotools_prop_depth'] = 'Integer-Wert, der angibt, bis zu welcher Tiefe (im Ressourcenbaum) nach Ressourcen gesucht werden soll, ausgehend von jedem der angegebenen Elternelemente. Die erste Ebene von Ressourcen unterhalb des Elternelements hat die Tiefe 1.';
$_lang['pdotools_prop_fastMode'] = 'Schnelle Chunk-Verarbeitung. Wenn diese Einstellung auf "Ja" gesetzt wird, wird der MODX-Parser nicht verwendet und unverarbeitete Tags werden aus dem Ergebnis entfernt.';
$_lang['pdotools_prop_first'] = 'Geben Sie den idx-Wert an, der die erste Ressource repräsentiert (siehe Eigenschaften "idx" und "tplFirst").';
$_lang['pdotools_prop_hideContainers'] = 'Wenn diese Einstellung auf "Ja" gesetzt wird, werden keine Ressourcen angezeigt, die als Container gekennzeichnet sind (Ressourcen-Feld "isfolder").';
$_lang['pdotools_prop_idx'] = 'Sie können den Startwert für idx vorgeben; dies ist ein Wert, der mit jeder verarbeiteten Ressource um 1 erhöht wird.';
$_lang['pdotools_prop_includeContent'] = 'Gibt an, ob die Inhalte der Ressourcen mit zurückgegeben werden sollen.';

$_lang['pdotools_prop_includeTVs'] = 'Eine optionale kommaseparierte Liste von Template-Variablen, die enthalten sein sollen.';
$_lang['pdotools_prop_prepareTVs'] = 'Kommaseparierte Liste von Template-Variablen, die vorbereitet werden sollen (weil sie von Medienquellen abhängig sind). Standardmäßig ist diese Einstellung auf "1" gesetzt, was bedeutet, dass alle in "includeTVs" aufgeführten Template-Variablen vorbereitet werden.';
$_lang['pdotools_prop_processTVs'] = 'Kommaseparierte Liste von Template-Variablen, die verarbeitet werden sollen. Wenn Sie diese Einstellung auf "1" setzen, werden alle Template-Vaiablen in "includeTVs" verarbeitet. Standardmäßig ist dieses Feld leer.';
$_lang['pdotools_prop_tvFilters'] = 'Liste von Template-Variablen-Werten, nach denen die Ressourcen gefiltert werden. Unterstützt zwei Trennsymbole und zwei Suchformate. Das erste Trennsymbol || repräsentiert ein logisches ODER und den primären Gruppierungs-Mechanismus. Innerhalb jeder Gruppe können Sie eine kommaseparierte Liste von Werten angeben (das Komma ist das zweite Trennsymbol und repräsentiert ein logisches UND). Diese Werte können entweder durch Angabe des Namens einer bestimmten Template-Variablen zugeordnet werden, z.B. myTV==Wert, oder es wird nur der Wert angegeben, was bedeutet, dass der Wert in allen der Ressource zugeordneten Template-Variablen gesucht wird. Ein Beispiel wäre &tvFilters=`filter2==one,filter1==bar%||filter1==foo`.<br />HINWEIS: Beim Filtern nach Werten wird eine Abfrage mit LIKE verwendet, und das Prozentzeichen (%) gilt als Platzhalter.<br />NOCH EIN HINWEIS: Hierbei wird nur auf die "rohen" Werte der jeweiligen Ressource geschaut, d.h. es muss ein Wert speziell für die Ressource gesetzt sein, und er wird nicht weiter ausgewertet.';
$_lang['pdotools_prop_tvFiltersAndDelimiter'] = 'Die Zeichenkette, die in "tvFilters" verwendet wird, um zwei logische Ausdrücke, die durch ein logisches UND verbunden sind, zu trennen (oder anders ausgedrückt: Die hier eingegebene Zeichenkette ersetzt das UND). Der Standardwert ist ",".';
$_lang['pdotools_prop_tvFiltersOrDelimiter'] = 'Die Zeichenkette, die in "tvFilters" verwendet wird, um zwei logische Ausdrücke, die durch ein logisches ODER verbunden sind, zu trennen (oder anders ausgedrückt: Die hier eingegebene Zeichenkette ersetzt das ODER). Der Standardwert ist "||".';

$_lang['pdotools_prop_last'] = 'Geben Sie den idx-Wert an, der die letzte Ressource repräsentiert (siehe Eigenschaften "idx" und "tplLast"). Standardmäßig ist dies die Anzahl der verarbeiteten Ressourcen + der erste idx-Wert - 1.';
$_lang['pdotools_prop_neighbors_limit'] = 'Die Anzahl der benachbarten Dokumente zur Linken und Rechten. Die Standardeinstellung ist 1.';
$_lang['pdotools_prop_limit'] = 'Begrenzt die Anzahl der zurückgegebenen Ressourcen. Geben Sie "0" ein, wenn die Anzahl nicht begrenzt werden soll.';
$_lang['pdotools_prop_offset'] = 'Gibt an, wie viele der bei der Suche gefundenen Ressourcen übersprungen werden sollen. Der Standardwert ist "0".';
$_lang['pdotools_prop_outputSeparator'] = 'Eine optionale Zeichenkette, mittels derer die Ausgaben der einzelnen Template-Chunks voneinander getrennt werden.';
$_lang['pdotools_prop_parents'] = 'Kommaseparierte Liste von IDs, die als Elternelemente (Ausgangspunkte für die Suche) dienen. Geben Sie "0" ein, um Elternelemente zu ignorieren, wenn Sie mittels der Eigenschaft "resources" einzelne Ressourcen angeben, die enthalten sein sollen. Wenn Sie einer ID eines Elternelements ein Minuszeichen voranstellen, so wird dieses samt aller Kindelemente von der Suche ausgeschlossen.';
$_lang['pdotools_prop_resources'] = 'Kommaseparierte Liste von IDs von Ressourcen, die im Ergebnis enthalten sein sollen. Stellen Sie einer ID ein Minuszeichen voran, um die zugehörige Ressource von der Suche auszuschließen.';
$_lang['pdotools_prop_templates'] = 'Kommaseparierte Liste von Templates, die zur Filterung der Ergebnisse verwendet wird. Stellen Sie einer Template-ID ein Minuszeichen voran, um die Ressourcen, die dieses Template verwenden, von der Suche auszuschließen.';
$_lang['pdotools_prop_from'] = 'ID der Ressource, von der ausgehend die Breadcrumb-Navigation erstellt wird. Normalerweise ist dies der Ursprung der Site (im Ressourcen-Baum), z.B. "0".';
$_lang['pdotools_prop_to'] = 'ID der Ressource, für die die Breadcrumb-Navigation erstellt wird. Standardmäßig ist dies die ID der aktuellen Ressource.';
$_lang['pdotools_prop_users'] = 'Kommaseparierte Liste von Benutzern, deren Daten ausgegeben werden sollen. Sie können sowohl Benutzernamen als auch IDs verwenden. Wenn ein Wert mit einem Minuszeichen beginnt, wird dieser Benutzer von der Abfrage ausgeschlossen.';
$_lang['pdotools_prop_groups'] = 'Kommaseparierte Liste von Benutzergruppen. Sie können sowohl Namen als auch IDs verwenden. Wenn ein Wert mit einem Minuszeichen beginnt, so werden die dieser Benutzergruppe zugeordneten Benutzer von der Suche ausgeschlossen.';
$_lang['pdotools_prop_roles'] = 'Kommaseparierte Liste von Benutzer-Rollen. Sie können sowohl Namen als auch IDs verwenden. Wenn ein Wert mit einem Minuszeichen beginnt, so werden die dieser Rolle zugeordneten Benutzer von der Suche ausgeschlossen.';
$_lang['pdotools_prop_exclude'] = 'Kommaseparierte Liste von Ressourcen-IDs, die von der Abfrage ausgeschlossen werden sollen.';
$_lang['pdotools_prop_returnIds'] = 'Wenn diese Einstellung auf "Ja" gesetzt wird, gibt das Snippet nur eine kommaseparierte Liste von Ressourcen-IDs zurück anstatt der kompletten Ergebnisse.';
$_lang['pdotools_prop_showBlocked'] = 'Wenn diese Einstellung auf "Ja" gesetzt wird, werden auch geblockte Benutzer angezeigt.';
$_lang['pdotools_prop_showInactive'] = 'Wenn diese Einstellung auf "Ja" gesetzt wird, werden auch deaktivierte Benutzer angezeigt.';
$_lang['pdotools_prop_showDeleted'] = 'Wenn diese Einstellung auf "Ja" gesetzt wird, werden auch als gelöscht markierte Ressourcen angezeigt.';
$_lang['pdotools_prop_showHidden'] = 'Wenn diese Einstellung auf "Ja" gesetzt wird, werden auch versteckte Ressourcen angezeigt.';
$_lang['pdotools_prop_showLog'] = 'Wenn diese Einstellung auf "Ja" gesetzt wird, fügt das Snippet einen detaillierten Bericht über den Verlauf der Abfrage hinzu (für Entwickler).';
$_lang['pdotools_prop_showUnpublished'] = 'Wenn diese Einstellung auf "Ja" gesetzt wird, werden auch unveröffentlichte Ressourcen angezeigt.';
$_lang['pdotools_prop_showAtHome'] = 'Breadcrums-Navigation auf der Startseite der Site anzeigen.';
$_lang['pdotools_prop_showHome'] = 'Einen Link zur Startseite der Site am Anfang der Breadcrumbs-Navigation anzeigen.';
$_lang['pdotools_prop_showCurrent'] = 'Aktuelles Dokument in der Navigation anzeigen.';
$_lang['pdotools_prop_hideSingle'] = 'Ergebnis nicht anzeigen, wenn es nur ein einziges Element enthält.';
$_lang['pdotools_prop_hideUnsearchable'] = 'Keine Ressourcen anzeigen, die nicht durchsuchbar sind.';

$_lang['pdotools_prop_sortby'] = 'Ein beliebiges Ressourcen-Feld (einschließlich Template-Variablen, wenn diese über die Eigenschaft "includeTVs" einbezogen wurden), nach dem sortiert werden soll. Einige Felder, nach denen üblicherweise sortiert wird, sind publishedon, menuindex, pagetitle etc.; eine Liste aller Ressourcen-Felder finden Sie in der MODX-Dokumentation. Geben Sie nur die Namen der Felder ein, keine MODX-Tags. Bitte beachten Sie, dass bei Verwendung von Feldern wie template, publishedby und ähnlichen für die Sortierung nach den "rohen" Werten sortiert wird, also nach Template- oder Benutzer-IDs und NICHT den zugehörigen Namen. Sie können auch eine zufällige Sortierung wählen, indem Sie "RAND()" eingeben.';
$_lang['pdotools_prop_sortbyTV'] = 'Sortieren nach dem TV. Wenn es nicht angegeben ist, in &includeTVs, es wird uncluded automatisch.';
$_lang['pdotools_prop_sortbyTVType'] = 'Sortieren nach TV. Die Optionen sind: string, integer, decimal, und "datetime". Wenn leer, dann die TV werden sortiert, je nach type: text, Zahl oder Datum.';
$_lang['pdotools_prop_sortdir'] = 'Um die Sortierung: absteigend oder aufsteigend';
$_lang['pdotools_prop_sortdirTV'] = 'Sortieren Richtung TV: aufsteigend bzw. absteigend sortiert. Wenn nicht angegeben, es wird gleich dem parameter &sortdir.';
$_lang['pdotools_prop_toPlaceholder'] = 'Wenn hier etwas eingegeben wird, wird das Ergebnis an den hier angegebenen Platzhalter übergeben, anstatt direkt ausgegeben zu werden.';
$_lang['pdotools_prop_toSeparatePlaceholders'] = 'Wenn hier etwas eingegeben wird, wird JEDES Ergebnis einem eigenen Platzhalter zugeordnet, der die hier eingegebene Bezeichnung hat, gefolgt von einer aufsteigenden Nummer (beginnend bei 0).';
$_lang['pdotools_prop_totalVar'] = 'Der Schlüssel (bzw. Name) eines Platzhalters, der die Gesamtzahl der Ressourcen enthält, die ausgegeben würden, wenn der Wert der Eigenschaft "limit" nicht berücksichtigt würde.';  // getResources???

$_lang['pdotools_prop_tpl'] = 'Name eines Chunks, der als Ressourcen-Template dient. Wird kein Chunk angegeben, so werden die Eigenschaften der im Suchergebnis enthaltenen Ressourcen direkt ausgegeben (in Array-Notation).';
$_lang['pdotools_prop_tplFirst'] = 'Name eines Chunks, der als Ressourcen-Template für die erste Ressource dient (siehe Eigenschaft "first").';
$_lang['pdotools_prop_tplLast'] = 'Name eines Chunks, der als Ressourcen-Template für die letzte Ressource dient (siehe Eigenschaft "last").';
$_lang['pdotools_prop_tplOdd'] = 'Name eines Chunks, der als Ressourcen-Template für Ressourcen mit einem ungeraden idx-Wert dient (siehe Eigenschaft "idx").';
$_lang['pdotools_prop_tplWrapper'] = 'Name eines Chunks, der als äußeres Template für die Ausgabe dient (und diese quasi umschließt). Dies funktioniert nicht zusammen mit "toSeparatePlaceholders" (dieser Hinweis gilt natürlich nur für die Snippets, die die Eigenschaft "toSeparatePlaceholders" verwenden).';
$_lang['pdotools_prop_neighbors_tplWrapper'] = 'Name eines Chunks, der als äußeres Template für die Ausgabe dient (und diese quasi umschließt). Dies funktioniert nicht zusammen mit "toSeparatePlaceholders".';
$_lang['pdotools_prop_tvPrefix'] = 'Das Präfix für Template-Variablen-Eigenschaften.';
$_lang['pdotools_prop_where'] = 'Ein Ausdruck im JSON-Stil mit Kriterien, aus denen zusätzliche WHERE-Bedingungen erstellt werden können.';
$_lang['pdotools_prop_wrapIfEmpty'] = 'Wenn diese Einstellung auf "Ja" gesetzt wird, wird der Inhalt des in "tplWrapper" angegebenen Chunks auch dann ausgegeben, wenn die Ausgabe leer ist.';
$_lang['pdotools_prop_tplOperator'] = 'Ein optionaler Operator der für den Vergleich der "tplCondition" mit den in "conditionalTpls" definierten Operanden verwendet wird. Standardwert ist == (ist gleich).';
$_lang['pdotools_prop_tplCondition'] = 'Eine Bedingung zum Vergleich mit der Eigenschaft conditionalTpls, um Ressourcen verschiedenen Templates zuzuordnen, basierend auf benutzerdefinierten Bedingungen.';
$_lang['pdotools_prop_conditionalTpls'] = 'Eine JSON-Map von Bedingungs-Operanden und Templates zum Vergleich mit der tplCondition-Eigenschaft unter Verwendung des angegebenen tplOperator.';
$_lang['pdotools_prop_tplCurrent'] = 'Сhunk für das aktuelle Dokument in der Navigation.';
$_lang['pdotools_prop_tplHome'] = 'Сhunk für den Link zur Startseite der Site.';
$_lang['pdotools_prop_tplMax'] = 'Сhunk, der am Anfang der Breadcrumb-Navigation eingefügt wird, wenn es mehr Elemente gibt, als durch "limit" zugelassen werden.';
$_lang['pdotools_prop_tplPrev'] = 'Сhunk mit einem Link zum vorhergehenden Dokument.';
$_lang['pdotools_prop_tplUp'] = 'Сhunk mit einem Link zum übergeordneten Dokument (Elternelement).';
$_lang['pdotools_prop_tplNext'] = 'Сhunk mit einem Link zum nächsten Dokument.';

$_lang['pdotools_prop_select'] = 'Kommaseparierte Liste von Tabellenspalten, die aus der Datenbank ausgelesen werden sollen (schränkt die Abfrage auf die angegebenen Felder ein). Sie können einen JSON-String mit einem Array angeben, z.B. {"modResource":"id,pagetitle,content"}.';
$_lang['pdotools_prop_loadModels'] = 'Kommaseparierte Liste von zusätzlichen Komponenten, die für die Abfrage benötigt werden. Beispiel: "&loadModels=`ms2gallery,msearch2`".';
$_lang['pdotools_prop_direction'] = 'Textrichtung oder Breadcrumbs-Sortierung: von links nach rechts (ltr) oder von rechts nach links (rtl), z.B. für arabischen Text.';
$_lang['pdotools_prop_id'] = 'ID der Ressource.';
$_lang['pdotools_prop_field'] = 'Feld der Ressource.';
$_lang['pdotools_prop_top'] = 'Wählt das Elternelement der angegebenen "id" auf der Ebene "top".';  // von der Ressource aus gesehen nach oben?
$_lang['pdotools_prop_topLevel'] = 'Wählt das Elternelement der angegebenen "id" auf der Ebene "topLevel" vom Ursprung des Kontexts aus gesehen (im Ressourcen-Baum).';

$_lang['pdotools_prop_forceXML'] = 'Erzwingt die Ausgabe der Seite im XML-Format.';
$_lang['pdotools_prop_sitemapSchema'] = 'Schema der Sitemap.';
$_lang['pdotools_prop_scheme'] = 'Schema der Generierung der links: "uri" für die Ersetzung der Dokument-uri (sehr schnell) oder ein parameter für modX::makeUrl().';

$_lang['pdotools_prop_field_default'] = 'Geben Sie ein zusätzliches Ressourcen-Feld an, dessen Inhalt zurückgegeben wird, wenn das in "field" angegebene Feld der Ressource leer ist.';
$_lang['pdotools_prop_field_output'] = 'Der hier eingegebene Text wird zurückgegeben, wenn die in "default" und "field" definierten Felder der Ressource leer sind.';

$_lang['pdotools_prop_cache'] = 'Ergebnisse der Snippet-Ausführung cachen.';
$_lang['pdotools_prop_cachePageKey'] = 'Der Name des Schlüssel-Caches.';
$_lang['pdotools_prop_cacheTime'] = 'Zeit, bis der Cache ungültig wird, in Sekunden.';
$_lang['pdotools_prop_cacheKey'] = 'Cache-key. Gespeichert in "core/cache/default/yourkey"';
$_lang['pdotools_prop_cacheAnonymous'] = 'Aktivieren Sie das Caching nur für nicht autorisierte Besucher.';
$_lang['pdotools_prop_element'] = 'Der Name des auszuführenden Snippets.';
$_lang['pdotools_prop_maxLimit'] = 'Das maximale Limit der Abfrage. Hat Vorrang vor dem Limit, das der Benutzer in einer URL angibt.';
$_lang['pdotools_prop_page'] = 'Die Nummer der Seite für die Ausgabe. Hat Vorrang vor der Nummer, die der Benutzer in der URL angegeben hat.';
$_lang['pdotools_prop_pageLimit'] = 'Anzahl der Links zu anderen Seiten. Ist dieser Wert größer oder gleich 7, wird der erweiterte Modus eingeschaltet.';
$_lang['pdotools_prop_pageNavVar'] = 'Name des Platzhalters für die Ausgabe der Paginierung (Links zu anderen Seiten).';
$_lang['pdotools_prop_pageCountVar'] = 'Name des Platzhalters für die Ausgabe der Anzahl der Seiten.';
$_lang['pdotools_prop_pageLinkScheme'] = 'Schema der generation der link zur Seite. Sie können die Platzhalter [[+pageVarKey]] und [[+page]]';
$_lang['pdotools_prop_pageVarKey'] = 'Der Name des Parameters, der in der URL die Seitennummer angibt.';
$_lang['pdotools_prop_plPrefix'] = 'Präfix für Platzhalter; Standard ist "wf.".';

$_lang['pdotools_prop_tplPage'] = 'Chunk für einen normalen Link zu einer Seite.';
$_lang['pdotools_prop_tplPageActive'] = 'Chunk für den Link zur aktuellen Seite.';
$_lang['pdotools_prop_tplPageFirst'] = 'Chunk für den Link zur ersten Seite.';
$_lang['pdotools_prop_tplPagePrev'] = 'Chunk für den Link zur vorhergehenden Seite.';
$_lang['pdotools_prop_tplPageLast'] = 'Chunk für den Link zur letzten Seite.';
$_lang['pdotools_prop_tplPageNext'] = 'Chunk für den Link zur nächsten Seite.';
$_lang['pdotools_prop_tplPageFirstEmpty'] = 'Chunk, der ausgegeben wird, wenn es keinen Link zur ersten Seite gibt (z.B. weil die aktuelle Seite die erste Seite ist).';
$_lang['pdotools_prop_tplPagePrevEmpty'] = 'Chunk, der ausgegeben wird, wenn es keinen Link zur vorhergehenden Seite gibt (z.B. weil die aktuelle Seite die erste Seite ist).';
$_lang['pdotools_prop_tplPageLastEmpty'] = 'Chunk, der ausgegeben wird, wenn es keinen Link zur letzten Seite gibt (z.B. weil die aktuelle Seite die letzte Seite ist).';
$_lang['pdotools_prop_tplPageNextEmpty'] = 'Chunk, der ausgegeben wird, wenn es keinen Link zur nächsten Seite gibt (z.B. weil die aktuelle Seite die letzte Seite ist).';
$_lang['pdotools_prop_tplPageSkip'] = 'Chunk, der fehlende Paginierungs-Links repräsentiert (z.B. durch Auslassungspunkte). Wird im erweiterten Modus (wenn die Eigenschaft "pageLimit" auf einen Wert >= 7 gesetzt wurde) verwendet.';
$_lang['pdotools_prop_tplPageWrapper'] = 'Chunk, der den gesamten Paginierungs-Block definiert. Es können die Platzhalter [[+first]], [[+prev]], [[+pages]], [[+next]] und [[+last]] verwendet werden.';

$_lang['pdotools_prop_previewUnpublished'] = 'Optional. Wenn diese Einstellung auf "Ja" gesetzt wird, Sie in den Manager eingeloggt sind und die Berechtigung "view_unpublished" besitzen, so können Sie in der Vorschau Ihrer Site auch unveröffentlichte Ressourcen im Menü sehen.';
$_lang['pdotools_prop_checkPermissions'] = 'Kommaseparierte Liste von beim Erstellen des Menüs zu prüfenden Berechtigungen.';
$_lang['pdotools_prop_displayStart'] = 'Dokument wie von der startId im Menu angegeben anzeigen.';  // What is startId?
$_lang['pdotools_prop_hideSubMenus'] = 'Wenn diese Eigenschaft auf "Ja" gesetzt wird, werden alle nicht-aktiven Untermenüs aus der Snippet-Ausgabe entfernt. Dieser Parameter wirkt sich nur aus, wenn mehrere Ebenen angezeigt werden.';
$_lang['pdotools_prop_useWeblinkUrl'] = 'Wenn WebLinks in der Ausgabe verwendet werden und diese Einstellung auf "Ja" gesetzt wird, gibt das Snippet den Link aus, der im Weblink definiert ist, anstatt des normalen MODX-Links. Um die Standard-Anzeige von Weblinks zu nutzen (wie bei jeder anderen Ressource), setzen Sie diese Einstellung auf "Nein".';
$_lang['pdotools_prop_rowIdPrefix'] = 'Wenn hier etwas eingegeben wird, ersetzt das Skript den Platzhalter "id" durch eine eindeutige ID, die aus dem hier angegebenen Präfix, gefolgt von der Ressourcen-ID, besteht.';
$_lang['pdotools_prop_level'] = 'Anzahl der Ebenen, die im Menü angezeigt werden. Wenn Sie hier "0" eingeben, werden alle Ebenen angezeigt.';
$_lang['pdotools_prop_hereId'] = 'Optional. Wenn hier eine Ressourcen-ID eingegeben wird, wird die zugehörige Ressource im Menü als die aktuelle Ressource angezeigt. Standardmäßig wird die jeweils aktive Ressource im Menü als die aktuelle angezeigt.';

$_lang['pdotools_prop_webLinkClass'] = 'CSS-Klasse für Weblink-Elemente.';
$_lang['pdotools_prop_firstClass'] = 'CSS-Klasse für das erste Element einer Menü-Ebene.';
$_lang['pdotools_prop_hereClass'] = 'CSS-Klasse für alle Elemente entlang des Pfades bis zum aktuellen Element.';
$_lang['pdotools_prop_innerClass'] = 'CSS-Klasse für das innere Template.';
$_lang['pdotools_prop_lastClass'] = 'CSS-Klasse für das letzte Element einer Menü-Ebene.';
$_lang['pdotools_prop_levelClass'] = 'CSS-Klasse für die Angabe der jeweiligen Menü-Ebene. Die Nummer der jeweiligen Ebene wird an den hier eingegebenen Klassen-Namen angehängt (z.B. level1, level2, level3 etc., wenn Sie hier "level" eingeben).';
$_lang['pdotools_prop_outerClass'] = 'CSS-Klasse für das äußere Template.';
$_lang['pdotools_prop_parentClass'] = 'CSS-Klasse für Menüpunkte, die Container sind und Kindelemente besitzen.';
$_lang['pdotools_prop_rowClass'] = 'CSS-Klasse für jede Ausgabezeile (jeden Menüpunkt).';
$_lang['pdotools_prop_selfClass'] = 'CSS-Klasse für das aktuelle Element.';

$_lang['pdotools_prop_tplCategoryFolder'] = 'Name des Chunks, der das Template für den äußersten Container enthält; wenn hier nichts eingegeben wird, wird automatisch "&lt;ul&gt;[[+wf.wrapper]]&lt;/ul&gt;" verwendet.';
$_lang['pdotools_prop_tplHere'] = 'Name des Chunks, der das Template für die aktuelle Ressource enthält, wenn diese ein Container ist und Kindelemente besitzt. Denken Sie an den Platzhalter [[+wf.wrapper]], um die untergeordneten Dokumente (Kindelemente) auszugeben.';
$_lang['pdotools_prop_tplInner'] = 'Name des Chunks, der das Template für alle Untermenüs enthält. Wenn diese Eigenschaft nicht definiert ist, wird stattdessen "outerTpl" verwendet.';
$_lang['pdotools_prop_tplInnerHere'] = 'Name des Chunks, der das Template für die aktuelle Ressource enthält, wenn sie sich in einem Unterordner befindet.';
$_lang['pdotools_prop_tplInnerRow'] = 'Name des Chunks, der das Template für die Ressourcen enthält, die sich in einem Unterordner befinden.';  // Really the current one??? See above!
$_lang['pdotools_prop_tplOuter'] = 'Name des Chunks, der das Template für den äußersten Container enthält; wenn hier nichts eingegeben wird, wird automatisch "&lt;ul&gt;[[+wrapper]]&lt;/ul&gt;" verwendet.';  // Same as pdotools_prop_tplCategoryFolder apart from the default HTML
$_lang['pdotools_prop_tplParentRow'] = 'Name des Chunks, der das Template für Ressourcen enthält, die Container sind und Kindelemente besitzen. Denken Sie an den Platzhalter [[+wrapper]], um die untergeordneten Dokumente (Kindelemente) auszugeben.';
$_lang['pdotools_prop_tplParentRowActive'] = 'Name des Chunks, der das Template enthält für Elemente, die Container sind, Kindelemente besitzen und gerade im Ressourcenbaum aktiv sind.';
$_lang['pdotools_prop_tplParentRowHere'] = 'Name des Chunks, der das Template für die aktuelle Ressource enthält, wenn diese ein Container ist und Kindelemente besitzt. Denken Sie an den Platzhalter [[+wf.wrapper]], um die untergeordneten Dokumente (Kindelemente) auszugeben.';
$_lang['pdotools_prop_tplStart'] = 'Name des Chunks, der das Template für das Startelement enthält, falls dies mittels des &displayStart-Parameters aktiviert wurde. Hinweis: Das Standard-Template zeigt das Startelement an, verlinkt es aber nicht. Wenn Sie keinen Link benötigen, kann dem Standard-Template eine Klasse zugeordnet werden, indem man den Parameter &firstClass=`className` verwendet.';

$_lang['pdotools_prop_ultimate'] = 'Die Parameter &top und &topLevel funktionieren wie im Snippet UltimateParent.';
$_lang['pdotools_prop_loop'] = 'Schleife links. Wenn es keinen link zu der nächsten Seite, den link auf der ersten Seite und Umgekehrt.';

$_lang['pdotools_prop_countChildren'] = 'Bringen die genaue Anzahl der aktiven Nachkommen des Dokumentes in плейсхолдер [[+children]].';

$_lang['pdotools_prop_ajax'] = 'Aktivieren Sie die Unterstützung von ajax-Anfragen.';
$_lang['pdotools_prop_ajaxMode'] = 'Ajax Paginierung aus der box. Erhältlich in 3 Modi: "Standard", "button" und "Blättern".';
$_lang['pdotools_prop_ajaxElemWrapper'] = 'jQuery selector für die wrapper-element mit den Ergebnissen und Seitenzählung.';
$_lang['pdotools_prop_ajaxElemRows'] = 'jQuery selector für das element mit den Ergebnissen.';
$_lang['pdotools_prop_ajaxElemPagination'] = 'jQuery selector für element mit Paginierung.';
$_lang['pdotools_prop_ajaxElemLink'] = 'jQuery selector für die Paginierung links.';
$_lang['pdotools_prop_ajaxElemMore'] = 'jQuery selector für "laden" - button in ajaxMode = button.';
$_lang['pdotools_prop_ajaxTplMore'] = 'Stück für templating "mehr Schaltfläche" wenn ajaxMode = button. Muss eine Selektor angegeben in "ajaxElemMore".';
$_lang['pdotools_prop_ajaxHistory'] = 'Speichern Sie die Seite Nummer in der url, wenn Sie arbeiten in ajax-Modus.';

$_lang['pdotools_prop_frontend_js'] = 'Link auf javascript für die Belastung durch das snippet.';
$_lang['pdotools_prop_frontend_css'] = 'Link auf css-Stile für das laden der snippet.';

$_lang['pdotools_prop_setMeta'] = 'Anmeldung von meta-tags mit links zu den vorherigen und nächsten Seite.';

$_lang['pdotools_prop_title_limit'] = 'Die Grenze von einer Abfrage für die Eltern der Ressource.';
$_lang['pdotools_prop_title_cache'] = 'Aktivieren cache Ressource Eltern für den Titel der Seite.';
$_lang['pdotools_prop_title_outputSeparator'] = 'String separate Elemente in den Titel der Seite.';
$_lang['pdotools_prop_registerJs'] = 'Insert zur Seite, die javascript-Variablen für die Unterstützung &ajaxMode von snippet pdoPage.';
$_lang['pdotools_prop_tplPages'] = 'Template der Paginierung in den Titel der Seite.';
$_lang['pdotools_prop_tplSearch'] = 'Template für die Suche im Titel der Seite.';
$_lang['pdotools_prop_minQuery'] = 'Die minimale Länge der Suchanfrage angezeigt in den Titel der Seite.';
$_lang['pdotools_prop_queryVarKey'] = 'Der name der variable für den Suchbegriff in der url.';
$_lang['pdotools_prop_titleField'] = 'Feld der aktuellen Ressource angezeigt in den Titel der Seite.';
$_lang['pdotools_prop_strictMode'] = 'Strict-Modus. pdoPage tun leitet beim laden nicht existierenden Seiten.';

$_lang['pdotools_prop_tplYear'] = 'Template für das Jahr';
$_lang['pdotools_prop_tplMonth'] = 'Template für den Monat';
$_lang['pdotools_prop_tplDay'] = 'Vorlage für das Tag';
$_lang['pdotools_prop_dateField'] = 'Das Feld der Ressource für den Erhalt von Dokument-Datum: createdon, publishedon, oder editedon.';
$_lang['pdotools_prop_dateFormat'] = 'Datum-format für die Funktion strftime()';
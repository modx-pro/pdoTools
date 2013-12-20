<?php
/**
 * Build the setup options form.
 */
$exists = false;
$output = null;
switch ($options[xPDOTransport::PACKAGE_ACTION]) {
	case xPDOTransport::ACTION_INSTALL:

	case xPDOTransport::ACTION_UPGRADE:
		$exists = $modx->getCount('modSystemSetting', array('key' => 'parser_class', 'value' => 'pdoParser'));
		break;

	case xPDOTransport::ACTION_UNINSTALL: break;
}

if (!$exists) {
	if ($modx->getOption('manager_language') == 'ru') {
		$text = 'Вы можете включить <b>эксперементальный</b> парсер pdoTools, который работает немного быстрее оригинального и обрабатывает дополнительные плейсхолдеры <b>FastField</b>.
			<br/>Тогда вы сможете:
			<ul>
				<li>Выводить поля ресурсов: <em>[[#15.pagetitle]], [[#20.content]]</em></li>
				<li>Выводить ТВ параметры ресурсов: <em>[[#15.date]], [[#20.some_tv]]</em></li>
				<li>Выводить поля товаров miniShop2: <em>[[#21.price]], [[#22.article]]</em></li>
				<li>Выводить массивы ресурсов и товаров: <em>[[#12.properties.somefield]], [[#15.size.1]]</em></li>
				<li>Выводить глобальные массивы: <em>[[#POST.key]], [[#SESSION.another_key]]</em></li>
				<li>Распечатывать массивы для отладки: <em>[[#15.colors]], [[#GET]], [[#12.properties]]</em></li>
			</ul>
			<br/>
			<label id="pdoParser">
				<input type="checkbox" name="pdoParser" value="1" id="pdoParser" />
				Включить эксперементальный pdoParser
			</label>

			<small>Если что-то пойдёт не так - просто удалите системные настройки<br/><b>parser_class</b> и <b>parser_class_path</b>.</small>';
	}
	else {
		$text = 'You can enable <b>experimental</b> parser of pdoTools, that works a little faster than the original, and processes additional <b>FastField</b> placeholders.
			<br/>Then you will can:
			<ul>
				<li>Display resource fields: <em>[[#15.pagetitle]], [[#20.content]]</em></li>
				<li>Display TVs: <em>[[#15.date]], [[#20.some_tv]]</em></li>
				<li>Display fields of miniShop2 products: <em>[[#21.price]], [[#22.article]]</em></li>
				<li>Display array fields of resources and products: <em>[[#12.properties.somefield]], [[#15.size.1]]</em></li>
				<li>Display values from global arrays: <em>[[#POST.key]], [[#SESSION.another_key]]</em></li>
				<li>Print the entire array for debugging: <em>[[#15.colors]], [[#GET]], [[#12.properties]]</em></li>
			</ul>
			<br/>
			<label id="pdoParser">
				<input type="checkbox" name="pdoParser" value="1" id="pdoParser" />
				Enable experimental pdoParser
			</label>
			<small>If something goes wrong - just delete system settings<br/><b>parser_class</b> and <b>parser_class_path</b>.</small>';
	}

	$output = '
		<style>
			#setup_form_wrapper {font: normal 12px Arial;line-height:18px;}
			#setup_form_wrapper ul {margin-left: 5px; font-size: 10px; list-style: disc inside;}
			#setup_form_wrapper a {color: #08C;}
			#setup_form_wrapper small {font-size: 10px; color:#555; font-style:italic;}
			#setup_form_wrapper label {color: black; font-weight: bold;}
		</style>
		<div id="setup_form_wrapper">'.$text.'</div>
	';
}

return $output;
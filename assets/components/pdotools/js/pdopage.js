if (typeof(pdoPage) == 'undefined') {
	pdoPage = {callbacks: {}, keys: {}, settings: {}};
}

pdoPage.Reached = false;

pdoPage.initialize = function(config) {

	pdoPage.settings = $.extend({}, {
		wrapper: "#items",
		rows: "#items .row",
		pagination: "#items .pagination",
		link: "#items .pagination a",
		more: "#items .btn-more",
		moreTpl: "<button class=\"btn btn-default btn-more\">Laden</button>",
		mode: "scroll",
		pageVarKey: "page",
		pageLimit: 12,
		assetsUrl: "/assets/components/pdotools/"
	}, config);

	if (pdoPage.keys[pdoPage.settings['pageVarKey']] == undefined) {
		var tkey = pdoPage.settings['pageVarKey'];
		var tparams = pdoPage.Hash.get();
		var tpage = tparams[tkey] == undefined ? 1 : tparams[tkey];
		pdoPage.keys[tkey] = Number(tpage);
	}
	var $this = this;
	switch (pdoPage.settings['mode']) {
		case 'default':
			$(document).on('click', pdoPage.settings['link'], function(e) {
				e.preventDefault();
				var href = $(this).prop('href');
				var key = pdoPage.settings['pageVarKey'];
				var match = href.match(new RegExp(key + '=(\\d+)'));
				var page = !match ? 1 : match[1];
				if (pdoPage.keys[key] != page) {
					pdoPage.Hash.add(key, page);
					$this.loadPage(href);
				}
			});

			$(window).on('popstate', function(e) {
				if (e.originalEvent.state && e.originalEvent.state['pdoPage']) {
					$this.loadPage(e.originalEvent.state['pdoPage']);
				}
			});

			history.replaceState({pdoPage: window.location.href}, '');
			break;

		case 'scroll':
		case 'button':
			if (typeof(jQuery().sticky) == 'undefined') {
				$.getScript(pdoPage.settings['assetsUrl'] + 'js/lib/jquery.sticky.js', function() {
					pdoPage.initialize(pdoPage.settings);
				});
				return;
			}

			pdoPage.stickyPagination();
			var key = pdoPage.settings['pageVarKey'];

			if (pdoPage.settings['mode'] == 'button') {
				// Add more button
				$(pdoPage.settings['rows']).after(pdoPage.settings['moreTpl']);
				var has_results = false;
				$(pdoPage.settings['link']).each(function() {
					var href = $(this).prop('href');
					var match = href.match(new RegExp(key + '=(\\d+)'));
					var page = !match ? 1 : match[1];
					if (page > pdoPage.keys[key]) {
						has_results = true;
						return false;
					}
				});
				if (!has_results) {
					$(pdoPage.settings['more']).hide();
				}

				$(document).on('click', pdoPage.settings['more'], function(e) {
					e.preventDefault();
					pdoPage.addPage()
				});
			}
			else {
				// Scroll pagination
				var wrapper = $(pdoPage.settings['wrapper']);
				var $window = $(window);
				$window.on('scroll', function() {
					if (!pdoPage.Reached && $window.scrollTop() > wrapper.height() - $window.height()) {
						pdoPage.Reached = true;
						pdoPage.addPage();
					}
				});
			}

			break;
	}
};

pdoPage.addPage = function() {
	var key = pdoPage.settings['pageVarKey'];
	var params = pdoPage.Hash.get();
	var current = params[key] || 1;
	$(pdoPage.settings['link']).each(function() {
		var href = $(this).prop('href');
		var match = href.match(new RegExp(key + '=(\\d+)'));
		var page = !match ? 1 : Number(match[1]);
		if (page > current) {
			pdoPage.Hash.add(key, page);
			pdoPage.keys[key] = current;
			pdoPage.loadPage(href, 'append');
			return false;
		}
	});
};

pdoPage.loadPage = function(href, mode) {
	var wrapper = $(pdoPage.settings['wrapper']);
	var rows = $(pdoPage.settings['rows']);
	var pagination = $(pdoPage.settings['pagination']);
	var key = pdoPage.settings['pageVarKey'];
	var match = href.match(new RegExp(key + '=(\\d+)'));
	var page = !match ? 1 : Number(match[1]);
	if (!mode) {mode = 'replace';}

	if (pdoPage.keys[key] == page) {
		return;
	}
	if (pdoPage.callbacks['before'] && typeof(pdoPage.callbacks['before']) == 'function') {
		pdoPage.callbacks['before'].apply(this, [pdoPage.settings]);
	}
	else if (pdoPage.settings['mode'] != 'scroll') {
		wrapper.css({opacity: .3});
	}

	var params = pdoPage.Hash.get();
	for (var i in params) {
		if (params.hasOwnProperty(i) && pdoPage.keys[i] && i != key) {
			delete(params[i]);
		}
	}
	params[key] = pdoPage.keys[key] = page;
	var waitAnimation = $('<div class="wait-wrapper"><div class="wait"></div></div>');
	if (mode == 'append') {
		wrapper.find(rows).append(waitAnimation);
	} else {
		wrapper.find(rows).empty().append(waitAnimation);
	}
	$.get(document.location.pathname, params, function(response) {
		if (response) {
			wrapper.find(pagination).html(response['pagination']);
			if (mode == 'append') {
				wrapper.find(rows).append(response['output']);
				if (pdoPage.settings['mode'] == 'button') {
					if (response['pages'] == response['page']) {
						$(pdoPage.settings['more']).hide();
					}
					else {
						$(pdoPage.settings['more']).show();
					}
				}
				else if (pdoPage.settings['mode'] == 'scroll') {
					pdoPage.Reached = false;
				}
				waitAnimation.remove();
			}
			else {
				wrapper.find(rows).html(response['output']);
			}

			if (pdoPage.callbacks['after'] && typeof(pdoPage.callbacks['after']) == 'function') {
				pdoPage.callbacks['after'].apply(this, [pdoPage.settings, response)]);
			}
			else if (pdoPage.settings['mode'] != 'scroll') {
				wrapper.css({opacity: 1});
				if (pdoPage.settings['mode'] == 'default') {
					$('html, body').animate({scrollTop: wrapper.position().top - 50 || 0}, 0);
				}
			}
			pdoPage.updateTitle(response);
			$(document).trigger('pdopage_load', [pdoPage.settings, response]);
		}
	}, 'json');
};

pdoPage.stickyPagination = function() {
	var pagination = $(pdoPage.settings['pagination']);
	if (pagination.is(':visible')) {
		pagination.sticky({
			wrapperClassName: 'sticky-pagination',
			getWidthFrom: pdoPage.settings['pagination'],
			responsiveWidth: true
		});
		$(pdoPage.settings['wrapper']).trigger('scroll');
	}
};

pdoPage.updateTitle = function(response) {
	if (typeof(pdoTitle) == 'undefined') {
		return;
	}
	var $title = $('title');
	var separator = pdoTitle.separator || ' / ';
	var tpl = pdoTitle.tpl;

	var title = [];
	var items = $title.text().split(separator);
	var pcre = new RegExp('^' + tpl.split(' ')[0] + ' ');
	for (var i = 0; i < items.length; i++) {
		if (i === 1 && response.page && response.page > 1) {
			title.push(tpl.replace('{page}', response.page).replace('{pageCount}', response.pages));
		}
		if (!items[i].match(pcre)) {
			title.push(items[i]);
		}
	}
	$title.text(title.join(separator));
};

pdoPage.Hash = {
	get: function() {
		var vars = {}, hash, splitter, hashes;
		if (!this.oldbrowser()) {
			var pos = window.location.href.indexOf('?');
			hashes = (pos != -1) ? decodeURIComponent(window.location.href.substr(pos + 1)) : '';
			splitter = '&';
		}
		else {
			hashes = decodeURIComponent(window.location.hash.substr(1));
			splitter = '/';
		}

		if (hashes.length == 0) {return vars;}
		else {hashes = hashes.split(splitter);}

		for (var i in hashes) {
			if (hashes.hasOwnProperty(i)) {
				hash = hashes[i].split('=');
				if (typeof hash[1] == 'undefined') {
					vars['anchor'] = hash[0];
				}
				else {
					vars[hash[0]] = hash[1];
				}
			}
		}
		return vars;
	}

	,set: function(vars) {
		var hash = '';
		for (var i in vars) {
			if (vars.hasOwnProperty(i)) {
				if (typeof vars[i] == 'string') {
					hash += '&' + i + '=' + vars[i];
				} else {
					for (var j in vars[i]) {
						if (vars[i].hasOwnProperty(j)) {
							hash += '&' + i + '=' + vars[i][j];
						}
					}
				}
			}
		}

		if (!this.oldbrowser()) {
			if (hash.length != 0) {
				hash = '?' + hash.substr(1);
			}
			window.history.pushState({pdoPage: document.location.pathname + hash}, '', document.location.pathname + hash);
		}
		else {
			window.location.hash = hash.substr(1);
		}
	}

	,add: function(key, val) {
		var hash = this.get();
		hash[key] = val;
		this.set(hash);
	}

	,remove: function(key) {
		var hash = this.get();
		delete hash[key];
		this.set(hash);
	}

	,clear: function() {
		this.set({});
	}

	,oldbrowser: function() {
		return !(window.history && history.pushState);
	}
};

if (typeof(jQuery) == 'undefined') {
	console.log("You must load jQuery for using ajax mode in pdoPage.");
}

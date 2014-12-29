if (typeof(pdoPage) == 'undefined') {
	pdoPage = {callbacks: {}, keys: {}};
}

pdoPage.justLoaded = true;
pdoPage.Reached = false;

pdoPage.initialize = function(config) {
	if (typeof(pdoHash) == 'undefined') {
		$.getScript(config['assetsUrl'] + 'js/lib/hash.js', function() {
			pdoPage.initialize(config);
		});
		return;
	}

	if (pdoPage.keys[config['pageVarKey']] == undefined) {
		var tkey = config['pageVarKey'];
		var tparams = pdoHash.get();
		var tpage = tparams[tkey] == undefined ? 1 : tparams[tkey];
		pdoPage.keys[tkey] = Number(tpage);
	}
	var $this = this;
	switch (config['mode']) {
		case 'default':
			$(document).on('click', config['link'], function(e) {
				e.preventDefault();
				var href = $(this).prop('href');
				var key = config['pageVarKey'];
				var match = href.match(new RegExp(key + '=(\\d+)'));
				var page = !match ? 1 : match[1];
				if (pdoPage.keys[key] != page) {
					pdoHash.add(key, page);
					$this.loadPage(href, config);
					$this.justLoaded = false;
				}
			});

			$(window).on('popstate', function(e) {
				if (!$this.justLoaded && e.originalEvent.state && e.originalEvent.state['pdoPage']) {
					$this.loadPage(e.originalEvent.state['pdoPage'], config);
					$this.justLoaded = false;
				}
			});

			history.replaceState({pdoPage: window.location.href}, '');
			break;

		case 'scroll':
		case 'button':
			if (typeof(jQuery().sticky) == 'undefined') {
				$.getScript(config['assetsUrl'] + 'js/lib/jquery.sticky.js', function() {
					pdoPage.initialize(config);
				});
				return;
			}

			pdoPage.stickyPagination(config);
			var key = config['pageVarKey'];

			if (config['mode'] == 'button') {
				// Add more button
				$(config['link']).each(function() {
					var href = $(this).prop('href');
					var match = href.match(new RegExp(key + '=(\\d+)'));
					var page = !match ? 1 : match[1];
					if (page > pdoPage.keys[key]) {
						$(config['rows']).after(config['moreTpl']);
						return false;
					}
				});

				$(document).on('click', config['more'], function(e) {
					e.preventDefault();
					pdoPage.addPage(config)
				});
			}
			else {
				// Scroll pagination
				var wrapper = $(config['wrapper']);
				var $window = $(window);
				$window.on('scroll', function() {
					if (!pdoPage.Reached && $window.scrollTop() > wrapper.height() - $window.height()) {
						pdoPage.Reached = true;
						pdoPage.addPage(config);
					}

				});
			}

			break;
	}
};

pdoPage.addPage = function(config) {
	var key = config['pageVarKey'];
	$(config['link']).each(function() {
		var href = $(this).prop('href');
		var match = href.match(new RegExp(key + '=(\\d+)'));
		var page = !match ? 1 : match[1];
		if (page > pdoPage.keys[key]) {
			pdoHash.add(key, page);
			pdoPage.loadPage(href, config, 'append');
			return false;
		}
	});
};

pdoPage.loadPage = function(href, config, mode) {
	var wrapper = $(config['wrapper']);
	var rows = $(config['rows']);
	var pagination = $(config['pagination']);
	var key = config['pageVarKey'];
	var match = href.match(new RegExp(key + '=(\\d+)'));
	var page = !match ? 1 : match[1];
	if (!mode) {mode = 'replace';}

	if (pdoPage.keys[key] == page) {
		return;
	}
	if (pdoPage.callbacks['before'] && typeof(pdoPage.callbacks['before']) == 'function') {
		pdoPage.callbacks['before'].apply(this, [config]);
	}
	else if (config['mode'] != 'scroll') {
		wrapper.css({opacity: .3});
	}

	var params = pdoHash.get();
	for (var i in params) {
		if (params.hasOwnProperty(i) && pdoPage.keys[i] && i != key) {
			delete(params[i]);
		}
	}
	params[key] = pdoPage.keys[key] = Number(page);
	$.get(document.location.pathname, params, function(response) {
		if (response && response['total']) {
			wrapper.find(pagination).html(response['pagination']);
			if (mode == 'append') {
				wrapper.find(rows).append(response['output']);
				if (config['mode'] == 'button') {
					if (response['pages'] == response['page']) {
						$(config['more']).hide();
					}
					else {
						$(config['more']).show();
					}
				}
				else if (config['mode'] == 'scroll') {
					pdoPage.Reached = false;
				}
			}
			else {
				wrapper.find(rows).html(response['output']);
			}

			if (pdoPage.callbacks['after'] && typeof(pdoPage.callbacks['after']) == 'function') {
				pdoPage.callbacks['after'].apply(this, [config]);
			}
			else if (config['mode'] != 'scroll') {
				wrapper.css({opacity: 1});
				if (config['mode'] == 'default') {
					$('html, body').animate({scrollTop: wrapper.position().top - 50 || 0}, 0);
				}
			}
		}
	}, 'json');
};

pdoPage.stickyPagination = function(config) {
	var pagination = $(config['pagination']);
	if (pagination.is(':visible')) {
		pagination.sticky({
			wrapperClassName: 'sticky-pagination',
			getWidthFrom: config['pagination'],
			responsiveWidth: true
		});
		$(config['wrapper']).trigger('scroll');
	}
};

if (typeof(jQuery) == 'undefined') {
	console.log("You must load jQuery for using ajax mode in pdoPage.");
}
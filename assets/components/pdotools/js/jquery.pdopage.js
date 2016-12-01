;(function ($, window, document, undefined) {

    'use strict';

    var pluginName = 'pdoPage',
        defaults = {
            wrapper: '#pdopage',
            rows: '#pdopage .row',
            pagination: '#pdopage .pagination',
            link: '#pdopage .pagination a',
            more: '#pdopage .btn-more',
            pdoTitle: '#pdopage .title',
            moreTpl: '<button class="btn btn-default btn-more">Load more</button>',
            waitAnimation: '<div class="wait-wrapper"><div class="wait"></div></div>',
            mode: 'scroll',
            pageVarKey: 'page',
            pageLimit: 12,
            assetsUrl: '/assets/components/pdotools/'
        };

    function Plugin(element, options) {
        this.element = element;
        this.settings = $.extend({}, defaults, options);
        this._defaults = defaults;
        this._name = pluginName;
        this.key = this.settings.pageVarKey;
        this.wrapper = $(this.settings.wrapper);
        this.mode = this.settings.mode;
        this.reached = false;
        this.history = this.settings.history;
        this.oldBrowser = !(window.history && history.pushState);
        this.init();
    }

    $.extend(Plugin.prototype, {
        init: function () {
            var _this = this;
            if (_this.page == undefined) {
                var params = _this.hashGet();
                var page = params[_this.key] == undefined ? 1 : params[_this.key];
                _this.page = Number(page);
            }
            switch (_this.mode) {
                case 'default':
                    this.initDefault();
                    break;
                case 'scroll':
                case 'button':
                    if (_this.history) {
                        if (typeof(jQuery().sticky) == 'undefined') {
                            $.getScript(_this.settings.assetsUrl + 'js/lib/jquery.sticky.min.js', function () {
                                _this.init(_this.settings);
                            });
                            return;
                        }
                        _this.stickyPagination();
                    } else {
                        $(_this.settings.pagination).hide();
                    }
                    if (_this.mode == 'button') {
                        this.initButton();
                    } else {
                        this.initScroll();
                    }
                    break;
            }
        },
        initDefault: function () {
            // Default pagination
            var _this = this;
            $(document).on('click', _this.settings.link, function (e) {
                e.preventDefault();
                var href = $(this).prop('href');
                var match = href.match(new RegExp(_this.key + '=(\\d+)'));
                var page = !match ? 1 : match[1];
                if (_this.page != page) {
                    if (_this.history) {
                        _this.hashAdd(_this.key, page);
                    }
                    _this.loadPage(href);
                }
            });
            if (_this.history) {
                $(window).on('popstate', function (e) {
                    if (e.originalEvent.state && e.originalEvent.state.pdoPage) {
                        _this.loadPage(e.originalEvent.state.pdoPage);
                    }
                });
                history.replaceState({pdoPage: window.location.href}, '');
            }
        },
        initButton: function () {
            // More button pagination
            var _this = this;
            $(_this.settings.rows).after(_this.settings.moreTpl);
            var has_results = false;
            $(_this.settings.link).each(function () {
                var href = $(this).prop('href');
                var match = href.match(new RegExp(_this.key + '=(\\d+)'));
                var page = !match ? 1 : match[1];
                if (page > _this.page) {
                    has_results = true;
                    return false;
                }
            });
            if (!has_results) {
                $(_this.settings.more).hide();
            }
            $(document).on('click', _this.settings.more, function (e) {
                e.preventDefault();
                _this.addPage()
            });
        },
        initScroll: function () {
            // Scroll pagination
            var _this = this;
            var $window = $(window);
            $window.on('scroll', function () {
                if (!_this.reached && $window.scrollTop() > _this.wrapper.height() - $window.height()) {
                    _this.reached = true;
                    _this.addPage();
                }
            });
        },
        addPage: function () {
            var _this = this;
            var params = _this.hashGet();
            var current = params[_this.key] || 1;
            $(_this.settings.link).each(function () {
                var href = $(this).prop('href');
                var match = href.match(new RegExp(_this.key + '=(\\d+)'));
                var page = !match ? 1 : Number(match[1]);
                if (page > current) {
                    if (_this.history) {
                        _this.hashAdd(_this.key, page);
                    }
                    _this.page = current;
                    _this.loadPage(href, 'append');
                    return false;
                }
            });
        },
        loadPage: function (href, mode) {
            var _this = this;
            var rows = $(_this.settings.rows);
            var pagination = $(_this.settings.pagination);
            var match = href.match(new RegExp(_this.key + '=(\\d+)'));
            var page = !match ? 1 : Number(match[1]);
            if (!mode) {
                mode = 'replace';
            }
            if (_this.page == page) {
                return;
            }
            _this.wrapper.trigger('beforeLoad', [_this, _this.settings]);
            if (_this.mode != 'scroll') {
                _this.wrapper.css({opacity: .3});
            }
            var params = _this.hashGet();
            for (var i in params) {
                if (params.hasOwnProperty(i) && i != _this.key) {
                    delete(params[i]);
                }
            }
            params[_this.key] = _this.page = page;
            var waitAnimation = $(_this.settings.waitAnimation);
            if (mode == 'append') {
                _this.wrapper.find(rows).append(waitAnimation);
            } else {
                _this.wrapper.find(rows).empty().append(waitAnimation);
            }
            $.get(window.location.pathname, params, function (response) {
                if (response) {
                    _this.wrapper.find(pagination).html(response.pagination);
                    if (mode == 'append') {
                        _this.wrapper.find(rows).append(response.output);
                        if (_this.mode == 'button') {
                            if (response.pages == response.page) {
                                $(_this.settings.more).hide();
                            } else {
                                $(_this.settings.more).show();
                            }
                        } else if (_this.mode == 'scroll') {
                            _this.reached = false;
                        }
                        waitAnimation.remove();
                    } else {
                        _this.wrapper.find(rows).html(response.output);
                    }
                    _this.wrapper.trigger('afterLoad', [_this, _this.settings, response]);
                    if (_this.mode != 'scroll') {
                        _this.wrapper.css({opacity: 1});
                        if (_this.mode == 'default') {
                            $('html, body').animate({
                                scrollTop: _this.wrapper.position().top - 50 || 0
                            }, 0);
                        }
                    }
                    _this.updateTitle(response);
                }
            }, 'json');
        },
        stickyPagination: function () {
            var _this = this;
            var pagination = $(_this.settings.pagination);
            if (pagination.is(':visible')) {
                pagination.sticky({
                    wrapperClassName: 'sticky-pagination',
                    getWidthFrom: _this.settings.pagination,
                    responsiveWidth: true
                });
                _this.wrapper.trigger('scroll');
            }
        },
        updateTitle: function (response) {
            if (typeof(pdoTitle) == 'undefined') {
                return;
            }
            var title = $('title');
            var separator = pdoTitle.separator || ' / ';
            var tpl = pdoTitle.tpl;
            var parts = [];
            var items = title.text().split(separator);
            var pcre = new RegExp('^' + tpl.split(' ')[0] + ' ');
            for (var i = 0; i < items.length; i++) {
                if (i === 1 && response.page && response.page > 1) {
                    parts.push(tpl.replace('{page}', response.page).replace('{pageCount}', response.pages));
                }
                if (!items[i].match(pcre)) {
                    parts.push(items[i]);
                }
            }
            title.text(parts.join(separator));
        },
        hashGet: function () {
            var vars = {}, hash, splitter, hashes;
            if (!this.oldBrowser) {
                var pos = window.location.href.indexOf('?');
                hashes = (pos != -1) ? decodeURIComponent(window.location.href.substr(pos + 1)) : '';
                splitter = '&';
            } else {
                hashes = decodeURIComponent(window.location.hash.substr(1));
                splitter = '/';
            }
            if (hashes.length == 0) {
                return vars;
            } else {
                hashes = hashes.split(splitter);
            }
            for (var i in hashes) {
                if (hashes.hasOwnProperty(i)) {
                    hash = hashes[i].split('=');
                    if (typeof hash[1] == 'undefined') {
                        vars.anchor = hash[0];
                    } else {
                        vars[hash[0]] = hash[1];
                    }
                }
            }
            return vars;
        },
        hashSet: function (vars) {
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
            if (!this.oldBrowser) {
                if (hash.length != 0) {
                    hash = '?' + hash.substr(1);
                }
                window.history.pushState({pdoPage: window.location.pathname + hash}, '', window.location.pathname + hash);
            } else {
                window.location.hash = hash.substr(1);
            }
        },
        hashAdd: function (key, val) {
            var hash = this.hashGet();
            hash[key] = val;
            this.hashSet(hash);
        },
        hashRemove: function (key) {
            var hash = this.hashGet();
            delete hash[key];
            this.hashSet(hash);
        },
        hashClear: function () {
            this.hashSet({});
        }
    });

    $.fn[pluginName] = function (options) {
        return this.each(function () {
            if (!$.data(this, 'plugin_' + pluginName)) {
                $.data(this, 'plugin_' +
                    pluginName, new Plugin(this, options));
            }
        });
    };

})(jQuery, window, document);
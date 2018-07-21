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
            assetsUrl: '/assets/components/pdotools/',
            scrollTop: true
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
            if (this.page == undefined) {
                var params = this.hashGet();
                var page = params[this.key] == undefined ? 1 : params[this.key];
                this.page = Number(page);
            }
            switch (this.mode) {
                case 'default':
                    this.initDefault();
                    break;
                case 'scroll':
                case 'button':
                    if (this.history) {
                        if (typeof(jQuery().sticky) == 'undefined') {
                            $.getScript(this.settings.assetsUrl + 'js/lib/jquery.sticky.js', function () {
                                _this.init(_this.settings);
                            });
                            return;
                        }
                        this.stickyPagination();
                    } else {
                        $(this.settings.pagination).hide();
                    }
                    if (this.mode == 'button') {
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
            $(document).on('click', this.settings.link, function (e) {
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
            if (this.history) {
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
            $(this.settings.rows).after(this.settings.moreTpl);
            var has_results = false;
            $(this.settings.link).each(function () {
                var href = $(this).prop('href');
                var match = href.match(new RegExp(_this.key + '=(\\d+)'));
                var page = !match ? 1 : match[1];
                if (page > _this.page) {
                    has_results = true;
                    return false;
                }
            });
            if (!has_results) {
                $(this.settings.more).hide();
            }
            $(document).on('click', this.settings.more, function (e) {
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
            var params = this.hashGet();
            var current = params[this.key] || 1;
            $(this.settings.link).each(function () {
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
            var rows = $(this.settings.rows);
            var pagination = $(this.settings.pagination);
            var match = href.match(new RegExp(this.key + '=(\\d+)'));
            var page = !match ? 1 : Number(match[1]);
            if (!mode) {
                mode = 'replace';
            }
            if (this.page == page && mode != 'force') {
                return;
            }
            this.wrapper.trigger('beforeLoad', [this, this.settings]);
            if (this.mode != 'scroll') {
                this.wrapper.css({opacity: .3});
            }
            this.page = page;
            var waitAnimation = $(this.settings.waitAnimation);
            if (mode == 'append') {
                this.wrapper.find(rows).append(waitAnimation);
            } else {
                this.wrapper.find(rows).empty().append(waitAnimation);
            }
            var params = this.getUrlParameters(href);
            params[this.key] = this.page;
            $.get(window.location.pathname, params, function (response) {
                if (response) {
                    _this.wrapper.find(pagination).replaceWith(response.pagination);
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
                        if (mode == 'force' && _this.history) {
                            _this.hashSet(params);
                        }
                    }
                    _this.wrapper.trigger('afterLoad', [_this, _this.settings, response]);
                    if (_this.mode != 'scroll') {
                        _this.wrapper.css({opacity: 1});
                        if (_this.mode == 'default' && _this.settings.scrollTop) {
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
            var pagination = $(this.settings.pagination);
            if (pagination.is(':visible')) {
                pagination.sticky({
                    wrapperClassName: 'sticky-pagination',
                    getWidthFrom: this.settings.pagination,
                    responsiveWidth: true
                });
                this.wrapper.trigger('scroll');
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
        getUrlParameters: function (url) {
            var result = {};
            var searchIndex = url.indexOf("?");
            if (searchIndex !== -1) {
                result = this.deparam(url.substring(searchIndex + 1));
            }
            return result;
        },
        deparam: function (params) {
            // source: https://github.com/jupiterjs/jquerymx/blob/master/lang/string/deparam/deparam.js
            var digitTest = /^\d+$/,
                keyBreaker = /([^\[\]]+)|(\[\])/g,
                paramTest = /([^?#]*)(#.*)?$/,
                prep = function (str) {
                    return decodeURIComponent(str.replace(/\+/g, ' '));
                };
            var data = {}, pairs, lastPart;
            if (params && paramTest.test(params)) {
                pairs = params.split('&');
                $.each(pairs, function (index, pair) {
                    var parts = pair.split('='),
                        key = prep(parts.shift()),
                        value = prep(parts.join('=')),
                        current = data;
                    if (key) {
                        parts = key.match(keyBreaker);
                        for (var j = 0, l = parts.length - 1; j < l; j++) {
                            if (!current[parts[j]]) {
                                // If what we are pointing to looks like an `array`
                                current[parts[j]] = digitTest.test(parts[j + 1]) || parts[j + 1] === '[]' ? [] : {};
                            }
                            current = current[parts[j]];
                        }
                        lastPart = parts.pop();
                        if (lastPart === '[]') {
                            current.push(value);
                        } else {
                            current[lastPart] = value;
                        }
                    }
                });
            }
            return data;
        },
        hashGet: function () {
            var vars = {}, hash, hashes;
            if (!this.oldBrowser) {
                var pos = window.location.href.indexOf('?');
                hashes = (pos != -1) ? decodeURIComponent(window.location.href.substr(pos + 1)) : '';
                vars = this.deparam(hashes);
            } else {
                hashes = decodeURIComponent(window.location.hash.substr(1));
                if (hashes.length) {
                    hashes = hashes.split('/');
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
                }
            }
            return vars;
        },
        hashSet: function (vars) {
            var hash = '';
            for (var i in vars) {
                if (vars.hasOwnProperty(i)) {
                    if (typeof vars[i] != 'object') {
                        hash += '&' + i + '=' + vars[i];
                    } else {
                        for (var j in vars[i]) {
                            if (vars[i].hasOwnProperty(j)) {
                                if (!isNaN(parseFloat(j)) && isFinite(parseFloat(j))) {
                                    hash += '&' + i + '[' + ']=' + vars[i][j];
                                } else {
                                    hash += '&' + i + '[' + j + ']=' + vars[i][j];
                                }
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
        var args = arguments;
        if (options === undefined || typeof options === 'object') {
            return this.each(function () {
                if (!$.data(this, 'plugin_' + pluginName)) {
                    $.data(this, 'plugin_' + pluginName, new Plugin(this, options));
                }
            });
        } else if (typeof options === 'string' && options[0] !== '_' && options !== 'init') {
            var returns;
            this.each(function () {
                var instance = $.data(this, 'plugin_' + pluginName);
                if (instance instanceof Plugin && typeof instance[options] === 'function') {
                    returns = instance[options].apply(instance, Array.prototype.slice.call(args, 1));
                }
                if (options === 'destroy') {
                    $.data(this, 'plugin_' + pluginName, null);
                }
            });
            return returns !== undefined ? returns : this;
        }
    };

})(jQuery, window, document);

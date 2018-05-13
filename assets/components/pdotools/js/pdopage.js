if (typeof(pdoPage) == 'undefined') {
    pdoPage = {callbacks: {}, keys: {}, configs: {}};
}

pdoPage.Reached = false;

pdoPage.initialize = function (config) {

    if (pdoPage.keys[config.pageVarKey] === undefined) {
        var tkey = config.pageVarKey;
        var tparams = pdoPage.Hash.get();
        var tpage = tparams[tkey] || 1;
        pdoPage.keys[tkey] = Number(tpage);
        pdoPage.configs[tkey] = config;
    }
    var $this = this;
    switch (config.mode) {
        case 'default':
            document.querySelector(config.pagination).addEventListener('click', function(e){
                e.preventDefault();
                var target = e.target;
                if(target.tagName == 'A') {
                    clickLinkPagenation(target);
                }
            });
            
            function clickLinkPagenation(node){
                var href = node.getAttribute('href');
                var key = config.pageVarKey;
                var match = href.match(new RegExp(key + '=(\\d+)'));
                var page = !match ? 1 : match[1];
                if (pdoPage.keys[key] != page) {
                    if (config.history) {
                        if (page == 1) {
                            pdoPage.Hash.remove(key);
                        } else {
                            pdoPage.Hash.add(key, page);
                        }
                    }
                    $this.loadPage(href, config);
                }
            }

            if (config.history) {
                window.addEventListener('popstate', function(e){
                    if (e.state && e.state.pdoPage) {
                        $this.loadPage(e.state.pdoPage, config);
                    }
                });

                history.replaceState({pdoPage: window.location.href}, '');
            }
            break;

        case 'scroll':
        case 'button':
            if (config.history) {
                if (typeof(jQuery().sticky) === 'undefined') {
                    
                    var getScript = function (source, callback) {
                        var script = document.createElement('script');
                        var prior = document.getElementsByTagName('script')[0];
                        script.async = 1;
                    
                        script.onload = script.onreadystatechange = function( _, isAbort ) {
                            if(isAbort || !script.readyState || /loaded|complete/.test(script.readyState) ) {
                                script.onload = script.onreadystatechange = null;
                                script = undefined;
                    
                                if(!isAbort) { if(callback) callback(); }
                            }
                        };
                    
                        script.src = source;
                        prior.parentNode.insertBefore(script, prior);
                    }
                    
                    getScript(config.assetsUrl + 'js/lib/jquery.sticky.min.js', function() {
                        pdoPage.initialize(config);
                    });
                    
                    return;
                }
                pdoPage.stickyPagination(config);
            } else {
                document.querySelector(config.pagination).style.display = 'none';
            }
            
            var key = config.pageVarKey;

            if (config.mode == 'button') {
                // Add more button
                document.querySelector(config.rows).insertAdjacentHTML('afterend', config.moreTpl);
                var has_results = false;
                
                [].slice.call(document.querySelectorAll(config.link)).forEach(function(el, i){
                    var href = el.getAttribute('href');
                    var match = href.match(new RegExp(key + '=(\\d+)'));
                    var page = !match ? 1 : match[1];
                    if (page > pdoPage.keys[key]) {
                        has_results = true;
                        return false;
                    }
                });
                if (!has_results) {
                    document.querySelector(config.more).style.display = 'none';
                }

                document.querySelector(config.more).addEventListener('click', function(e){
                    e.preventDefault();
                    pdoPage.addPage(config);
                });
            }
            else {
                // Scroll pagination
                var scrollPagination = function() {
                    var wrapper = document.querySelector(config.wrapper);
                    if (!pdoPage.Reached && -document.body.getBoundingClientRect().top > wrapper.clientHeight - window.innerHeight) {
                        pdoPage.Reached = true;
                        pdoPage.addPage(config);
                    }
                };
                
                document.addEventListener('scroll', scrollPagination);
                scrollPagination();
                
            }
            break;
    }
};

pdoPage.addPage = function (config) {
    
    var key = config.pageVarKey;
    var current = pdoPage.keys[key] || 1;
    var links = document.querySelectorAll(config.link);
    for(var i = 0; i < links.length; i++){
        var href = links[i].getAttribute('href');
        var match = href.match(new RegExp(key + '=(\\d+)'));
        var page = !match ? 1 : Number(match[1]);
        if (page > current) {
            if (config.history) {
                if (page == 1) {
                    pdoPage.Hash.remove(key);
                } else {
                    pdoPage.Hash.add(key, page);
                }
            }
            pdoPage.loadPage(href, config, 'append');
            return false;
        }
    }

 
};

pdoPage.loadPage = function (href, config, mode) {
    var wrapper = document.querySelector(config.wrapper);
    var rows = document.querySelector(config.rows);
    var pagination = document.querySelector(config.pagination);
    var key = config.pageVarKey;
    var match = href.match(new RegExp(key + '=(\\d+)'));
    var page = !match ? 1 : Number(match[1]);
    if (!mode) {
        mode = 'replace';
    }

    if (pdoPage.keys[key] == page) {
        return;
    }
    if (pdoPage.callbacks.before && typeof(pdoPage.callbacks.before) == 'function') {
        pdoPage.callbacks.before.apply(this, [config]);
    }
    else {
        if (config.mode != 'scroll') {
            wrapper.style.opacity = '0.3';
        }
        wrapper.classList.add('loading');
    }

    var params = pdoPage.Hash.get();
    for (var i in params) {
        if (params.hasOwnProperty(i) && pdoPage.keys[i] && i != key) {
            delete(params[i]);
        }
    }

    params[key] = pdoPage.keys[key] = page;
    params.pageId = config.pageId;
    params.hash = config.hash;
    
    var xhr = new XMLHttpRequest();
    xhr.open('POST', config.connectorUrl, true);
    xhr.setRequestHeader('Accept', 'application/json, text/javascript, */*; q=0.01');
    xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded;charset=UTF-8');
    xhr.setRequestHeader("X-Requested-With", "XMLHttpRequest");
    xhr.onreadystatechange = function () {
        if (xhr.readyState === 4 && xhr.status === 200) {
            var response = JSON.parse(xhr.responseText);
            if (response && response.total) {
                pagination.innerHTML = response.pagination;
                if (mode == 'append') {
                    rows.insertAdjacentHTML("beforeEnd", response.output);
                    if (config.mode == 'button') {
                        var btnMore = document.querySelector(config.more);
                        if (response.pages == response.page) {
                            btnMore.style.display = 'none';
                        }
                        else {
                            btnMore.style.display = '';
                        }
                    }
                    else if (config.mode == 'scroll') {
                        pdoPage.Reached = false;
                    }
                }
                else {
                    rows.innerHTML = response.output;
                }
    
                if (pdoPage.callbacks.after && typeof(pdoPage.callbacks.after) == 'function') {
                    pdoPage.callbacks.after.apply(this, [config, response]);
                }
                else {
                    wrapper.classList.remove('loading');
                    if (config.mode != 'scroll') {
                        wrapper.style.opacity = 1;
                        if (config.mode == 'default') {
                            window.scrollTo(0, wrapper.offsetHeight - 50 || 0);
                        }
                    }
                }
                pdoPage.updateTitle(config, response);
                
                var event = new CustomEvent('pdopage_load', {'detail': [config, response]});
                document.dispatchEvent(event);
            }

        }
    };
    var body = key + '=' + encodeURIComponent(page) + '&pdoPage.keys.' + key + '=' + encodeURIComponent(page) + '&pageId=' + encodeURIComponent(config.pageId) + '&hash=' + config.hash;
    xhr.send(body);
};

pdoPage.stickyPagination = function (config) {
    var pagination = document.querySelector(config.pagination);
    if (pagination.offsetParent === null) {
        pagination.sticky({
            wrapperClassName: 'sticky-pagination',
            getWidthFrom: config.wrapper,
            responsiveWidth: true,
            topSpacing: 2
        });
        
        // For a full list of event types: https://developer.mozilla.org/en-US/docs/Web/API/document.createEvent
        var event = document.createEvent('HTMLEvents');
        event.initEvent('scroll', true, false);
        document.querySelector(config.wrapper).dispatchEvent(event);
    }
};

pdoPage.updateTitle = function (config, response) {
    if (typeof(pdoTitle) == 'undefined') {
        return;
    }
    var $title = document.querySelector('title');
    var separator = pdoTitle.separator || ' / ';
    var tpl = pdoTitle.tpl;

    var title = [];
    var items = $title.textContent.split(separator);
    var pcre = new RegExp('^' + tpl.split(' ')[0] + ' ');
    for (var i = 0; i < items.length; i++) {
        if (i === 1 && response.page && response.page > 1) {
            title.push(tpl.replace('{page}', response.page).replace('{pageCount}', response.pages));
        }
        if (!items[i].match(pcre)) {
            title.push(items[i]);
        }
    }
    $title.textContent = title.join(separator);
};

pdoPage.Hash = {
    get: function () {
        var vars = {}, hash, splitter, hashes;
        if (!this.oldbrowser()) {
            var pos = window.location.href.indexOf('?');
            hashes = (pos != -1) ? decodeURIComponent(window.location.href.substr(pos + 1)).replace('+', ' ') : '';
            splitter = '&';
        }
        else {
            hashes = decodeURIComponent(window.location.hash.substr(1)).replace('+', ' ');
            splitter = '/';
        }

        if (hashes.length === 0) {
            return vars;
        }
        else {
            hashes = hashes.split(splitter);
        }

        var matches, key;
        for (var i in hashes) {
            if (hashes.hasOwnProperty(i)) {
                hash = hashes[i].split('=');
                if (typeof hash[1] == 'undefined') {
                    vars['anchor'] = hash[0];
                }
                else {
                    matches = hash[0].match(/\[(.*?|)\]$/);
                    if (matches) {
                        key = hash[0].replace(matches[0], '');
                        if (!vars.hasOwnProperty(key)) {
                            // Array
                            if (matches[1] == '') {
                                vars[key] = [];
                            }
                            // Object
                            else {
                                vars[key] = {};
                            }
                        }
                        if (vars[key] instanceof Array) {
                            vars[key].push(hash[1]);
                        }
                        else {
                            vars[key][matches[1]] = hash[1];
                        }
                    }
                    // String or numeric
                    else {
                        vars[hash[0]] = hash[1];
                    }
                }
            }
        }
        return vars;
    },

    set: function (vars) {
        var hash = '';
        for (var i in vars) {
            if (vars.hasOwnProperty(i)) {
                if (typeof vars[i] == 'object') {
                    for (var j in vars[i]) {
                        if (vars[i].hasOwnProperty(j)) {
                            // Array
                            if (vars[i] instanceof Array) {
                                hash += '&' + i + '[]=' + vars[i][j];
                            }
                            // Object
                            else {
                                hash += '&' + i + '[' + j + ']=' + vars[i][j];
                            }
                        }
                    }
                }
                // String or numeric
                else {
                    hash += '&' + i + '=' + vars[i];
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
    },

    add: function (key, val) {
        var hash = this.get();
        hash[key] = val;
        this.set(hash);
    },

    remove: function (key) {
        var hash = this.get();
        delete hash[key];
        this.set(hash);
    },

    clear: function () {
        this.set({});
    },

    oldbrowser: function () {
        return !(window.history && history.pushState);
    }
};

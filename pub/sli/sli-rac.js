window.SLI = window.SLI || {};
SLI.jQuery = SLI.jQuery || jQuery;
SLI.config = SLI.config || {};
(function(window, document, jQuery) {
    SLI.config.rac = SLI.config.rac || {};
    SLI.config.rac.templateId = "rac-data";
    SLI.rac = {
        base: location.protocol + "//accuride.resultspage.com",
        version: "5.3",
        selector: ".sli_ac_suggestion, .sli_ac_product",
        behaviourOptions: {}
    };
    if (window.sliSpark && window.sliSpark.t) {
        window.sliSpark("sli:onBeaconUserId", function(beaconUserId, pageId) {
            SLI.rac.behaviourOptions.SLIBeacon = beaconUserId;
            SLI.rac.behaviourOptions.SLIPid = pageId
        })
    }
    var IE_VERSION = IEVersion();
    if (IE_VERSION && IE_VERSION < 9) {
        return false
    } else {
        loadRAC()
    }

    function loadRAC() {
        if (typeof window.SLI.Autocomplete !== "undefined") {
            return
        }
        var location = document.location;
        if (location.host.match(/\.(?:local|cfe\.nz|resultsdemo|resultsstage)/)) {
            SLI.rac.base = location.protocol + "//" + document.domain
        }
        loadResource(SLI.rac.base + "/tb/ts/rac-data/css/styles.css?r=566835");
        SLI.jQuery(function() {
            SLI.jQuery("#sli_search_1, #sli_search_2").attr("data-provide", "rac");
            loadAndInitRac()
        })
    }

    function loadAndInitRac() {
        loadRACStub();
        SLI.jQuery(document).trigger({
            type: "sli-rac-event",
            message: "preInit"
        });
        window.sliAutocomplete = {};
        window.sliAutocomplete.select = new SLI.Autocomplete(SLI.rac);
        window.sliAutocomplete.wrapper = SLI.jQuery(sliAutocomplete.select.el)
    }

    function loadResource(path, callback) {
        var tag;
        if (path.match(/\.css(\?.+)?$/)) {
            tag = document.createElement("link");
            tag.href = path;
            tag.rel = "stylesheet";
            tag.type = "text/css";
            tag.media = "all"
        } else {
            tag = document.createElement("script");
            tag.src = path
        }
        tag.onload = tag.onreadystatechange = function() {
            if (!tag.readyState || /loaded|complete/.test(tag.readyState)) {
                tag = tag.onload = tag.onreadystatechange = null;
                if (typeof callback === "function") {
                    callback(true)
                }
            }
        };
        tag.onerror = function() {
            tag = tag.onerror = null;
            if (typeof callback === "function") {
                callback(false)
            }
        };
        var head = document.head || document.getElementsByTagName("head")[0] || document.documentElement;
        head.appendChild(tag)
    }

    function IEVersion() {
        var myNav = navigator.userAgent.toLowerCase();
        return (myNav.indexOf("msie") != -1) ? parseInt(myNav.split("msie")[1]) : false
    }
    SLI.jQuery(document).on("sli-rac-event", function(e) {
        switch (e.message) {
            case "select":
                var racType;
                if (e.racData.url.match(/rt=racscope/)) {
                    racType = "scope"
                } else {
                    if (e.racData.url.match(/rt=racclick/)) {
                        racType = "product"
                    } else {
                        racType = e.racData.type || "suggestion"
                    }
                }
                var track = "/search?w=" + encodeURIComponent(e.racData.query) + "&ts=rac&ractype=" + racType;
                try {
                    if (typeof _gaq !== "undefined") {
                        _gaq.push(["_trackPageview", track])
                    }
                } catch (err) {
                    console.log("SLI GA - issue with gaq syntax - virtual URL.")
                }
                try {
                    if (typeof(pageTracker) !== "undefined") {
                        pageTracker._trackPageview(track)
                    }
                } catch (err) {
                    console.log("SLI GA - issue with pageTracker syntax - virtual URL.")
                }
                try {
                    if (typeof(ga) !== "undefined") {
                        if (window.sliSpark && window.sliSpark.t) {
                            window.sliSpark("sli:pageType", "")
                        }
                        ga("send", "pageview", {
                            page: track
                        })
                    }
                } catch (err) {
                    console.log("SLI GA - issue with ga syntax - virtual URL.")
                }
                try {
                    if (typeof(dataLayer) !== "undefined") {
                        if (window.sliSpark && window.sliSpark.t) {
                            window.sliSpark("sli:pageType", "")
                        }
                        dataLayer.push({
                            event: "virtualpageview",
                            url: track
                        })
                    }
                } catch (err) {
                    console.log("SLI GA - issue with datalayer syntax - virtual URL.")
                }
                return e.racData
        }
    }).on("sli-ajax-complete", function(e) {
        if (typeof(sliAutocomplete) !== "undefined") {
            if (sliAutocomplete.select.input) {
                sliAutocomplete.select.input._onClear()
            }
        }
    });

    function loadRACStub() {
        window.SLI = window.SLI || {};
        SLI.jQuery = SLI.jQuery || jQuery;
        SLI.currentWindowLocation = document.location.href;
        var SUGGESTION_CLASS = "sli_ac_sugg";
        var ESCAPE_REGEX = /([.*+?^=!:${}()|[\]\/\\])/g;
        var keyCode = {
            TAB: 9,
            RETURN: 13,
            ESC: 27,
            PAGEUP: 33,
            PAGEDOWN: 34,
            END: 35,
            LEFT: 37,
            UP: 38,
            RIGHT: 39,
            DOWN: 40,
            DEL: 46
        };
        if (!Function.prototype.bind) {
            Function.prototype.bind = function(context) {
                if (typeof this !== "function") {
                    throw new TypeError("Function.prototype.bind - what is trying to be bound is not callable")
                }

                function NOP() {}
                var slice = [].slice,
                    args = slice.call(arguments, 1),
                    target = this,
                    boundFn = function() {
                        return target.apply(this instanceof NOP ? this : context || window, args.concat(slice.call(arguments)))
                    };
                NOP.prototype = this.prototype;
                boundFn.prototype = new NOP();
                return boundFn
            }
        }

        function throttled(fn) {
            var running = false;
            var requestAnimationFrame = window.requestAnimationFrame || window.mozRequestAnimationFrame || window.webkitRequestAnimationFrame;
            var actualCallback = function() {
                fn();
                running = false
            };
            if (requestAnimationFrame) {
                return function() {
                    if (!running) {
                        running = true;
                        requestAnimationFrame(actualCallback)
                    }
                }
            } else {
                return function() {
                    if (!running) {
                        running = true;
                        setTimeout(actualCallback, 66)
                    }
                }
            }
        }

        function highlightTerm(html, terms) {
            for (var i = 0; i < terms.length; ++i) {
                var escapedTerm = terms[i].replace(ESCAPE_REGEX, "\\$1");
                if (escapedTerm !== "") {
                    var reg = new RegExp("^(.*?\\b)(" + escapedTerm + ")(.*?)$", "gi");
                    var matches = reg.exec(html);
                    if (matches) {
                        var prefix = matches[1];
                        var term = matches[2];
                        var suffix = matches[3];
                        var newTerms = terms.slice();
                        newTerms.splice(i, 1);
                        return highlightTerm(prefix, newTerms) + '<span class="highlight">' + term + "</span>" + highlightTerm(suffix, newTerms)
                    }
                }
            }
            return html
        }

        function getWidth() {
            return SLI.jQuery(window).width()
        }

        function normalise(value) {
            return (value || "").replace(/^[\s\uFEFF\xA0]+|[\s\uFEFF\xA0]+$/g, "").replace(/\s{2,}/g, " ")
        }

        function generateHash(str) {
            var hash = 0,
                chr;
            for (var i = 0, len = str.length; i < len; i++) {
                chr = str.charCodeAt(i);
                hash = ((hash << 5) - hash) + chr;
                hash |= 0
            }
            return hash + ""
        }

        function QueryRequest(url, parent) {
            var self = this;
            var head = document.head || document.getElementsByTagName("head")[0] || document.documentElement;
            var script = this.script = document.createElement("script");
            script.async = true;
            script.src = url;
            script.setAttribute("hash", parent.lastRequest);
            script.onload = script.onreadystatechange = function(_, isAbort) {
                if (isAbort || !script.readyState || /loaded|complete/.test(script.readyState)) {
                    script.onload = script.onreadystatechange = null;
                    if (script.parentNode) {
                        script.parentNode.removeChild(script)
                    }
                    self.script = null
                }
            };
            head.appendChild(script)
        }
        QueryRequest.prototype.abort = function() {
            var script = this.script;
            if (script) {
                script.onload(undefined, true)
            }
        };

        function Autocomplete(options) {
            if (options) {
                this.init(options)
            } else {
                throw "need rich autocomplete settings"
            }
            this.urls = [];
            var inputs = this.inputs = [];
            var el = this.createElement();
            var st = el.style;
            var elements = document.querySelectorAll(this.searchboxAttr);
            var fixed = [];
            for (var i = 0, length = elements.length; i < length;
                ++i) {
                SLI.jQuery(elements[i]).attr("data-rac-input", i);
                var input = new Input(elements[i], this);
                inputs.push(input);
                if (input.isFixed) {
                    fixed.push(input.el)
                }
            }
            var self = this;
            SLI.jQuery("#" + this.id).on("click", ".sli_ac_suggestion, .sli_ac_product", function(e) {
                setTimeout(self.selectCurrent.bind(self, e), 50);
                this.blockSubmit = false;
                return false
            }).on("click", ".sli_close_rac", function() {
                self.input._onClear()
            });
            SLI.jQuery("body").on("mouseleave", "#" + el.id, this.resetActive.bind(this));
            switch (this.width) {
                case "auto":
                    break;
                case "parent":
                    this.resize = function() {
                        this.position()
                    };
                    break;
                default:
                    var w = parseInt(this.width, 10);
                    if (typeof w !== "number") {
                        w = this.defaults.width
                    }
                    st.width = w + "px"
            }
            var recalc = function() {
                if (this.resize) {
                    this.resize()
                }
                this.checkScreenSize()
            };
            recalc = recalc.bind(this);
            SLI.jQuery(window).on("resize", throttled(recalc)).on("orientationchange", recalc);
            if (fixed.length) {
                this.fixed = fixed;
                var windowScroll = function() {
                    var input = this.input;
                    if (!input || !this.isVisible || !this.fixed || this.fixed.indexOf(input.el) < 0) {
                        return
                    }
                    if (input.el.offsetWidth === 0) {
                        this.hide(input.el)
                    } else {
                        recalc()
                    }
                };
                SLI.jQuery(window).on("scroll", throttled(windowScroll.bind(this)))
            }
            this.checkDevice();
            this.checkScreenSize();
            for (var i = 0, length = inputs.length; i < length; ++i) {
                var input = inputs[i];
                input.el.setAttribute("data-rac-loaded", "true")
            }
        }
        Autocomplete.prototype = {
            id: "sli_autocomplete",
            tagName: "ul",
            activeClass: "sli_ac_active",
            dropdownClass: "sli_ac_dropdown",
            searchboxAttr: "[data-provide=rac]",
            zIndex: 30000,
            width: "parent",
            base: "",
            params: "/search?ts=rac-data&w=",
            defaults: {
                width: 420
            },
            offsetLeft: 0,
            offsetTop: 0,
            align: "right",
            maxSearchLength: 100,
            isVisible: false,
            selector: "li li",
            focus: false,
            currentSelected: -1,
            value: undefined,
            request: null,
            lastRequest: "",
            lastSuggestion: "",
            hoverTimeout: null,
            lastIndex: -1,
            input: null,
            size: null,
            breakpoints: {
                small: {
                    maxWidth: 767,
                    value: "s"
                },
                other: {
                    maxWidth: undefined,
                    value: "o"
                }
            },
            valueStop: "",
            stopQuery: false,
            init: function(options) {
                var i;
                if (!options.selector) {
                    throw "selector not specified"
                } else {
                    this.selector = options.selector
                }
                var sli = window.sli;
                if (sli && sli.global && sli.global.base) {
                    this.base = sli.global.base
                }
                if (options.params) {
                    this.params = options.params
                }
                if (options.base) {
                    this.base = options.base.replace(/\/+$/, "") + this.params
                }
                this.behaviourOptions = options.behaviourOptions;
                this.triggerEvent("postInit", this)
            },
            createElement: function() {
                var el = this.el = document.createElement(this.tagName);
                el.id = this.id;
                el.className = "sli_rich sli_dynamic sli_sugg_left";
                var st = el.style;
                st.display = "none";
                st.zIndex = this.zIndex;
                document.body.appendChild(el);
                return el
            },
            setInput: function(input) {
                if (this.input !== input || (this.fixed && this.fixed.indexOf(input.el) >= 0)) {
                    this.input = input;
                    if (this.resize) {
                        this.resize()
                    }
                }
            },
            position: function() {
                var input = this.input;
                if (!input) {
                    return
                }
                this.stickToTop(SLI.jQuery(input.el).closest("form"));
                var target = input.el;
                var $input = SLI.jQuery(target);
                var $form = $input.closest("form");
                var isTargetFixed = this.fixed && this.fixed.indexOf(target) >= 0;
                var docEl = document.documentElement;
                var body = document.body;
                var rect = target.getBoundingClientRect();
                var top = rect.top + target.offsetHeight + this.offsetTop;
                var left = rect.left + this.offsetLeft;
                var el = this.el;
                var $rac = SLI.jQuery(el);
                var st = el.style;
                if (!isTargetFixed) {
                    top += (typeof window.pageYOffset != "undefined" ? window.pageYOffset : docEl.scrollTop ? docEl.scrollTop : body.scrollTop ? body.scrollTop : 0);
                    left += (typeof window.pageXOffset != "undefined" ? window.pageXOffset : docEl.scrollLeft ? docEl.scrollLeft : body.scrollLeft ? body.scrollLeft : 0)
                }
                if (!$rac.hasClass("sli_is_full_width")) {
                    var w = parseInt($form.css("width"));
                    if (this.isTwoColumnsLayout() || ("grid" == "list" && this.align == "right")) {
                        var thumbWidth = 50;
                        if (this.align === "right") {
                            w = left + target.offsetWidth - thumbWidth;
                            if (w < target.offsetWidth) {
                                w = target.offsetWidth
                            } else {
                                left += target.offsetWidth - el.offsetWidth
                            }
                        } else {
                            w = body.offsetWidth - left - thumbWidth;
                            if (w < target.offsetWidth) {
                                w = target.offsetWidth
                            }
                        }
                    }
                    st.width = w + "px"
                }
                if (this.isStickToTop($form)) {
                    st.top = $form.css("height")
                } else {
                    st.top = top + "px"
                }
                st.left = left + "px";
                st.position = isTargetFixed ? "fixed" : "absolute";
                if (!this.hasTouch) {
                    input = this.input.el;
                    this._setPositionUnder(SLI.jQuery(input).siblings("input.sli_suggested_word"), input)
                }
                this.setCloseArea()
            },
            _select: function(index) {
                var items = this.items,
                    previous = this.currentSelected,
                    selected = items[index],
                    $selected = SLI.jQuery(selected);
                this.currentSelected = index;
                if (previous !== index && previous >= 0 && previous < items.length) {
                    SLI.jQuery(items[previous]).removeClass(this.activeClass)
                }
                $selected.addClass(this.activeClass);
                document.querySelectorAll(this.searchboxAttr + "." + this.dropdownClass)[0].setAttribute("aria-activedescendant", $selected[0].id);
                this._updateActiveSearchSuggestion($selected);
                if ($selected.hasClass("sli_recent_search")) {
                    return
                }
                var suggestion = $selected.find("[data-suggested-term]");
                if (suggestion.length > 0) {
                    var suggestedWord = suggestion.attr("data-suggested-term");
                    if (this.canShowDynamic()) {
                        var suggestedFacet = suggestion.attr("data-suggested-facet"),
                            extraHref = "productsOnly=yes";
                        if (suggestedFacet) {
                            extraHref += "&af=" + suggestedFacet
                        }
                        this.fireRequest(suggestedWord, extraHref)
                    }
                }
                if (this.itemSelected) {
                    this.itemSelected(selected)
                }
            },
            canShowDynamic: function() {
                return !this.hasTouch && this.screenSize() !== "s"
            },
            isTwoColumnsLayout: function() {
                return this.canShowDynamic() || this.isTablet()
            },
            _updateActiveSearchSuggestion: function($selected) {
                if (!this.canShowDynamic()) {
                    SLI.jQuery(".sli_ac_suggestion").removeClass(this.activeClass);
                    document.querySelectorAll(this.searchboxAttr + "." + this.dropdownClass)[0].setAttribute("aria-activedescendant", "");
                    return
                }
                if ($selected.hasClass("sli_ac_suggestion")) {
                    SLI.jQuery(".sli_ac_suggestion").removeClass(this.activeClass);
                    $selected.addClass(this.activeClass);
                    document.querySelectorAll(this.searchboxAttr + "." + this.dropdownClass)[0].setAttribute("aria-activedescendant", $selected[0].id)
                }
            },
            moveSelect: function(steps) {
                var max = this.items.length - 1;
                var index = this.currentSelected + steps;
                if (index < 0) {
                    index = 0
                } else {
                    if (index > max) {
                        index = max
                    }
                }
                this._select(index)
            },
            next: function() {
                this.moveSelect(1)
            },
            prev: function() {
                this.moveSelect(-1)
            },
            pageDown: function() {
                var current = this.currentSelected;
                var step = jQuery(".sli_ac_suggestion").length - current;
                this.moveSelect(step)
            },
            pageUp: function() {
                var current = this.currentSelected;
                this.moveSelect(-current)
            },
            show: function() {
                var el = this.el;
                var display;
                if (!this.isVisible) {
                    this.triggerEvent("show", {})
                }
                if (display !== false) {
                    el.style.display = "block";
                    this.isVisible = true;
                    if (this.input) {
                        SLI.jQuery(this.input.el).addClass(this.dropdownClass).attr("aria-expanded", "true")
                    }
                }
                var $suggestionsColumn = SLI.jQuery(".sli_ac_suggestions");
                if (this.isTwoColumnsLayout()) {
                    var minHeight = "400",
                        searchSuggestionsHeight = SLI.jQuery(".sli_ac_suggestions").css("height");
                    if (!minHeight || parseInt(minHeight) < parseInt(searchSuggestionsHeight)) {
                        minHeight = searchSuggestionsHeight
                    }
                    SLI.jQuery(".sli_product_list").css({
                        "min-height": minHeight
                    });
                    $suggestionsColumn.removeClass("sli_is_hidden")
                } else {
                    if (!SLI.jQuery(".sli_ac_suggestion").length) {
                        $suggestionsColumn.removeClass("sli_is_hidden").addClass("sli_is_hidden")
                    }
                }
                this.position()
            },
            setCloseArea: function() {
                var $rac = SLI.jQuery(this.el),
                    $closeArea = $rac.siblings(".sli_close_area"),
                    self = this;
                if ($closeArea.length) {
                    $closeArea.removeClass("sli_is_hidden")
                } else {
                    $closeArea = SLI.jQuery('<div class="js_sli_close_area sli_close_area"></div>');
                    $closeArea.insertAfter($rac);
                    $closeArea.off("click").on("click", function() {
                        self.input._onClear()
                    })
                }
            },
            hide: function(input) {
                this.el.style.display = "none";
                this.isVisible = false;
                if (input) {
                    var $input = SLI.jQuery(input.el);
                    $input.removeClass(this.dropdownClass);
                    this._setSuggestionInput("", $input);
                    this.resetStickToTop($input.closest("form"));
                    SLI.jQuery(input.el).attr("aria-expanded", "false")
                }
            },
            selectCurrent: function(e) {
                var i = this.currentSelected;
                var target = this.input ? this.input.el : e.target;
                var q = target.value;
                var param = {};
                var url;
                if (i === -1 && e) {
                    var suggestions = SLI.jQuery(".sli_ac_suggestion");
                    var t = SLI.jQuery(e.target);
                    var has = t.parent(".sli_ac_suggestion");
                    if (has && has.length > 0) {
                        t = has
                    }
                    i = suggestions.index(t)
                }
                if (i === -1) {
                    this.hide(this.input);
                    var skipCheck = SLI.rac.behaviourOptions.skipBlankCheck || false;
                    if (!skipCheck && (!q || q.length === 0 || !q.trim())) {
                        return false
                    }
                    if (typeof SliSearch === "function") {
                        SliSearch(target)
                    } else {
                        var $form = SLI.jQuery(target).closest('form[action*="/search"]');
                        if ($form.length > 0) {
                            $form.submit()
                        } else {
                            param.url = window.location.protocol + "//" + "stage.accuride.com".toLowerCase() + "/search/go?w=" + encodeURIComponent(q.trim());
                            var searchBox = SLI.jQuery(this.input.el);
                            var extraParams = "";
                            if (searchBox.attr("data-sli-append-inputs") == "true") {
                                var wrapper = SLI.jQuery(searchBox).closest(".sli_searchbox");
                                if (wrapper.length == 0) {
                                    wrapper = SLI.jQuery(searchBox).closest(".sli_searchform")
                                }
                                if (wrapper.length != 0) {
                                    var inputs = SLI.jQuery(wrapper).find('input[type="text"]:not([name="w"]), input[type="search"]:not([name="w"]), input[type="hidden"]');
                                    for (var i = 0; i < inputs.length; i++) {
                                        var value = inputs[i].value;
                                        var name = inputs[i].getAttribute("name");
                                        if (searchBox.attr("data-sli-non-empty-inputs") == "true") {
                                            value = value.replace(/^\s+|\s+$/g, "");
                                            if (value !== "") {
                                                extraParams += "&" + encodeURIComponent(name) + "=" + encodeURIComponent(value)
                                            }
                                        } else {
                                            extraParams += "&" + encodeURIComponent(name) + "=" + encodeURIComponent(value)
                                        }
                                    }
                                }
                            }
                            param.url = param.url + extraParams;
                            if (typeof SLI !== "undefined" && typeof SLI.ajaxSearchSubmit !== "undefined") {
                                SLI.ajaxSearchSubmit(param.url)
                            } else {
                                window.location = param.url
                            }
                        }
                    }
                    this.hide(this.input);
                    return true
                } else {
                    url = this.urls[i] + "&asug=" + encodeURIComponent(q.trim());
                    param = {
                        url: url,
                        query: q
                    };
                    if (url.indexOf("rt=racsug") >= 0) {
                        param.type = "suggestion"
                    } else {
                        if (url.indexOf("rt=racclick") >= 0) {
                            param.type = "product"
                        } else {
                            if (url.indexOf("rt=racscope") >= 0) {
                                param.type = "scope"
                            }
                        }
                    }
                    var item = this.items[i];
                    if (target && item && param.type != "product") {
                        var term = SLI.jQuery(item).find("[data-suggested-term]").attr("data-suggested-term");
                        if (term) {
                            param.query = normalise(term)
                        }
                    }
                    this.triggerEvent("select", param);
                    if (!param) {
                        return false
                    }
                    if (target && item) {
                        item = SLI.jQuery(item).find("." + SUGGESTION_CLASS);
                        if (item) {
                            target.value = normalise(SLI.jQuery(item).text())
                        }
                    }
                }
                this.hide(this.input);
                var redirectUrl = param.url + (param.url.match(/[#?]/) ? "&" : "?") + "apelog=yes";
                if (param.type !== "product" && typeof SLI !== "undefined" && typeof SLI.ajaxSearchSubmit === "function") {
                    SLI.ajaxSearchSubmit(redirectUrl)
                } else {
                    document.location = redirectUrl
                }
                return true
            },
            _mouseOver: function(index) {
                var current = this.currentSelected;
                if (index < 0 || index === current || !this.isVisible) {
                    return
                }
                this._select(index)
            },
            _mouseOut: function() {
                this.resetActive()
            },
            stopRequest: function() {
                if (this.request !== null) {
                    this.request.abort()
                }
            },
            _createSuggestionInput: function() {
                var input = this._getInput();
                if (!input || !input.el) {
                    return
                }
                var $input = SLI.jQuery(input.el),
                    $suggestionInput = $input.siblings("input.sli_suggested_word"),
                    notFound = ($suggestionInput.length === 0);
                if (notFound) {
                    $suggestionInput = SLI.jQuery('<input type="search" class="sli_suggested_word" style="display: none" disabled />')
                }
                if (notFound) {
                    $suggestionInput.insertAfter($input)
                }
            },
            _getInput: function() {
                if (this.input) {
                    return this.input
                }
                if (this.inputs && this.inputs.length) {
                    return this.inputs[0]
                }
                return undefined
            },
            _setPositionUnder: function($bottomElem, topElem) {
                if (!$bottomElem.length || !topElem) {
                    return
                }
                if (topElem.length) {
                    topElem = topElem[0]
                }
                var $topElem = SLI.jQuery(topElem);
                if (parseInt($topElem.css("z-index")) < 1) {
                    $topElem.addClass("sli_z_index_1")
                }
                var rect = topElem.getBoundingClientRect();
                $bottomElem.css({
                    height: rect.height + "px",
                    position: "absolute",
                    width: rect.width + "px",
                    background: $topElem.css("background"),
                    "font-size": $topElem.css("font-size"),
                    padding: $topElem.css("padding"),
                    display: "block",
                    "z-index": parseInt($topElem.css("z-index")) - 1
                });
                $topElem.css("background", "transparent");
                if ($topElem.parent().css("position") === "relative") {
                    $bottomElem.css({
                        left: 0,
                        top: 0
                    })
                } else {
                    var offset = $topElem.length == 0 ? 0 : $topElem.offset();
                    $bottomElem.css({
                        left: offset.left,
                        top: offset.top
                    })
                }
                return $bottomElem
            },
            _setSuggestionInput: function(suggestion, $input) {
                if (this.hasTouch) {
                    return
                }
                var placeholder = this.placeholder = this.placeholder || $input.attr("placeholder");
                suggestion = (this.screenSize() === "s") ? "" : suggestion;
                placeholder = (suggestion === "") ? placeholder : "";
                $input.siblings("input.sli_suggested_word").val(suggestion);
                $input.attr("placeholder", placeholder)
            },
            _completeWord: function(term, suggestion) {
                if (suggestion.toLowerCase().indexOf(term.toLowerCase()) !== 0) {
                    return ""
                }
                var completion = suggestion.substring(term.length);
                if (completion !== "") {
                    var uppers = term.match(/[A-Z]/g);
                    if (uppers && uppers.length / term.length > 0.5) {
                        completion = completion.toUpperCase()
                    }
                }
                return term + completion
            },
            addRACData: function(data) {
                var self = this;
                if (this.lastRequest !== data.requested) {
                    return
                }
                if (data.template !== "") {
                    if (data.productsOnly) {
                        SLI.jQuery("#sli_autocomplete .sli_ac_products").replaceWith(data.template);
                        if (data.announce !== "" && data.announce != undefined) {
                            SLI.jQuery("#sli_announce .sli_product_count").html(SLI.jQuery(data.announce).find(".sli_product_count"))
                        }
                    } else {
                        this.el.innerHTML = data.template;
                        if (data.announce !== "" && data.announce != undefined) {
                            SLI.jQuery("#sli_announce").html(SLI.jQuery(data.announce))
                        }
                        SLI.jQuery(".sli_ac_suggestions .sli_ac_suggestion a").each(function(index, suggestionEl) {
                            var childSpans = SLI.jQuery(suggestionEl).find("span");
                            if (childSpans.length > 0) {
                                childSpans.each(function(index2, spanEl) {
                                    var suggestions = SLI.jQuery(spanEl).text();
                                    SLI.jQuery(spanEl).html(highlightTerm(suggestions, self.input.el.value.trim().split(/\s+/)))
                                })
                            } else {
                                SLI.jQuery(suggestionEl).html(highlightTerm(SLI.jQuery(suggestionEl).text(), self.input.el.value.trim().split(/\s+/)))
                            }
                        })
                    }
                }
                if (typeof data.suggestion !== "undefined" && data.suggestion !== "") {
                    this.lastSuggestion = data.suggestion.toLowerCase()
                }
                this.setHover();
                this.items = this.el.querySelectorAll(this.selector);
                this.urls = SLI.jQuery(this.selector).map(function() {
                    return SLI.jQuery(this).find('a[data-role="main-link"]').attr("href") || SLI.jQuery(this).find("a").attr("href")
                });
                if (!this.canShowDynamic()) {
                    this.currentSelected = -1
                }
                SLI.jQuery(".sli_suggestion_arrow").on("click", function(e) {
                    e.stopPropagation();
                    var $suggestion = SLI.jQuery(this).closest("li").find("[data-suggested-term]");
                    if ($suggestion.length) {
                        SLI.jQuery(self.input.el).focus().val($suggestion.attr("data-suggested-term"));
                        self.input._onChange();
                        self.keepFocus = true
                    }
                });
                this.triggerEvent("updated", {
                    word: this.lastSuggestion,
                    suggestion: data.suggestion
                });
                this.displayRAC()
            },
            setHover: function() {
                var self = this;
                if (this.canShowDynamic()) {
                    SLI.jQuery(".sli_ac_suggestions li").on("mouseover", function(e) {
                        var target = e.target || e.srcElement;
                        self.lastIndex = SLI.jQuery(".sli_ac_suggestions li").index(SLI.jQuery(target).closest("li"));
                        if (!self.hoverTimeout) {
                            self.hoverTimeout = setTimeout(function() {
                                window.clearTimeout(self.hoverTimeout);
                                self.hoverTimeout = null;
                                if (self.currentSelected !== self.lastIndex) {
                                    self._mouseOver(self.lastIndex)
                                }
                            }, 500)
                        }
                    });
                    SLI.jQuery(".sli_ac_product").on("mouseover", function() {
                        var index = SLI.jQuery(".sli_ac_suggestions li").length + SLI.jQuery(".sli_ac_product").index(this);
                        self._select(index);
                        window.clearTimeout(self.hoverTimeout);
                        self.hoverTimeout = null
                    });
                    SLI.jQuery(".sli_ac_suggestion:not(." + this.activeClass + ")").on("click", function() {
                        var suggestions = Array.prototype.slice.call(document.querySelectorAll(".sli_ac_suggestions li"));
                        self._mouseOver(suggestions.indexOf(this));
                        window.clearTimeout(self.hoverTimeout);
                        self.hoverTimeout = null
                    })
                } else {
                    var $selector = SLI.jQuery(this.selector);
                    $selector.off("mouseover").on("mouseover", function() {
                        self._mouseOver($selector.index(this))
                    }).off("mouseout").on("mouseout", function() {
                        self._mouseOut.bind(self)
                    })
                }
            },
            hasRAC: function() {
                return false || (SLI.jQuery(this.el).find(".sli_ac_product").length && this.input && SLI.jQuery(this.input.el).val().length)
            },
            displayRAC: function() {
                if (typeof SLI !== "undefined" && typeof SLI.controller !== "undefined" && typeof SLI.controller.getCurrentPage !== "undefined") {
                    if (decodeURI(document.location.href) !== decodeURI(SLI.controller.getCurrentPage())) {
                        return
                    }
                }
                if (document.location.href !== SLI.currentWindowLocation && document.location.hash !== "") {
                    return
                }
                if (!this.hasRAC()) {
                    return
                }
                if (!this.el.children.length > 0) {
                    return
                }
                this.show();
                SLI.jQuery(".sli_loader").removeClass("sli_loading");
                this.valueStop = "";
                this.stopQuery = false;
                if (this.isStickToTop(SLI.jQuery(this.input.el).closest("form"))) {
                    window.scrollTo(0, 1)
                }
                SLI.jQuery(document).trigger({
                    type: "sli-rac",
                    message: "displayed"
                })
            },
            _updateSuggestion: function(suggestion) {
                var $input = SLI.jQuery(this.input.el),
                    term = $input.val(),
                    completion = "";
                if (term !== "" && typeof suggestion !== "undefined" && suggestion !== "") {
                    completion = this._completeWord(term, suggestion)
                }
                this._setSuggestionInput(completion, $input)
            },
            doRequest: function(val) {
                var noResultVal = this.valueStop || "";
                if (val.length > this.maxSearchLength) {
                    return false
                }
                if (val === this.value && this.hasRAC()) {
                    this.displayRAC();
                    return false
                }
                if (this.stopQuery && val.length > noResultVal.length && val.substr(0, noResultVal.length) == noResultVal) {
                    return false
                }
                return true
            },
            outOfFocus: function(input) {
                if (this.keepFocus) {
                    this.keepFocus = false;
                    return
                }
                this.focus = false;
                this.hide(input);
                this.resetActive();
                this.resetStickToTop(SLI.jQuery(input.el).closest("form"))
            },
            getScope: function() {
                if (this.currentSelected >= 0) {
                    var $suggestion = SLI.jQuery(this.items[this.currentSelected]).find("[data-suggested-term]");
                    return $suggestion.find(".sli_scope").text()
                }
            },
            fireRequest: function(word, extraHref) {
                var keyword = word ? word : this.value;
                var url = this.base;
                if (extraHref == "recentOnly=1") {
                    var terms = SLI.recentSearches.get().join(encodeURIComponent(SLI.recentSearches.separator));
                    url = url.replace("&w=", "") + "&rt=rac&dv=" + this.size + "&sli-rs-value=" + terms
                } else {
                    url = url + encodeURIComponent(keyword) + "&rt=rac&dv=" + this.size
                }
                var self = this;
                SLI.jQuery.each(this.behaviourOptions, function(key, value) {
                    if (key === "productLabel") {
                        SLI.jQuery(".sli_ac_products .sli_ac_section").html(value)
                    }
                    url += "&" + encodeURIComponent(key) + "=" + encodeURIComponent(value)
                });
                if (extraHref) {
                    url += "&" + extraHref
                }
                url = this.preRequest ? this.preRequest(url) : url;
                var thisRequest = generateHash(url).toString();
                url += "&requested=" + thisRequest;
                if (SLI.config && SLI.config.ajax && typeof(SLI.config.ajax.enabled) !== "undefined" && SLI.config.ajax.enabled && SLI.config.ajax.lnpath === "NoAjaxOnThisPage") {
                    url += "&doAjax=true"
                }
                if (thisRequest !== this.lastRequest) {
                    this.stopRequest();
                    this.lastRequest = thisRequest;
                    SLI.jQuery(".sli_loader").addClass("sli_loading");
                    this.request = new QueryRequest(url, this)
                } else {
                    this.displayRAC()
                }
            },
            resetActive: function() {
                var current = this.currentSelected;
                var items = this.items;
                if (current >= 0 && current < items.length) {
                    SLI.jQuery(items[current]).removeClass(this.activeClass);
                    var array = document.querySelectorAll(this.searchboxAttr);
                    for (var i = 0; i < array.length; i++) {
                        array[i].setAttribute("aria-activedescendant", "")
                    }
                }
                this.currentSelected = -1
            },
            valueChanged: function(input, value) {
                this.setInput(input);
                if (!this.doRequest(value)) {
                    return
                }
                this.value = value;
                if (value.length === 0) {
                    this.stopRequest();
                    this.hide(input);
                    if (!this.hasRAC()) {
                        this.resetStickToTop(SLI.jQuery(input.el).closest("form"))
                    }
                } else {
                    this.fireRequest();
                    this.resetActive()
                }
            },
            inFocus: function(input) {
                this.resetActive();
                this.focus = true;
                if (input !== this.input) {
                    this.hide(this.input);
                    this.setInput(input)
                } else {
                    this.displayRAC();
                    SLI.currentWindowLocation = document.location.href
                }
                if (this.input.el.value !== "") {
                    this.fireRequest(this.input.el.value)
                }
            },
            checkScreenSize: function() {
                var size = this.screenSize();
                if (size !== this.size) {
                    this.size = size;
                    if (this.isVisible && this.value && this.value.length > 0) {
                        this.fireRequest()
                    }
                }
            },
            screenSize: function() {
                var bp = this.breakpoints;
                return (getWidth() < bp.small.maxWidth) ? bp.small.value : bp.other.value
            },
            resetStickToTop: function($form) {
                if (!this.isStickToTop($form)) {
                    return
                }
                SLI.jQuery("body").removeClass("sli_full_width");
                SLI.jQuery(this.el).removeClass("sli_is_full_width");
                $form.removeClass("sli_sticky sli_sticky_full_width");
                window.scrollTo(0, 0)
            },
            isStickToTop: function($elem) {
                return $elem.is(".sli_sticky, .sli_sticky_full_width")
            },
            canStickToTop: function() {
                return this.hasTouch && this.focus && this.hasRAC()
            },
            stickToTop: function($form) {
                if (!false || this.isStickToTop($form) || !this.canStickToTop()) {
                    return
                }
                SLI.jQuery(this.el).removeClass("sli_is_full_width");
                $form.removeClass("sli_sticky sli_sticky_full_width");
                if (this.screenSize() === "s") {
                    SLI.jQuery("body").addClass("sli_full_width");
                    SLI.jQuery(this.el).addClass("sli_is_full_width");
                    $form.addClass("sli_sticky_full_width")
                } else {
                    $form.addClass("sli_sticky")
                }
                window.scrollTo(0, 1)
            },
            isTablet: function() {
                return this.screenSize() !== "s" && this.hasTouch
            },
            scrollToSearchBox: function() {
                if (!this.input) {
                    return
                }
                this.resetStickToTop(SLI.jQuery(this.input.el).closest("form"));
                var body = document.body;
                var rect = this.input.el.getBoundingClientRect();
                window.scrollTo(rect.left + body.scrollLeft, rect.top + body.scrollTop)
            },
            checkDevice: function() {
                this.hasTouch = (("ontouchstart" in window) || window.DocumentTouch && document instanceof DocumentTouch);
                if (this.hasTouch) {
                    var $elems = [SLI.jQuery(this.searchboxAttr).closest("form"), SLI.jQuery(this.el)];
                    for (var i = 0, len = $elems.length; i < len; i++) {
                        if (!$elems[i].hasClass("sli_has_touch")) {
                            $elems[i].addClass("sli_has_touch")
                        }
                    }
                }
                var isSafari = /constructor/i.test(window.HTMLElement);
                var $body = SLI.jQuery("body");
                if (isSafari && !$body.hasClass("sli_is_safari")) {
                    $body.addClass("sli_is_safari")
                }
            },
            triggerEvent: function(msg, data) {
                SLI.jQuery(document).trigger({
                    type: "sli-rac-event",
                    message: msg,
                    racData: data
                })
            }
        };

        function Input(el, autocomplete, options) {
            var self = this;
            this.el = el;
            this.autocomplete = autocomplete;
            if (options && options.delay) {
                this.delay = options.delay
            }
            el.setAttribute("autocomplete", "off");
            this.isFixed = el.getAttribute("data-sli-position") === "fixed";
            if (el.hasAttribute("data-sli-width")) {
                this.width = el.getAttribute("data-sli-width")
            }
            var proxiedBlur = this._onBlur.bind(this);
            var delay = this.delay + 300;
            var searchboxSelector = '[data-rac-input="' + self.el.getAttribute("data-rac-input") + '"]';
            SLI.jQuery("body").on("blur", searchboxSelector, function(e) {
                if (e.type == "focusout") {
                    self.keepFocus = true
                } else {
                    setTimeout(proxiedBlur, delay)
                }
            }).on("focus", searchboxSelector, function(e) {
                self.el = e.target;
                self._onFocus.call(self)
            }).on("keydown", searchboxSelector, self._onKeyDown.bind(self)).on("keyup", searchboxSelector, self._onKeyUp.bind(self)).on("compositionupdate", searchboxSelector, self._onCompositionUpdate.bind(self)).on("compositionend", searchboxSelector, self._onCompositionEnd.bind(self)).on("search", searchboxSelector, self._onClear.bind(self))
        }
        Input.prototype = {
            delay: 150,
            isFixed: false,
            lastValue: "",
            timeout: null,
            isCompositionEnd: false,
            blockSubmit: false,
            _onChange: function(value) {
                this._onFocus();
                if (!value || typeof(value) !== "string") {
                    value = this.el.value
                }
                this.autocomplete.valueChanged(this, normalise(value))
            },
            _onBlur: function() {
                this.autocomplete.outOfFocus(this)
            },
            _onFocus: function() {
                this.autocomplete.inFocus(this)
            },
            _onKeyPress: function() {
                if (this.blockSubmit) {
                    this.blockSubmit = false;
                    if (this.autocomplete.isVisible) {
                        return false
                    }
                }
            },
            _onKeyDown: function(event) {
                var el = this.el;
                var autocomplete = this.autocomplete;
                autocomplete.focus = true;
                this.blockSubmit = false;
                var key = event.which !== null ? event.which : event.charCode !== null ? event.charCode : event.keyCode;
                switch (key) {
                    case keyCode.UP:
                        event.preventDefault();
                        if (autocomplete.isVisible) {
                            autocomplete.prev()
                        } else {
                            this._onChange()
                        }
                        break;
                    case keyCode.DOWN:
                        event.preventDefault();
                        if (autocomplete.isVisible) {
                            autocomplete.next()
                        } else {
                            this._onChange()
                        }
                        break;
                    case keyCode.PAGEUP:
                        event.preventDefault();
                        if (autocomplete.isVisible) {
                            autocomplete.pageUp()
                        } else {
                            this._onChange()
                        }
                        break;
                    case keyCode.PAGEDOWN:
                        event.preventDefault();
                        if (autocomplete.isVisible) {
                            autocomplete.pageDown()
                        } else {
                            this._onChange()
                        }
                        break;
                    case keyCode.LEFT:
                    case keyCode.RIGHT:
                        break;
                    case keyCode.END:
                    case keyCode.TAB:
                        var completion = SLI.jQuery(el).siblings("input.sli_suggested_word").val();
                        if (completion && completion !== "" && SLI.jQuery(el).val() != completion) {
                            event.preventDefault();
                            SLI.jQuery(el).val(completion);
                            this._onChange()
                        } else {
                            this._onClear()
                        }
                        break;
                    case keyCode.RETURN:
                        event.preventDefault();
                        if (autocomplete.selectCurrent(event)) {
                            this.blockSubmit = false;
                            return false
                        }
                        autocomplete.stopRequest();
                        if (this.timeout !== null) {
                            clearTimeout(this.timeout)
                        }
                        this.lastValue = el.value;
                        if (!this.isCompositionEnd) {
                            autocomplete.hide(this)
                        }
                        break;
                    case keyCode.ESC:
                        autocomplete.hide(this);
                        break;
                    default:
                        autocomplete.resetActive();
                        clearTimeout(this.timeout);
                        break
                }
                this.lastValue = el.value
            },
            _onKeyUp: function(event) {
                var key = event.which !== null ? event.which : event.charCode !== null ? event.charCode : event.keyCode;
                switch (key) {
                    case keyCode.ESC:
                    case keyCode.RETURN:
                        if (!this.isCompositionEnd) {
                            this.autocomplete.hide(this)
                        } else {
                            this.isCompositionEnd = false
                        }
                        break;
                    default:
                        clearTimeout(this.timeout);
                        if (this.el.value !== this.lastValue) {
                            this.timeout = setTimeout(this._onChange.bind(this), this.delay)
                        }
                        break
                }
            },
            _onClear: function(event) {
                this.hideKeyboard();
                this.autocomplete.keepFocus = false;
                this._onBlur();
                var $closeArea = SLI.jQuery(".js_sli_close_area");
                if ($closeArea.length) {
                    $closeArea.removeClass("sli_is_hidden").addClass("sli_is_hidden")
                }
            },
            hideKeyboard: function() {
                if (!this.autocomplete.hasTouch) {
                    return
                }
                var element = SLI.jQuery(this.el);
                element.attr("readonly", "readonly");
                setTimeout(function() {
                    element.blur();
                    element.removeAttr("readonly")
                }, 100)
            },
            _onCompositionUpdate: function(event) {
                var value = event.data;
                if (value !== this.lastValue) {
                    clearTimeout(this.timeout);
                    this.timeout = setTimeout(this._onChange.bind(this, value), this.delay)
                }
            },
            _onCompositionEnd: function() {
                this.isCompositionEnd = true
            }
        };
        SLI.Autocomplete = Autocomplete
    }
})(window, document, SLI.jQuery || jQuery);
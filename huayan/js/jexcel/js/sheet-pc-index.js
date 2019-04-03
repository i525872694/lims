window.jExcelID || (window.jExcelID = '');
window.jExcelDiv ||(window.jExcelDiv = $('#'+jExcelID));
window.jExcelDiv ||(window.jExcelDiv = $(window));
window.gEditable || (window.gEditable = true);
window.immTableSheet || (window.immTableSheet = {});
window.showDialog = function(e, t, n) {
    $.Custom_Dialog(t, {
        type: n,
        title: e
    })
}
var padeditbar_utils = function() {
    var e = {
        sheetButtonClick: function(t, n) {
            if (immTableSheet && immTableSheet.jExcel) {
                var o = immTableSheet.jExcel,
                    a = $(o.div).jexcel("getSelectedRange");
                    console.log(t)
                if (a) {
                      l = a.from.row
                      , s = a.from.col;
                    if ("sheet-bold" == t || "sheet-italic" == t || "sheet-underline" == t || "sheet-strike" == t || "sheet-merge" == t || "sheet-textwrap" == t) {
                        var u = e.getSheetStyleState();
                        "sheet-bold" == t ? t = u && "bold" === u.bold ? "sheet-bold-normal" : "sheet-bold-bold" : "sheet-italic" == t ? t = u && "italic" === u.italic ? "sheet-italic-normal" : "sheet-italic-italic" : "sheet-underline" == t ? t = u && "underline" === u.underline ? "sheet-underline-none" : "sheet-underline-underline" : "sheet-strike" == t ? t = u && "strikeline" === u.strikeline ? "sheet-strike-none" : "sheet-strike-strike" : "sheet-textwrap" === t && (t = u && "nowrap" === u.textwrap ? "sheet-textwrap-normal" : "sheet-textwrap-nowrap")
                    }else if("sheet-insert-link" == t){
                        t += !!linkViewer && linkViewer.isShow() ? "-close" : "-open";
                    }
                    if ("sheet-formula-sum" == t || "sheet-formula-count" == t || "sheet-formula-average" == t || "sheet-formula-max" == t || "sheet-formula-min" == t || "sheet-formula-stock" == t) {
                        // var c = t.replace(/sheet-formula-/, "");
                        // a.isSingle() || e.isSingleMergeCell() ? immTableSheet.sheetHot.canvasView.excel.insertFormula({
                        //     formula: c
                        // }) : (pad_editbar_formula_utils.init(),
                        // pad_editbar_formula_utils.selHandler(a, t));
                        return void showDialog("出现问题", "暂不能插入计算公式。");
                    } else if ("sheet-format-painter" === t)
                        immTableSheet.sheetHot.actionCenter.isPainting ? toolbarController.uncheckFormatPainter() : (toolbarController.checkFormatPainter(),
                        immTableSheet.sendCmd(a, t, n));
                    else if ("sheet-decimal-increase" === t || "sheet-decimal-decrease" === t) {
                        immTableSheet.sendCmd(a, t, n);
                    } else{
                        immTableSheet.sendCmd(a, t, n);
                    }
                }
            }else{
                padutils.showToolToast("请选择要操作的单元格!", {zIndex: 99999}, {toolToastCloseTime: 3e3});
            }
        },
        sheetButtonDblClick: function(e) {
            if (immTableSheet) {
                var t = window.sheetNode;
                if (t) {
                    t.getHotInstance();
                    switch (e) {
                    case "sheet-format-painter":
                        immTableSheet.actionCenter.painter(!0),
                        updateUndoRedoButtonState(),
                        toolbarController.checkFormatPainter()
                    }
                }
            }
        },
        sheetButtonClickWidthData: function(e, t) {
            var n = immTableSheet;
            n && n.sendCmd(a, e, t)
        },
        getSheetStyleState: function() {
            return immTableSheet.sheetHot.getCellStyle();
        },
        noInstance: 0,
        hasMerge: 1,
        hasNotMerge: -1,
        hasMergeCellsInSelectedRange: function(e) {
            if(!window.immTableSheet.jExcel) return false;
            var t = window.immTableSheet;
            if (!t) return this.noInstance;
            var n, r = t.getHotInstance(),
            o = r.getSelectedRange();
            if (r.mergeCells && (n = r.mergeCells.mergedCellInfoCollection), !n) return this.hasNotMerge;
            if (e) {
                var i = o.getTopLeftCorner(),
                a = o.getBottomRightCorner();
                for (l = 0, u = n.length; l < u; l++) {
                    var s = n[l];
                    if (s.row >= i.row && s.row <= a.row && s.col >= i.col && s.col <= a.col) return this.hasMerge
                }
                return this.hasNotMerge
            }
            if (n.length > 0) {
                var l, u, c = r.countRows(),
                f = r.countCols();
                for (l = 0, u = n.length; l < u; l++) if (n[l].row >= 0 && n[l].row < c && n[l].col >= 0 && n[l].col < f) return this.hasMerge;
                return this.hasNotMerge
            }
            return this.hasNotMerge
        },
        isSingleCell: function() {
            if(!window.immTableSheet.jExcel) return false;
            var n = $(immTableSheet.sheetHot.div).jexcel("getSelectedRange");
            return ! n || n.isSingle();
        },
        isSingleMergeCell: function() {
            if(!window.immTableSheet.jExcel) return false;
            var r = $(immTableSheet.sheetHot.div).jexcel("getSelectedRange");
            if (!r) return ! 0;
            return ! r || r.isSingleMergeCell()
        }
    };
    return e
} ()

padutils = {
    validUrlRe: new RegExp("^(?:https?|sftp|ftps?|ssh|ircs?|file|gopher|telnet|nntp|worldwind|chrome|chrome-extension|svn|git|mms|smb|afp|nfs|(x-)?man|gopher|txmt)://|^mailto:|^xmpp:|^sips?:|^tel:|^sms:|^news:|^bitcoin:|^magnet:|^urn:|^geo:|^/", "i"),
    escapeHtml: function(e) {
        var t = /[&<>'"]/g;
        return t.MAP || (t.MAP = {
            "&": "&amp;",
            "<": "&lt;",
            ">": "&gt;",
            '"': "&#34;",
            "'": "&#39;"
        }),
        e.replace(t,
        function(e) {
            return t.MAP[e]
        })
    },
    uniqueId: function() {
        function e(e, t) {
            return (Array(t + 1).join("0") + Number(e).toString(35)).slice( - t)
        }
        return [pad.getClientIp(), e( + new Date, 7), e(Math.floor(1e9 * Math.random()), 4)].join(".")
    },
    getLength: function(e) {
        for (var t = 0,
        n = 0; n < e.length; n++) {
            t += e.charCodeAt(n) <= 255 ? 1 : 2
        }
        return t
    },
    splitString: function(e, t) {
        var n = "",
        o = 0;
        if (padutils.getLength(e) <= t) n = e;
        else for (var i = 0; i < e.length; i++) {
            var r = e.charAt(i);
            if ((o += e.charCodeAt(i) <= 255 ? 1 : 2) > t) {
                n += "...";
                break
            }
            n += r
        }
        return n
    },
    getIsMobile: function() {
        return - 1 != navigator.userAgent.toLowerCase().indexOf("iphone") || -1 != navigator.userAgent.toLowerCase().indexOf("ipad") || -1 != navigator.userAgent.toLowerCase().indexOf("android") || -1 != navigator.userAgent.toLowerCase().indexOf("ipod")
    },
    uaDisplay: function(e) {
        var t;
        function n(e) {
            return (e = e.replace(/[^a-zA-Z0-9\.]/g, "")).length > 16 && (e = e.substr(0, 16)),
            e
        }
        function o(t) {
            var o = e.match(RegExp(t + "\\/([\\d\\.]+)"));
            return o && o.length > 1 ? n(t + o[1]) : null
        }
        if (o("Firefox")) return o("Firefox");
        if ((t = e.match(/compatible; ([^;]+);/)) && t.length > 1) return n(t[1]);
        if (e.match(/\(iPhone;/)) return "iPhone";
        if (o("Chrome")) return o("Chrome");
        if (t = e.match(/Safari\/[\d\.]+/)) {
            var i = "?";
            return (t = e.match(/Version\/([\d\.]+)/)) && t.length > 1 && (i = t[1]),
            n("Safari" + i)
        }
        return n(e.split(" ")[0])
    },
    binarySearch: function(e, t) {
        if (e < 1) return 0;
        if (t(0)) return 0;
        if (!t(e - 1)) return e;
        for (var n = 0,
        o = e - 1; o - n > 1;) {
            var i = Math.floor((n + o) / 2);
            t(i) ? o = i: n = i
        }
        return o
    },
    simpleDateTime: function(e) {
        var t = new Date( + e);
        return ["Sun", "Mon", "Tue", "Wed", "Thu", "Fri", "Sat"][t.getDay()] + " " + ["Jan", "Feb", "Mar", "Apr", "May", "Jun", "Jul", "Aug", "Sep", "Oct", "Nov", "Dec"][t.getMonth()] + " " + t.getDate() + " " + t.getFullYear() + " " + (t.getHours() + ":" + ("0" + t.getMinutes()).slice( - 2))
    },
    findURLs: function(e) {
        var t = new RegExp("(" + /[-:@a-zA-Z0-9_.,~%+\/?=&#;()$]/.source + "|" + /[\u0030-\u0039\u0041-\u005A\u0061-\u007A\u00C0-\u00D6\u00D8-\u00F6\u00F8-\u00FF\u0100-\u1FFF\u3040-\u9FFF\uF900-\uFDFF\uFE70-\uFEFE\uFF10-\uFF19\uFF21-\uFF3A\uFF41-\uFF5A\uFF66-\uFFDC]/.source + ")"),
        n = new RegExp(/(?:(?:https?|sftp|ftps?|ssh|ircs?|file|gopher|telnet|nntp|worldwind|chrome|chrome-extension|svn|git|mms|smb|afp|nfs|(x-)?man|gopher|txmt):\/\/|mailto:|xmpp:|sips?:|tel:|sms:|news:|bitcoin:|magnet:|urn:|geo:)/.source + t.source + "*(?![:.,;])" + t.source, "g");
        return function(e) {
            n.lastIndex = 0;
            for (var t, o = null; t = n.exec(e);) {
                o = o || [];
                var i = t.index,
                r = t[0];
                o.push([i, r])
            }
            return o
        } (e)
    },
    isWhitelistUrlScheme: function(e) {
        return padutils.validUrlRe.test(e)
    },
    escapeHtmlWithClickableLinks: function(e, t) {
        var n = 0,
        o = [],
        i = padutils.findURLs(e);
        function r(t) {
            t > n && (o.push(padutils.escapeHtml(e.substring(n, t))), n = t)
        }
        if (i) for (var a = 0; a < i.length; a++) {
            var s = i[a][0],
            c = i[a][1];
            r(s),
            o.push("<a ", t ? 'target="' + t + '" ': "", 'href="', c.replace(/\"/g, "&quot;"), '">'),
            r(s + c.length),
            o.push("</a>")
        }
        return r(e.length),
        o.join("")
    },
    bindEnterAndEscape: function(e, t, n) {
        t && e.keypress(function(e) {
            13 == e.which && t(e)
        }),
        n && e.keydown(function(e) {
            27 == e.which && n(e)
        })
    },
    timediff: function(e) {
        function t(e, t) {
            return (e = Math.round(e)) + " " + t + (1 != e ? "s": "") + " ago"
        }
        return (e = Math.max(0, ( + new Date - +e - pad.clientTimeOffset) / 1e3)) < 60 ? t(e, "second") : (e /= 60) < 60 ? t(e, "minute") : (e /= 60) < 24 ? t(e, "hour") : t(e /= 24, "day")
    },
    makeAnimationScheduler: function(e, t, n) {
        void 0 === n && (n = 1);
        var o = null;
        return {
            scheduleAnimation: function i() {
                o || (o = window.setTimeout(function() {
                    o = null;
                    for (var t = n,
                    r = !0; r && t > 0;) r = e(),
                    t--;
                    r && i()
                },
                t * n))
            }
        }
    },
    makeShowHideAnimator: function(e, t, n, o) {
        var i = t ? 0 : -2,
        r = 1e3 / n,
        a = r / o,
        s = padutils.makeAnimationScheduler(function() {
            if (i < -1 || 0 == i) return ! 1;
            if (i < 0) return (i += a) >= 0 ? (e(i = 0), !1) : (e(i), !0);
            if (i > 0) return (i += a) >= 1 ? (e(i = 1), i = -2, !1) : (e(i), !0)
        },
        r).scheduleAnimation;
        return {
            show: function() {
                e(i = -1),
                s()
            },
            hide: function() {
                i >= -1 && i <= 0 && (i = 1e-6, s())
            },
            quickShow: function() {
                i = i < -1 ? -1 : i <= 0 ? i: Math.max( - 1, Math.min(0, -i)),
                e(i),
                s()
            }
        }
    },
    _nextActionId: 1,
    uncanceledActions: {},
    getCancellableAction: function(e, t) {
        var n = padutils.uncanceledActions[e];
        n || (n = {},
        padutils.uncanceledActions[e] = n);
        var o = padutils._nextActionId++;
        return n[o] = !0,
        function() {
            var n = padutils.uncanceledActions[e];
            n && n[o] && t()
        }
    },
    cancelActions: function(e) {
        padutils.uncanceledActions[e] && delete padutils.uncanceledActions[e]
    },
    makeFieldLabeledWhenEmpty: function(e, t) {
        function n() {
            e.addClass("editempty"),
            e.val(t)
        }
        return (e = $(e)).focus(function() {
            e.hasClass("editempty") && e.val(""),
            e.removeClass("editempty")
        }),
        e.blur(function() {
            e.val() || n()
        }),
        {
            clear: n
        }
    },
    getCheckbox: function(e) {
        return $(e).is(":checked")
    },
    setCheckbox: function(e, t) {
        t ? $(e).attr("checked", "checked") : $(e).removeAttr("checked")
    },
    bindCheckboxChange: function(e, t) {
        $(e).bind("click change", t)
    },
    encodeUserId: function(e) {
        return e.replace(/[^a-y0-9]/g,
        function(e) {
            return "." == e ? "-": "z" + e.charCodeAt(0) + "z"
        })
    },
    decodeUserId: function(e) {
        return e.replace(/[a-y0-9]+|-|z.+?z/g,
        function(e) {
            return "-" == e ? ".": "z" == e.charAt(0) ? String.fromCharCode(Number(e.slice(1, -1))) : e
        })
    },
    setToolToastCss: function(e) {
        var t = $("#toolToast");
        t.length > 0 && e && $.each(e,
        function(e, n) {
            t.css(e, n)
        })
    },
    hideToolToast: function(e, t, n) {
        t || (t = $("#toolToastWrap:last")),
        n && (t.css({
            transition: "transform 0.4s ease-out",
            transform: "translateY(-44px)"
        }), setTimeout(function() {
            t.remove(),
            0 == $("#toolToast:last").filter(":visible").length && $("#toolToast:last").show(),
            e && e()
        },
        450)),
        setTimeout(function() {
            t.remove(),
            0 == $("#toolToast:last").filter(":visible").length && $("#toolToast:last").show(),
            e && e()
        },
        200)
    },
    toolToast: function(e, t, n, o, i) {
        var r, a, s = !1;
        if (n && n.standardToastType && (n.toolToastCloseTime = 3e3, n.supportMobile = !0), n && n.supportMobile && (s = !0, t || (t = {}), t)) {
            if (!t.top) try {
                t.top = $("header").height() + $("header").offset().top + $(window).scrollTop() + 10
            } catch(e) {
                console.log("showTooltoast mobile fail")
            }
            t["background-color"] || (t["background-color"] = "rgba(0,0,0,0.75)"),
            t.color || (t.color = "#ffffff"),
            t["padding-left"] || (t["padding-left"] = "10px"),
            t["padding-right"] || (t["padding-right"] = "10px")
        }
        if (padutils.hideToolToast(n && n.toolToastRemoveCallBack ? n.toolToastRemoveCallBack: void 0), a = o && i ? o: $("body"), "object" != typeof t && (t = {}), !i && !s) {
            delete t.top,
            delete t.left,
            delete t.right,
            delete t.bottom,
            delete t.width,
            delete t.height,
            delete t["max-width"],
            delete t["max-height"],
            delete t.position,
            delete t.transform;
            try {
                t.top = $("header").height() + $("header").offset().top
            } catch(e) {
                console.log("showTooltoast fail")
            }
        }
        var c = $('<div id="toolToastWrap">').prependTo(a);
        c.css({
            left: "20px",
            right: "30px",
            borderSizing: "border-box",
            display: "none",
            position: "fixed",
            zIndex: 10001
        }),
        s || c.css(t);
        var l = $("<div id='toolToast' class='toolToast'>").prependTo(c),
        d = $('<span id="tool-toast-content"></span>').html(e).prependTo(l);
        if (s && (t && l.css(t), c.css("right", "20px")), n && n.isNeedBtn) {
            var p = $('<span id="toolToastBtn"></span>').html(n.btnText);
            d.append(p);
            var u = n.btnTextAfter;
            if (u) {
                var h = $('<span id="toolToastBtnAfter"></span').html(u);
                d.append(h)
            }
            p.click(function() {
                n.btnCallBack && n.btnCallBack()
            })
        }
        if (n && n.isNeedClose) {
            var f = $("<div class='toast-colse-white'>"),
            g = $("<div class='toast-colse-white-img'>");
            f.append(g),
            l.append(f),
            l.css("padding-right", 11),
            f.click(function() {
                padutils.hideToolToast(n && n.toolToastRemoveCallBack ? n.toolToastRemoveCallBack: void 0)
            })
        } else if (n && n.isWarnning) {
            var m = $("<div class='toast-mark-white'>");
            l.prepend(m),
            l.css("background-color", "#ff7d6f"),
            l.css("color", "white"),
            l.css("padding-left", 20),
            l.css("padding-right", 11);
            var w = $("<div class='toast-colse-white'>");
            g = $("<div class='toast-colse-white-img'>");
            w.append(g),
            l.append(w),
            w.click(function() {
                padutils.hideToolToast(n && n.toolToastRemoveCallBack ? n.toolToastRemoveCallBack: void 0)
            })
        }
        var b = n && n.toastType ? n.toastType: null;
        if (b && ($("#tool-toast-content").after('<span id="tool-toast-content-link" class="toast-link"></span>'), $("#tool-toast-content-link").after('<span id="tool-toast-content-tail"></span>'), $("#tool-toast-content").css("margin-top", "-1px"), $("#tool-toast-content-link").css("margin-top", "-1px"), $("#tool-toast-content-tail").css("margin-top", "-1px")), b && "typeNotice" === b) {
            m = $("<div class='toast-icon-contain'>");
            var v = $("<img class='toast-type-icon' src='//s1.url.cn/tim/docs/static/img/newpic/tool_toast_notice_icon-0bd59c6976.png'>");
            m.append(v),
            l.prepend(m),
            l.css("background-color", "rgba(0,0,0,0.75)"),
            l.css("color", "white"),
            l.css("padding-left", 20),
            l.css("padding-right", 20)
        } else if (b && "typeSuccess" === b) {
            m = $("<div class='toast-icon-contain'>"),
            v = $("<img class='toast-type-icon' src='//s1.url.cn/tim/docs/static/img/newpic/tool_toast_suc_icon-06d1fb430e.png'>");
            m.append(v),
            l.prepend(m),
            l.css("background-color", "rgba(0,0,0,0.75)"),
            l.css("color", "white"),
            l.css("padding-left", 20),
            l.css("padding-right", 20)
        } else if (b && "typeWarnning" === b) {
            m = $("<div class='toast-icon-contain'>"),
            v = $("<img class='toast-type-icon' src='//s1.url.cn/tim/docs/static/img/newpic/tool_toast_warnning_icon-ff85144c64.png'>");
            m.append(v),
            l.prepend(m),
            l.css("background-color", "#ff7d6f"),
            l.css("color", "white"),
            l.css("padding-left", 20),
            l.css("padding-right", 20)
        }
        if (n && n.supportClose) {
            w = $("<div class='toast-close-contain'>");
            var y = $("<img id='toast-icon-close' class='toast-icon-close' src='//s1.url.cn/tim/docs/static/img/newpic/tool_toast_close_icon-420be9c9dc.png'>");
            w.css("mar-left", 20),
            w.append(y),
            l.append(w),
            l.css("padding-right", 11),
            y.click(function() {
                padutils.hideToolToast(n && n.toolToastRemoveCallBack ? n.toolToastRemoveCallBack: void 0)
            }),
            padutils.btnState($("#toast-icon-close"), "toast-icon-close-normal", "toast-icon-close-hover", "toast-icon-close-pressed")
        }
        if (n && n.linkTitle) {
            var C, T, x = "",
            S = e.lastIndexOf(n.linkTitle);
            C = e.substring(0, S),
            T = e.substring(S, S + n.linkTitle.length),
            S + n.linkTitle.length < e.length && (x = e.substring(S + n.linkTitle.length)),
            $("#tool-toast-content").html(C),
            $("#tool-toast-content-link").html(T),
            $("#tool-toast-content-tail").html(x),
            $("#tool-toast-content-link").click(function() {
                padutils.hideToolToast(n && n.toolToastRemoveCallBack ? n.toolToastRemoveCallBack: void 0, c),
                n && n.linkCallback()
            })
        }
        if (n && n.supportCountDown) {
            var I = n && n.countDownSecond ? n.countDownSecond: 5;
            $("#tool-toast-content-tail").after('<span id="tool-toast-countdown-head">(</span>'),
            $("#tool-toast-countdown-head").after('<span id="tool-toast-countdown">' + I + "</span>"),
            $("#tool-toast-countdown").after('<span id="tool-toast-countdown-tail">S)</span>'),
            setInterval(function() {
                var e = $("#tool-toast-countdown").text();
                0 == --e ? n && n.countDownCallback && n.countDownCallback() : $("#tool-toast-countdown").text(e)
            },
            1e3)
        }
        n && n.standardToastType && this.setMobileStandardTotalCss(n.standardToastType),
        c.show(),
        l.show(),
        n && n.toolToastCloseTime && parseFloat(n.toolToastCloseTime) && setTimeout(function() {
            padutils.hideToolToast(n && n.toolToastRemoveCallBack ? n.toolToastRemoveCallBack: void 0, c)
        },
        parseFloat(n.toolToastCloseTime))
    },
    toolToastForMobile: function(e, t, n, o, i) {
        var r;
        console.log("padutils: toolToastForMobile"),
        padutils.hideToolToast(n && n.toolToastRemoveCallBack ? n.toolToastRemoveCallBack: void 0),
        r = o && i ? o: $("body"),
        "object" != typeof t && (t = {});
        var a = $('<div id="toolToastWrap">').prependTo(r);
        a.css({
            borderSizing: "border-box",
            display: "none",
            position: "absolute",
            top: "0px",
            left: "0px",
            zIndex: 10001
        }),
        a.css(t);
        var s = $("<div id='toolToast'>").prependTo(a);
        s.css("position", "relative"),
        s.css("left", "0px"),
        s.css("top", "0px"),
        s.css("padding", "15px"),
        s.css("border-radius", "0px"),
        s.css("width", document.body.clientWidth + "px"),
        s.css("padding-left", 12),
        s.css("padding-right", 12),
        s.css("white-spacing", "nowrap"),
        $('<div id="tool-toast-content" class="tool-toast-content-mobile"></div>').prependTo(s),
        $("#tool-toast-content").css("max-width", document.body.clientWidth - 52 + "px"),
        console.log("toolToast: screenWidth[" + document.body.clientWidth + "]");
        var c = n && n.toastType ? n.toastType: null;
        if (c && "typeNotice" === c) {
            var l = $("<div class='toast-icon-contain-mobile-notice'>");
            $("<img class = toast-mark-icon-mobile>");
            s.prepend(l),
            s.css("background-color", "#ffffff")
        }
        if (c && "typeNotice2" === c) {
            l = $("<div class='toast-icon-contain-mobile-notice2'>"),
            $("<img class = toast-mark-icon-mobile>");
            s.prepend(l),
            s.css("background-color", "rgba(0,0,0,0.80)"),
            s.css("color", "white"),
            $("#tool-toast-content").css("color", "white")
        } else if (c && "typeSuccess" === c) {
            l = $("<div class='toast-icon-contain-mobile-success'>"),
            $("<img class = toast-mark-icon-mobile>");
            s.prepend(l),
            s.css("background-color", "#ffffff"),
            s.css("color", "white")
        } else if (c && "typeWarnning" === c) {
            l = $("<div class='toast-icon-contain-mobile-warnning'>"),
            $("<img class = toast-mark-icon-mobile>");
            s.prepend(l),
            s.css("background-color", "#ff7d6f"),
            s.css("color", "white"),
            $("#tool-toast-content").css("color", "white")
        }
        if (n && n.linkTitle) {
            var d, p, u = "",
            h = e.lastIndexOf(n.linkTitle);
            d = e.substring(0, h),
            p = e.substring(h, h + n.linkTitle.length),
            h + n.linkTitle.length < e.length && (u = e.substring(h + n.linkTitle.length)),
            $("#tool-toast-content").append(d),
            $("#tool-toast-content").append($('<span id="tool-toast-content-link" class="toast-link-mobile">').text(p).click(function() {
                padutils.hideToolToast(n && n.toolToastRemoveCallBack ? n.toolToastRemoveCallBack: void 0, a, !!n.standardToastType),
                n && n.linkCallback()
            })),
            $("#tool-toast-content").append(u)
        } else $("#tool-toast-content").append(e);
        if (n && n.supportCountDown) {
            var f = n && n.countDownSecond ? n.countDownSecond: 5;
            $("#tool-toast-content").append('<span id="tool-toast-countdown-head">(</span>'),
            $("#tool-toast-content").append('<span id="tool-toast-countdown">' + f + "</span>"),
            $("#tool-toast-content").append('<span id="tool-toast-countdown-tail">S)</span>'),
            $("#tool-toast-countdown-head").css("font-size", "16px"),
            $("#tool-toast-countdown-head").css("color", "#ffffff"),
            $("#tool-toast-countdown").css("font-size", "16px"),
            $("#tool-toast-countdown").css("color", "#ffffff"),
            $("#tool-toast-countdown-tail").css("font-size", "16px"),
            $("#tool-toast-countdown-tail").css("color", "#ffffff"),
            setInterval(function() {
                var e = $("#tool-toast-countdown").text();
                0 == --e ? n && n.countDownCallback && n.countDownCallback() : $("#tool-toast-countdown").text(e)
            },
            1e3)
        }
        a.show(),
        s.show(),
        n && n.toolToastCloseTime && parseFloat(n.toolToastCloseTime) && setTimeout(function() {
            padutils.hideToolToast(n && n.toolToastRemoveCallBack ? n.toolToastRemoveCallBack: void 0, a, !!n.standardToastType)
        },
        parseFloat(n.toolToastCloseTime)),
        $(window).resize(function() {
            $("#toolToastWrap").length > 0 && ($("#toolToast").css("width", document.body.clientWidth + "px"), $("#tool-toast-content").css("max-width", document.body.clientWidth - 52 + "px"))
        })
    },
    changeToastText: function(e) {
        $("#toolToastWrap").length ? $("#tool-toast-content").text(e) : padutils.toolToast(e, {
            top: "5px"
        })
    },
    showToolToast: function(e, t, n, o, i) { ! padutils.getIsMobile() || clientVars.isIPad ? padutils.toolToast(e, t, n, o, i) : padutils.toolToastForMobile(e, t, n, o, i)
    },
    setMobileStandardTotalCss: function(e) {
        var t = $("#toolToastWrap"),
        n = $("#toolToast"),
        o = 0;
        this.checkIsIphoneX() && 90 === window.orientation && (o = 44),
        t.css({
            left: o,
            right: 0,
            width: "100%",
            height: "40px",
            "z-index": 2e4
        }),
        n.css({
            top: 0,
            width: "100%",
            "max-width": "100%",
            "padding-left": "40px",
            "background-color": "rgba(0,0,0,0.8)",
            "font-size": "18px"
        }),
        n.addClass("toast-m toast-m-default-black")
    },
    toolTipMouseenter: function(e) {
        var extendParam = e.data ? e.data.extendParam: null,
        drawViewCallback = e.data ? e.data.drawViewCallback: null;
        padutils.setTimeoutId && (clearTimeout(padutils.setTimeoutId), padutils.setTimeoutId = 0),
        $("#tooltip").remove();
        var vtimeOut = 0;
        if (extendParam && extendParam.timeOut && (vtimeOut = extendParam.timeOut), !(extendParam && extendParam.showTtpInNeedScroll && $(this)[0].scrollWidth <= $(this).outerWidth())) {
            var handleElement = $(this),
            setTimeoutId = setTimeout(function() {
                $("#tooltip").remove();
                var tooltipData = handleElement.attr("data-tooltip") ? handleElement.attr("data-tooltip") : handleElement.data("tooltip");
                if ("function" == typeof tooltipData && (tooltipData = tooltipData()), void 0 !== tooltipData && "string" != typeof tooltipData && (tooltipData = handleElement[0].getAttribute("data-tooltip")), tooltipData && !handleElement.hasClass("hp-ui-button-active")) {
                    if (extendParam && extendParam.mouseenterCallBack && extendParam.mouseenterCallBack(), tooltipData && tooltipData.indexOf("()") && 0 == tooltipData.indexOf("'")) {
                        var tempTooltipData = tooltipData;
                        try {
                            tooltipData = eval(tooltipData)
                        } catch(e) {
                            tooltipData = tempTooltipData
                        }
                    }
                    var tooltip = $("<div id='tooltip'>");
                    if (/\n/.test(tooltipData)) {
                        var lines = tooltipData.split(/\n/);
                        lines.forEach(function(e) {
                            var t = document.createElement("p");
                            $(t).text(e),
                            tooltip.append(t)
                        })
                    } else tooltip.text(tooltipData);
                    tooltip.prependTo($("body")),
                    tooltip.data("target-dom", e.target);
                    var tooltip_taper_angle_Class = "tooltip-before";
                    extendParam && (extendParam.tooltipArrowClass && (tooltip_taper_angle_Class = extendParam.tooltipArrowClass), extendParam.tooltipClass && !tooltip.hasClass(extendParam.tooltipClass) && tooltip.addClass(extendParam.tooltipClass));
                    var tooltipAngle = $("<div id='" + tooltip_taper_angle_Class + "'>"),
                    tooltip_taper_angle = null;
                    tooltip_taper_angle = "tooltip-after" == tooltip_taper_angle_Class ? tooltipAngle.appendTo(tooltip) : tooltipAngle.prependTo(tooltip),
                    drawViewCallback(tooltip, tooltip_taper_angle, handleElement)
                }
            },
            vtimeOut);
            vtimeOut > 0 && (padutils.setTimeoutId = setTimeoutId)
        }
    },
    toolTipMouseleave: function(e) {
        var t = e.data ? e.data.extendParam: null;
        $("#tooltip").remove(),
        padutils.setTimeoutId && (clearTimeout(padutils.setTimeoutId), padutils.setTimeoutId = 0),
        t && t.mouseleaveCallBack && t.mouseleaveCallBack()
    },
    baseTooltip: function(e, t, n) {
        if (padutils.getIsMobile()) {
            if (!clientVars.isIPad) return;
            if (! (e && (e + "").indexOf("otherusers") > -1)) return
        }
        this.removeTooltip(e);
        var o = $(e);
        o.on("mouseenter", null, {
            drawViewCallback: t,
            extendParam: n
        },
        this.toolTipMouseenter),
        o.on("mouseleave", null, {
            drawViewCallback: t,
            extendParam: n
        },
        this.toolTipMouseleave)
    },
    tooltip: function(e, t) {
        return t = t || {},
        padutils.baseTooltip(e,
        function(e, n, o) {
            if (o.siblings(".dropdown-wrapper.open").length > 0 || o.siblings(".toolbar-border-palette.open").length > 0 || "toolbar-colorPicker-picker-wrapper" == o.attr("id") && $.fn.colorPicker.isColorPaletteShow(1) || "toolbar-colorPicker-picker-paint-wrapper" == o.attr("id") && $.fn.colorPicker.isColorPaletteShow(2) || ("sheet-calculate-button" == o.attr("id") || "sheet-calculate-more" == o.attr("id")) && $("#sheet-calculate").siblings(".dropdown-wrapper.open").length > 0 || "web_header_readonly_tip" == o.attr("id") && !$(".readonly_tip_popup").is(":hidden")) $("#tooltip").remove();
            else {
                var i = o.offset().top + o.outerHeight() + 10,
                r = o.offset().left + o.outerWidth() / 2 - e.outerWidth() / 2;
                r = (r = Math.min(document.body.clientWidth - e.outerWidth() - 10, r)) <= 10 ? 10 : r;
                var a = o.offset().left + o.outerWidth() / 2 - r;
                n.css({
                    left: t.beforeLeft || a
                }),
                i + 30 > window.innerHeight && n && "tooltip-after" == n.attr("id") && (i = o.offset().top - o.outerHeight()),
                e.css({
                    top: i,
                    left: r > 0 ? r: 0,
                    zIndex: 1e5,
                    maxWidth: t.maxWidth || "300px",
                    paddingLeft: t.paddingLeft || "10px",
                    textAlign: t.textAlign || "center"
                }).hide().fadeIn(),
                t.afterShow && t.afterShow(e)
            }
        },
        t)
    },
    hideTooltip: function() {
        $("#tooltip").remove()
    },
    removeTooltip: function(e) {
        var t = this;
        $(e).each(function() {
            $(this).off("mouseenter", null, t.toolTipMouseenter),
            $(this).off("mouseleave", null, t.toolTipMouseleave)
        })
    },
    horizontalTooltip: function(e, t, n) {
        return t || (t = {}),
        t.tooltipArrowClass || (t.tooltipArrowClass = "tooltip-right"),
        padutils.baseTooltip(e,
        function(e, o, i) {
            n && n.height ? e.css("height", n.height) : e.css("height", i.outerHeight() - 6),
            e.css("line-height", e.css("height")),
            t && t["min-width"] && e.css("min-width", t["min-width"] - 16);
            var r, a = i.offset().top;
            r = "tooltip-right" == $(o).get(0).id ? i.offset().left + i.outerWidth() : i.offset().left - e.outerWidth() - 9,
            t && t.left && (r += t.left),
            a < 0 && o.css({
                top: e.outerHeight() / 2 + a
            }),
            e.css({
                top: a > 0 ? a: 0,
                left: r > 0 ? r: 0,
                zIndex: 1e5
            }).hide().fadeIn()
        },
        t)
    },
    btnState: function(e, t, n, o) {
        e.bind("mousedown",
        function(e) {
            $(this).removeClass(n + " " + t),
            $(this).addClass(o)
        }),
        e.bind("mouseup",
        function(e) {
            $(this).removeClass(o + " " + t),
            $(this).addClass(n)
        }),
        e.bind("mouseenter",
        function(e) {
            $(this).removeClass(t),
            $(this).addClass(n)
        }),
        e.bind("mouseleave",
        function(e) {
            $(this).removeClass(n + " " + o),
            $(this).addClass(t)
        })
    },
    btnRemoveState: function(e) {
        e.unbind("mousedown"),
        e.unbind("mouseup"),
        e.unbind("mouseenter"),
        e.unbind("mouseleave")
    },
    toolTextCount: function(e, t, n, o, i) {
        var r = i || $("body");
        o && o.isNeedReflush && $(window).resize(function() {
            n && "left" in n || (a = r.outerWidth() / 2 - e.outerWidth() / 2, e.css({
                left: a > 0 ? a: 0
            }))
        });
        var a = r.outerWidth() / 2 - e.outerWidth() / 2;
        e.css({
            left: a > 0 ? a: 0
        }),
        n && $.each(n,
        function(t, n) {
            e.css(t, n)
        }),
        e.text(t),
        e.show()
    },
    hideTextCount: function(e) {
        e.hide()
    },
    getQuery: function(e) {
        var t = "",
        n = window.location.search;
        return n && (t = n.match(new RegExp("(\\?|&)" + e + "=([^&]*)(#|&|$)"))),
        t ? decodeURIComponent(t[2]) : ""
    },
    getEnvParam: function(e) {
        return (e || "") + "route_ip=" + this.getQuery("route_ip") + "&room_route_ip=" + this.getQuery("room_route_ip")
    },
    addFromParam: function(e, t) {
        return e.indexOf("route_ip") >= 0 && t.indexOf("route_ip") >= 0 ? e: (e.indexOf("?") >= 0 ? e += "&" + t: e += "?" + t, e)
    },
    authApply: function(e) {
        void 0 !== window.PermitApply ? (PermitApply.permitApply(clientVars.padType, null, e), window.tdwReport && tdwReport({
            opername: "doc_author",
            module: "edit",
            action: "clk_apply"
        })) : ScriptLoader.ScriptLoader.load("PermitApply",
        function(t) {
            window.PermitApply = t,
            t.permitApply(clientVars.padType, null, e),
            window.tdwReport && tdwReport({
                opername: "doc_author",
                module: "edit",
                action: "clk_apply"
            })
        })
    },
    getCookie: function(e) {
        var t = new RegExp("(?:^|;+|\\s+)" + e + "=([^;]*)"),
        n = "";
        return window.document && (n = window.document.cookie.match(t)),
        (n ? n[1] : "") || null
    },
    checkIsIphoneX: function() {
        return ! (!navigator.userAgent.match(/(iPhone)/) || !(812 == screen.availHeight && 375 == screen.availWidth || 375 == screen.availHeight && 812 == screen.availWidth || 375 == screen.width && 812 == screen.height || 812 == screen.width && 375 == screen.height))
    },
    isPortrait: function() {
        if ("number" == typeof window.orientation) return window.orientation / 90 % 2 == 0;
        try {
            var e = window.screen.orientation.angle;
            if ("number" == typeof e) return e / 90 % 2 == 0
        } catch(e) {}
        return ! 0
    },
    getClientVersion: function() {
        var e = navigator.userAgent,
        t = (navigator.appVersion, getVersion(e, /\sTencentDocs\/\d+\.\d+\.\d+\.\d+/i)),
        n = t ? null: getVersion(e, /\sTIM.{0,5}\d+\.\d+\.\d+\.\d+/i),
        o = null;
        return t || n || (o = mqq && mqq.clientVersion > "0" ? mqq.clientVersion: null),
        {
            app_version: t,
            tim_version: n,
            qq_version: o,
            h5: !t && !n && !o
        }
    },
    isDevMode: function() {
        var e = clientVars.envi,
        t = $.cookie.get("dev_oa_name");
        return ! (!e || 0 !== e.type || !t)
    }
};
// 
indexDropdown = 0,
DropDown = function(e, t, n, o) {
    var i = 0,
    r = 0,
    a = null,
    s = null,
    c = {},
    l = null,
    d = {
        getMyId: function(e) {
            return e + "-" + i
        },
        setMyRowId: function(e, t) {
            var n = "li-dropdown-" + i + "-" + r++;
            e.attr("id", n),
            t && (c[t] = "#" + n)
        },
        initMyId: function() {
            i = indexDropdown++,
            a.attr("id", d.getMyId("dropdown-wrapper"))
        },
        isNormalImageContentTemplate: function(e) {
            return "template-dropdown-image-content-row" == e || "template-dropdown-image-row" == e || "template-dropdown-content-row" == e || "template-dropdown-content-separator-row" == e
        },
        getNormalImageContentRowHtml: function(e, t) {
            if (d.isNormalImageContentTemplate(t)) {
                var n = $($("#" + t).html()),
                o = e.image,
                i = e.content,
                r = e.fontFamily,
                a = e.clickcallback,
                s = e.hover,
                c = e.disable,
                l = e.iconClsName,
                p = e.contentClsName;
                if (!o && !i && !a) return;
                if (e.drawviewcallback) e.drawviewcallback(n, e);
                else {
                    var u = $(n).find(".dropdown-img"),
                    h = $(n).find(".dropdown-content");
                    o ? u.css("background-image", "url(" + o + ")") : $(n).find(".dropdown-img img").hide(),
                    i ? h.append(i) : h.hide(),
                    p && h.addClass(p),
                    r && h.css("font-family", r),
                    c && h.css("opacity", .3),
                    s && ($(n).first("li.li-dropdown").attr("data-tooltip", s), padutils.horizontalTooltip($(n).first("li.li-dropdown"), {
                        "min-width": 66,
                        left: 10
                    })),
                    l && u.addClass(l)
                }
                return ! c && a && "function" == typeof a && $(n).on("click",
                function(e) {
                    a(e)
                }),
                d.setMyRowId($(n), e.identity),
                n
            }
            return null
        },
        updateText: function(e, t, n, o) {
            var i = c[e];
            if (i) {
                var r = s.find(i);
                r && n && $(r).attr(n, o),
                r && $(r).find(".dropdown-c-content").text(t)
            }
        },
        isCelltypeTemplate: function(e) {
            return "template-dropdown-celltype-row" === e || "template-dropdown-separator-row" === e
        },
        getCelltypeRowHtml: function(e, t) {
            if ("template-dropdown-celltype-row" === t) {
                var n = $($("#" + t).html()),
                o = e.title,
                i = e.detail,
                r = e.clickcallback,
                a = e.hover;
                if (!o || !r) return;
                return e.drawviewcallback ? e.drawviewcallback(n, e) : ($(n).find(".dropdown-celltype-title").append(o), i ? $(n).find(".dropdown-celltype-detail").append(i) : $(n).find(".dropdown-celltype-detail").addClass("hidden"), a && ($(n).first("li.li-dropdown").attr("data-tooltip", a), padutils.horizontalTooltip($(n).first("li.li-dropdown"), {
                    "min-width": 66,
                    left: -15
                }))),
                r && "function" == typeof r && $(n).on("click",
                function(e) {
                    r(e)
                }),
                d.setMyRowId($(n), e.identity),
                n
            }
            return "template-dropdown-separator-row" === t ? n = $($("#" + t).html()) : null
        },
        getRowTemplate: function(e) {
            return e.template || (e.image || e.iconClsName) && e.content && "template-dropdown-image-content-row" || (e.image || e.iconClsName) && !e.content && "template-dropdown-image-row" || !(e.image || e.iconClsName) && e.content && "template-dropdown-content-row" || "template-dropdown-image-content-row"
        },
        getRowHtml: function(e) {
            var t = d.getRowTemplate(e),
            n = null;
            return d.isCelltypeTemplate(t) ? n = d.getCelltypeRowHtml(e, t) : d.isNormalImageContentTemplate(t) && (n = d.getNormalImageContentRowHtml(e, t)),
            n
        },
        updateDropDown: function(e) {
            s.append($(e))
        },
        disable: function(e) {
            if (e) for (var t = 0; t < e.length; t++) {
                var n = e[t];
                c[n] && ($(c[n]).hasClass("disabled") || $(c[n]).addClass("disabled"))
            }
        },
        enable: function(e) {
            if (e) for (var t = 0; t < e.length; t++) {
                var n = e[t];
                c[n] && $(c[n]).hasClass("disabled") && $(c[n]).removeClass("disabled")
            }
        },
        getLastStatus: function() {
            return l
        },
        setLastStatus: function(e) {
            l = e
        },
        updateToolTip: function(e) {
            if (e) for (var t in e) c[t] && $(c[t]).attr("data-tooltip", e[t])
        },
        hide: function() {
            a.removeClass("open")
        },
        show: function() {
            d.isEnableOpenDropDown() && (a.addClass("open"), d.setMaxHeight(), t && (t.children().first().offset().left + a.outerWidth(!0) < $(document.body).width() || t.children().first().outerWidth(!0) >= a.outerWidth(!0) ? a.css("left", 2) : a.css("left", t.children().first().outerWidth(!0) - a.outerWidth(!0)), d.refreshSelItem(!0)))
        },
        setMaxHeight: function() {
            if (s) {
                var e = $("header").height(),
                t = $(window).height() - e - 75;
                s.css("max-height", t + "px")
            }
        },
        toggleDropDown: function() {
            a.hasClass("open") ? d.hide() : d.show()
        },
        bodyclickhandler: function(e, t) {
            $(e.target).is("#" + t.attr("id")) || $(e.target).parents("#" + t.attr("id")).length && 0 == $(e.target).parents(a.attr("id")).length || d.hide()
        },
        registerEventListeners: function() {
            $("body").on("click",
            function(e) {
                d.bodyclickhandler(e, t)
            }),
            $(window).on("resize",
            function(e) {
                d.bodyclickhandler(e, t)
            }),
            window.addEventListener("closedropdown",
            function(e) {
                d.bodyclickhandler(e, t)
            }),
            a.on("mousedown",
            function(e) {
                e.preventDefault()
            })
        },
        disableOpenDropDown: function() {
            $(t).addClass("dropdown-container-disabled"),
            d.hide()
        },
        enableOpenDropDown: function() {
            $(t).removeClass("dropdown-container-disabled")
        },
        isEnableOpenDropDown: function() {
            return ! $(t).hasClass("dropdown-container-disabled")
        },
        init: function(e, t) {
            for (var n in e, a = $($("#template-dropdown-wrapper").html()), d.initMyId(), (s = $("<div></div>")).addClass("dropdown-wrapper-box"), a.append(s), t.append(a), e) {
                var o = d.getRowHtml(e[n]);
                d.updateDropDown(o)
            }
            d.registerEventListeners(),
            $(window).on("resize", d.setMaxHeight)
        },
        _scrollIfNeeded: function(e, t) {
            if (e && t) if (e.offsetTop < t.scrollTop) t.scrollTop = e.offsetTop;
            else {
                var n = e.offsetTop + e.offsetHeight;
                n > t.scrollTop + t.offsetHeight && (t.scrollTop = n - t.offsetHeight / 2)
            }
        },
        refreshSelItem: function(e) {
            if (t && a.hasClass("open") && d.isEnableOpenDropDown()) {
                var i = t.children().first().text().trim();
                i || ((i = t.children().first().attr("identity")) && -1 != i.indexOf("left") ? i = "左对齐": i && -1 != i.indexOf("right") ? i = "右对齐": i && -1 != i.indexOf("center") && (i = "居中对齐"));
                var r = $(n).attr("identity"),
                s = c[r],
                l = a.find(s);
                if (e || !l || !l.hasClass("li-dropdown-selected")) {
                    var p = a.find("li.li-dropdown-selected");
                    p && p.removeClass("li-dropdown-selected"),
                    i && o && l && 0 === l.find(".dropdown-no-selected").length && (a.find(s).addClass("li-dropdown-selected"), d._scrollIfNeeded(a.find(s)[0], a.find("div.dropdown-wrapper-box")[0]))
                }
            }
        }
    };
    return d.init(e, t),
    d
}
var padeditor = {
    ace: {
        callWithAce: function(e, t, n){
            var o = function() {
                return e(this)
            };
            if (void 0 !== n) {
                var r = o;
                o = function() {
                    r()
                }
            }
            return o()
        }
    }
};

!function(e, t) {
    e.TableBase = t();
}(this, function() {
    function e(e, t) {
        this.initTable(e, t),
        this.initHotCmd()
    }
    return e.prototype.initTable = function(e, t) {
        e && (this.container = e,
        this.isSheet = !!t,
        this.hot = null)
    }
    ,
    e.prototype.cellRenderer = function(e, t, n, r, o, i, a) {
        Handsontable.renderers.FormulaRenderer.apply(this, arguments);
        var l = a.style;
        if (l) {
            var s = "";
            l.bold && (s += "font-weight: " + l.bold + ";"),
            l.italic && (s += "font-style: " + l.italic + ";");
            var u = "strikeline" === l.strikeline
              , c = "underline" === l.underline;
            if (u && !c ? s += "text-decoration: line-through;" : !u && c ? s += "text-decoration: underline;" : u && c ? s += "text-decoration: underline line-through;" : u || c || (s += "text-decoration: none;"),
            l.linefeed && "off" === l.linefeed && (s += "text-decoration: none;"),
            l.h_align && (s += "text-align: " + l.h_align + ";"),
            l.v_align && (s += "vertical-align: " + l.v_align + ";"),
            l.fontcolor && (s += "color: " + l.fontcolor + ";"),
            l.cellcolor) {
                var f = l.cellcolor;
                if (f)
                    this.sheetBorderCalculate || (this.sheetBorderCalculate = new SheetBorderCalculate),
                    s += "background-color: " + f + ";",
                    s += "border-color: " + this.sheetBorderCalculate.toCalculateColor(f) + ";"
            }
            if (l.fontsize)
                s += "font-size: " + (parseInt(l.fontsize) + 4) + "px;";
            if (l.fontfamily) {
                s += "font-family: " + ["Microsoft YaHei", "STFangsong", "STKaiti", "STSong", "Arial", "Comic Sans MS", "Courier New", "Georgia", "Impact", "Times New Roman", "Trebuchet MS", "Verdana"][parseInt(l.fontfamily) - 1] + ";"
            }
            if (l.sheetborder) {
                var d, h, p, g, m = "", v = l.sheetborder;
                g = v.indexOf("--") >= 0 ? v.split("--") : [v];
                for (var y = 0; y < g.length; y++)
                    2 == (d = g[y].split(":")).length && (h = d[0],
                    p = "1px solid " + d[1] + ";",
                    "border-top-attr" === h ? m += "border-top: " + p : "border-right-attr" === h || "border-right-show" === h ? m += "border-right: " + p : "border-bottom-attr" === h || "border-bottom-show" === h ? m += "border-bottom: " + p : "border-left-attr" === h && (m += "border-left: " + p));
                s += m
            }
            t.style.cssText = s
        } else
            t.style.cssText && (t.style.cssText = "")
    }
    ,
    e.prototype.initHotCmd = function() {
        this.hot_cmd = {
            "sheet-bold-bold": "bold:bold",
            "sheet-bold-normal": "bold:normal",
            "sheet-italic-italic": "italic:italic",
            "sheet-italic-normal": "italic:normal",
            "sheet-underline-underline": "underline:underline",
            "sheet-underline-none": "underline:none",
            "sheet-strike-strike": "strikeline:strikeline",
            "sheet-strike-none": "strikeline:none",
            "sheet-left-align": "h_align:left",
            "sheet-center-align": "h_align:center",
            "sheet-right-align": "h_align:right",
            "sheet-top-align": "v_align:top",
            "sheet-middle-align": "v_align:middle",
            "sheet-bottom-align": "v_align:bottom",
            "sheet-linefeed-on": "linefeed:on",
            "sheet-linefeed-off": "linefeed:off",
            "sheet-merge": "mergecells",
            "sheet-fix-row-col": "fix_row_col",
            "sheet-format-painter": "matchprop",
            "sheet-clear-format": "cleanstyle",
            "sheet-insert-link-open": "insertlink:open",
            "sheet-insert-link-close": "insertlink:close",
            "sheet-format-default": "format:default",
            "sheet-format-text": "format:text",
            "sheet-format-numeral": "format:numeral",
            "sheet-format-numeral-int": "format:numeral-int",
            "sheet-format-percentage": "format:percentage",
            "sheet-format-percentage-int": "format:percentage-int",
            "sheet-format-fraction": "format:fraction",
            "sheet-format-exponential": "format:exponential",
            "sheet-format-currency-chs": "format:currency-chs",
            "sheet-format-currency-hk": "format:currency-hk",
            "sheet-format-currency-en": "format:currency-en",
            "sheet-format-time": "format:time",
            "sheet-format-date": "format:date",
            "sheet-format-date-chs": "format:date-chs",
            "sheet-format-date-time": "format:date-time",
            "sheet-format-date-chs-ym": "format:date-chs-ym",
            "sheet-format-date-chs-md": "format:date-chs-md",
            "sheet-format-custom": "",
            "sheet-textwrap-nowrap": "textwrap:nowrap",
            "sheet-textwrap-normal": "textwrap:pre-wrap",
            "sheet-freeze-row": "freeze:row",
            "sheet-freeze-col": "freeze:col",
            "sheet-freeze-cell": "freeze:cell",
            "sheet-freeze-cancel": "freeze:cancel",
            "sheet-insert-shape": "insertshape:add",
            "sheet-update-shape": "insertshape:update",
            "sheet-filter-done": "filter:done",
            "sheet-filter-cancel": "filter:cancel",
            "sheet-decimal-decrease": "format:decimal-decrease",
            "sheet-decimal-increase": "format:decimal-increase",
            "sheet-add-thousands-separator": "format:add-thousands-separator"
        }
    }
    ,
    e.prototype.sendCmd = function(a, e, t) {
        var n = immTableSheet.sheetHot
          , r = immTableSheet.jExcel
          , o = this.hot_cmd[e] || e;
        if ("sheet-undo" === o){
            $(r.div).jexcel('undo');
        }else if ("sheet-redo" === o){
            $(r.div).jexcel('redo');
        }else if ("matchprop" === o){
            immTableSheet.sheetHot.actionCenter.painter(),
            updateUndoRedoButtonState();
        }else if ("cleanstyle" === o){
            // 获取样式更改前的样式
            var oldStyle = immTableSheet.sheetHot.actionCenter.getStyle(r);
            immTableSheet.sheetHot.actionCenter.cleanStyle(r),
            updateUndoRedoButtonState();
            // 记录进操作历史记录
            immTableSheet.sheetHot.actionCenter.setHistory(r, oldStyle);
        }else if (0 === o.indexOf("format:")) {
            immTableSheet.sheetHot.actionCenter.format(n, r, o);
            updateUndoRedoButtonState()
        } else if (0 === o.indexOf("fontfamily:") || 0 === o.indexOf("fontsize:") || 0 === o.indexOf("fontcolor:") || 0 === o.indexOf("cellcolor:") || 0 === o.indexOf("bold:") || 0 === o.indexOf("italic:") || 0 === o.indexOf("underline:") || 0 === o.indexOf("strikeline:") || 0 === o.indexOf("h_align:") || 0 === o.indexOf("v_align:") || 0 === o.indexOf("textwrap:") || 0 === o.indexOf("decimal:")){
            // 获取样式更改前的样式
            var oldStyle = immTableSheet.sheetHot.actionCenter.getStyle(r);
            immTableSheet.sheetHot.actionCenter.cellStyle(a, r, o),
            updateUndoRedoButtonState();
            // 记录进操作历史记录
            immTableSheet.sheetHot.actionCenter.setHistory(r, oldStyle);
        }else if ("mergecells" === o){
            $(r.div).jexcel(a.hasMergeCellsInSelectedRange() ? "unmergeCell" : "mergeCell"),
            updateUndoRedoButtonState();
        }else if (0 === o.indexOf("sheetborder:")){
            // 获取样式更改前的样式
            var oldStyle = immTableSheet.sheetHot.actionCenter.getStyle(r);
            immTableSheet.sheetHot.actionCenter.sheetborder(a, r, o),
            updateUndoRedoButtonState();
            // 记录进操作历史记录
            immTableSheet.sheetHot.actionCenter.setHistory(r, oldStyle);
        }else if (0 === o.indexOf("insertlink:")){
            immTableSheet.sheetHot.actionCenter.insertlink(o)
        }else{
            console.log(o)
            console.warn("sendCmd:警告无匹配操作");
        }
    }
    ,
    e
});


!function(e, t) {
    "object" == typeof exports ? module.exports = t() : "function" == typeof define && define.amd ? define(t) : e.ImmTableSheet = t()
}(this, function() {
    function e() {}
    return e.prototype = new TableBase,
    e.prototype.init = function() {
        var e = this;
        this.actionCenter = {};
        this.jExcel = $.fn.jexcel.defaults[window.jExcelID];
        this.sheetHot = {
            getCellStyle: function(e){
                // 默认查看当前激活的单元格样式
                e || (e = $(immTableSheet.jExcel.div).jexcel("getActiveCell", true));
                return !e ? {} : {
                    bold: 700 == $(e).css("font-weight") ? "bold" : "normal",
                    borderTop: $(e).css("border-top"),
                    borderRight: $(e).css("border-right"),
                    borderBottom: $(e).css("border-bottom"),
                    borderLeft: $(e).css("border-left"),
                    cellcolor: $(e).css("color"),
                    fontcolor: $(e).css("background-color"),
                    fontsize: $(e).css("font-size"),
                    format: "default",
                    format_data: "0.00_",
                    textwrap: $(e).css("white-space"),
                    // italic: $(e).css("font-style"),
                    // strikeline: "none",
                    // underline: "underline"
                }
            },
            actionCenter: {
                getStyle: function(r){
                    var styles = [];
                    $(r.div).find(".highlight").each(function(){
                        styles.push({
                            e: 'tbody td[id='+$(this).prop("id")+']',
                            class: $(this).attr("class") || '',
                            style: $(this).attr("style") || ''
                        });
                    });
                    return styles;
                },
                setHistory: function(r, oldStyle, newStyle){
                    var o = $(r.div).find(".highlight:first");
                    var d = $(r.div).find(".highlight:last");
                    var newStyle = immTableSheet.sheetHot.actionCenter.getStyle(r);
                    $(r.div).jexcel('setHistory', null, {
                        type:'setStyle',
                        selection: [o, d],
                        styles: {
                            oldStyle: oldStyle,
                            newStyle: newStyle
                        }
                    });
                },
                cellStyle: function(a, r, o){
                    de = o.split(":");
                    switch(de[0]){
                        case "fontsize":
                            $(r.div).find(".highlight").css("font-size", de[1]);
                            break;
                        case "bold":
                            $(r.div).find(".highlight").css("font-weight", de[1]);
                            break;
                        case "h_align":
                            $(r.div).find(".highlight").css("text-align", de[1]);
                            break;
                        case "v_align":
                            $(r.div).find(".highlight").css("vertical-align", de[1]);
                            break;
                        case "textwrap":
                            $(r.div).find(".highlight").css("white-space", de[1]);
                            break;
                        case "fontcolor":
                            $(r.div).find(".highlight").css("color", de[1]);
                            break;
                        case "cellcolor":
                            $(r.div).find(".highlight").css("background-color", de[1]);
                            break;
                        default :
                            console.warn("此操作暂未开放! "+de[0]);
                            padutils.showToolToast("此操作暂未开放!"+de[0], {zIndex: 99999}, {toolToastCloseTime: 3e3});
                    }
                },
                sheetborder: function(a, r, o){
                    var de = o.split(":");
                    $(r.div).find(".highlight").addClass('hasBorder');
                    var n = {
                        // all,所有边线
                        a: function(col, row){
                            var e = n.e(col, row);
                            $(e).css("border-top", "0px solid #000");
                            $(e).css("border-right", "1px solid #000");
                            $(e).css("border-bottom", "1px solid #000");
                            $(e).css("border-left", "0px solid #000");
                        },
                        // clear,清除边线
                        c: function(col, row){
                            var e = n.e(col, row);
                            $(e).css("border-top", "0px solid #ccc");
                            $(e).css("border-right", "1px solid #ccc");
                            $(e).css("border-bottom", "1px solid #ccc");
                            $(e).css("border-left", "0px solid #ccc");
                        },
                        // top上边线
                        t: function(col, row){
                            var e = n.e(col, row);
                            $(e).css("border-top", "1px solid #000");
                        },
                        // right右边线
                        r: function(col, row){
                            var e = n.e(col, row);
                            $(e).css("border-right", "1px solid #000");
                        },
                        // bottom下边线
                        b: function(col, row){
                            var e = n.e(col, row);
                            $(e).css("border-bottom", "1px solid #000");
                        },
                        // left左边线
                        l: function(col, row){
                            var e = n.e(col, row);
                            $(e).css("border-left", "1px solid #000");
                        },
                        // has 是否含有某个class类
                        h: function(col, row, cla){
                            var e = n.e(col, row);
                            cla || (cla = "hasBorder");
                            if(cla === 't'){
                                return !$(e).css("border-top") && $(e).css("border-top") != '1px solid rgb(204, 204, 204)';
                            }else if(cla === 'r'){
                                return !$(e).css("border-right") && $(e).css("border-right") != '1px solid rgb(204, 204, 204)';
                            }else if(cla === 'b'){
                                return !$(e).css("border-bottom") && $(e).css("border-bottom") != '1px solid rgb(204, 204, 204)';
                            }else if(cla === 'l'){
                                return !$(e).css("border-left") && $(e).css("border-left") != '1px solid rgb(204, 204, 204)';
                            }
                            return $(e).hasClass(cla);
                        },
                        // 获取单元格对象
                        e: function(col, row){
                            return (typeof col === "object") ? col : $(r.div).find("td[id="+col+"-"+row+"]")
                        }
                    }
                    n.c($(r.div).find(".highlight"));
                    switch(de[1]){
                        case "border-all":
                            var i = parseInt(a.from.col)
                              , s = parseInt(a.from.row)
                              , u = parseInt(a.to.col)
                              , c = parseInt(a.to.row);
                            n.a($(r.div).find(".highlight"));
                            // 左边线
                            n.l($(r.div).find(".highlight.c0"));
                            for (var col = i; col <= u; col++){
                                n.h(col, s-1, "hasBorder") || n.t(col, s);
                            }
                            for (var row = s; row<= c; row++) {
                                n.h(i-1, row, "hasBorder") || n.l(i, row);
                            }
                        break;
                        case "border-inner":
                            var i = parseInt(a.from.col)
                              , s = parseInt(a.from.row)
                              , u = parseInt(a.to.col)
                              , c = parseInt(a.to.row);
                            for (var col = i; col <= u; col++){
                                n.h(col, s-1, "b") || n.t(col, s);
                                if(!n.h(col, c, "hide")){
                                    n.b(col, c)
                                }else{
                                    n.b($(r.div).find("td[id=" + $(r.div).find("td[id="+col+"-"+c+"]").data("merge") + "]"));
                                }
                            }
                            for (var row = s; row<= c; row++) {
                                n.h(i-1, row, "hasBorder") || n.l(i, row);
                                if(!n.h(u+1, row, "hasBorder")) {
                                    if(!n.h(u, row, "hide")){
                                        n.r(u, row);
                                    }else{
                                        n.r($(r.div).find("td[id=" + $(r.div).find("td[id="+u+"-"+row+"]").data("merge") + "]"));
                                    }
                                }
                            }
                            break;
                        case "border-clear":
                            $(r.div).find(".highlight").removeClass("hasBorder");
                            break;
                    }
                }
                ,cleanStyle: function(r){
                    $(r.div).find(".highlight").each(function(){
                        $(this).attr("style", '');
                        var bs = $(this).attr("class").match(/__builtInStyle[0-9]+/i);
                        bs && $(this).removeClass(bs[0]);
                    });
                },insertlink: function(o){
                    de = o.split(":");
                    if("open" == de[1]){
                        linkViewer.show();;
                    }else{
                        linkViewer.hide();;
                    }
                },format(n, r, o){
                    // 声明统一值
                    var newMask = false;
                    $(r.div).find(".highlight:first").each(function(){
                        var ids = $(this).prop('id').split('-');
                        var mask = r.cellDataArray[ids[1]][ids[0]]['mask'];
                        switch(o.split(':')[1]){
                            // 常规
                            case 'default':
                                newMask = false;
                            break;
                            // 文本
                            case 'text':
                                newMask = false;
                            break;
                            // 减少小数位数
                            case 'decimal-decrease':
                                if(!$.isNumeric(newMask)){
                                    newMask = $.isNumeric(mask) ? parseInt(mask) : 0;
                                }
                                newMask > 0 && (newMask-=1);
                            break;
                            // 增加小数位数
                            case 'decimal-increase':
                                if(!$.isNumeric(newMask)){
                                    newMask = $.isNumeric(mask) ? parseInt(mask) : 0;
                                }
                                newMask+=1;
                            break;
                        }
                    });
                    // 所有选中单元格执行同一个修改值而不是各改各的
                    $(r.div).find(".highlight").each(function(){
                        var ids = $(this).prop('id').split('-');
                        // 如果newMask是false就删除该属性
                        if(false === newMask){
                            delete r.cellDataArray[ids[1]][ids[0]]['mask'];
                        }else{
                            r.cellDataArray[ids[1]][ids[0]]['mask'] = newMask;
                        }
                        // 根据设置的mask更新数据
                        var value = r.data[ids[1]][ids[0]];
                        if (value.substr(0,1) == '=') {
                            // 如果是计算公式,则重新运算,对数据进行重新修约
                            $(r.div).jexcel('executeFormula', $(this).prop('id'));
                        }else{
                            // 执行文本格式化
                            $(r.div).jexcel('setValue', $(this), $(this).limsMask(value, r.cellDataArray[ids[1]][ids[0]]));
                        }
                    });
                }
            }
        };
    }
    ,
    e.prototype.sendCmd = function(e, t) {
        "sheet-export" == e ? this.doSheetExport() : TableBase.prototype.sendCmd.call(this, e, t)
    }
    ,
    e;
})


function prettyDate(e) {
    if (e) {
        var t = e.replace(/\s+/, "").split(/[^0-9]/)
          , n = null;
        n = t.length >= 5 ? e ? new Date(t[0],t[1] - 1,t[2],t[3],t[4]) : new Date : e ? new Date(e) : new Date;
        var o = new Date
          , i = n.getHours()
          , r = n.getMinutes()
          , a = (i > 9 ? i : "0" + i) + ":" + (r > 9 ? r : "0" + r)
          , s = new Date(o.getFullYear(),o.getMonth(),o.getDate()) - n;
        return s > 0 && s < 864e5 ? "昨天 " + a : n.getFullYear() < o.getFullYear() ? n.getFullYear() + "/" + (n.getMonth() + 1) + "/" + n.getDate() + " " + a : n.getMonth() < o.getMonth() || n.getDate() < o.getDate() ? n.getMonth() + 1 + "/" + n.getDate() + " " + a : a
    }
}
function dateFormat(e) {
    return e instanceof Date ? [e.getFullYear(), "年", e.getMonth() + 1, "月", e.getDate(), "日 ", e.getHours() > 9 ? e.getHours() : "0" + e.getHours(), ":", e.getMinutes() > 9 ? e.getMinutes() : "0" + e.getMinutes()].join("") : e.toString() ? e.toString() : ""
}
var timerTipsHidden = null
function showWeakTips(e, t, n, o) {
    var i = $("#editor-last-state");
    i.css("z-index", "9999"),
    destoryTimerTipsHidden(),
    o ? (i.data("tooltip", o),
    padutils.tooltip("#editor-last-state", {
        tooltipClass: "editor-state-tip"
    })) : (i.removeData("tooltip"),
    padutils.removeTooltip("#editor-last-state")),
    i.text(e),
    $("#editor-last-changed").hide(),
    i.show(),
    "undefined" != typeof pad_header && pad_header.refreshLayout(),
    t && (timerTipsHidden = setTimeout(function() {
        $("#last-saved-timestamp").prettyDate(),
        hideWeakTips()
    }, t))
}

function hideWeakTips() {
    $("#editor-last-changed").show(),
    $("#editor-last-state").hide(),
    destoryTimerTipsHidden()
}
function destoryTimerTipsHidden() {
    timerTipsHidden && (clearTimeout(timerTipsHidden),
    timerTipsHidden = null)
}

function setStatusTips(e, t, n, o) {
    destoryTimerTipsHidden(),
    showWeakTips(e, t, !0, o)
}
function setDateTipsHover(e) {
    if ("string" == typeof e) {
        var t = e.replace(/\s+/, "").split(/[^0-9]/);
        e = t.length >= 5 ? e ? new Date(t[0],t[1] - 1,t[2],t[3],t[4]) : new Date : e ? new Date(e) : new Date
    }
    var n = dateFormat(e);
    $("#editor-last-changed").data("tooltip", "最近保存: " + n)
}
"undefined" != typeof jQuery && (jQuery.fn.prettyDate = function() {
    if(this.each)
    return this.each(function() {
        destoryTimerTipsHidden();
        var e = prettyDate(this.getAttribute("time"));
        hideWeakTips(),
        setDateTipsHover(this.getAttribute("time")),
        jQuery(this).text(e || "刚刚")
    })
}
)
// padutils.showToolToast("模板保存成功，在[新建-我的模板]中查看。", {
//         zIndex: 999999
//     }, {
//         toastType: "typeSuccess",
//         linkTitle: "查看",
//         toolToastCloseTime: 5e3,
//         linkCallback: function() {
//             alert('Hello');
//         }
//     }
// )

// 数据绑定
window.linkWrap = {
    wrap: $(".link-wrap"),
    box: $(".link-box"),
    content: $(".link-content"),
    cancel: $(".link-cancel"),
    modify: $(".link-modify"),
    show: function(e){
        // 隐藏字段关联编辑窗口
        window.linkModifyWrap.wrap.hide();
        if(!this.check()){
            this.hide();
            return false;
        }   
        var o = immTableSheet.jExcel,
            cellArr = $(o.div).jexcel("getActiveCell", true).prop("id").split("-"),
            cellHead = $(this).jexcel('getLetter', cellArr[0], cellArr[1]),
            cellInput = $(o.div).jexcel('getValue', cellHead);
        // 当前单元格是否具有绑定数据
        this.content.html(
            !o.cellDataArray[cellArr[1]][cellArr[0]]['link'] ? 
                '尚未关联任何数据' : 
                '"' + o.cellDataArray[cellArr[1]][cellArr[0]]['link'][1] + '"' + ':' + o.cellDataArray[cellArr[1]][cellArr[0]]['link'][0]
        );
        // 显示bindColumn-modal
        var d = $(e).offset().left - $(o.div).offset().left,
            p = $(e).offset().top - $(o.div).offset().top - 33;
        d < 5 && (d = 5),
        d + 220 > $(o.div).width() && (d = $(o.div).width() - 300),
        this.box.css({left: d, top: p}).show();
    },
    hide: function(){
        this.box.css({left: -600, top: -600}).hide();
    },
    check: function(){
        if(!linkViewer || !linkViewer.isShow()){
            return false;
        }
        if (immTableSheet.jExcel && (padeditbar_utils.isSingleCell() || padeditbar_utils.isSingleMergeCell())) {
            return true;
        }
        return false;        
    }
}
linkWrap.cancel.click(function(){
    linkWrap.hide();
    console.log('取消关联');
});
linkWrap.modify.click(function(){
    linkWrap.hide();
    linkModifyWrap.show();
});
window.linkModifyWrap = {
    wrap: $(".link-modify-wrap"),
    content: ".link-modify-content",
    headeInput: $(".link-modify-heade"),
    readOnly: $("#link-modify-readonly"),
    textInput: $(".link-modify-input:eq(0)"),
    linkInput: $(".link-modify-input:eq(1)"),
    formatInput: $(".link-modify-input:eq(2)"),
    cssClass: $(".link-modify-input:eq(3)"),
    warnning: $(".link-modify-warnning"),
    applyBtn: $(".link-modify-button"),
    arrow: $(".link-modify-arrow"),
    show: function(e) {
        this.hideWarnning(),
        window.linkWrap.hide();
        var linkInput ='',
            textInput = '',
            o = immTableSheet.jExcel,
            e = $(o.div).jexcel("getActiveCell", true);
        if(!e){
            return;
        }
        var d = parseInt($(e).offset().left) - $(o.div).offset().left + 10,
            p = parseInt($(e).offset().top) - $(o.div).offset().top + parseInt($(e).outerHeight()),
            cellArr = $(o.div).jexcel("getActiveCell", true).prop("id").split("-"),
            cellHead = $(this).jexcel('getLetter', cellArr[0], cellArr[1]);
        // 模态框小三角的指向位置
        // this.arrow.addClass("link-modify-arrow link-modify-arrow-top").removeClass("link-modify-arrow link-modify-arrow-bottom")//.top(this.arrow.offset().top += 8);
        // this.arrow.addClass("link-modify-arrow link-modify-arrow-bottom").removeClass("link-modify-arrow link-modify-arrow-top")//.top(this.arrow.offset().top -+= 8);
        // this.arrow.css({left: "20%"});
        // 单元格名称
        this.headeInput.val(cellHead);
        if(o.cellDataArray[cellArr[1]][cellArr[0]]['link']){
            // 新版版的数据绑定
            // 字段名称
            linkInput = o.cellDataArray[cellArr[1]][cellArr[0]]['link'][0];
            // 字段说明
            textInput = o.cellDataArray[cellArr[1]][cellArr[0]]['link'][1];
        }
        var cssClass = o.cellDataArray[cellArr[1]][cellArr[0]]['cssClass'] || '';
        var formatInput = o.cellDataArray[cellArr[1]][cellArr[0]]['format'] || '';
        this.linkInput.val(linkInput);
        this.textInput.val(textInput);
        this.formatInput.val(formatInput);
        this.cssClass.val(cssClass);
        this.readOnly.prop("checked", o.cellDataArray[cellArr[1]][cellArr[0]]['readOnly'])
        this.wrap.css({
            left: d + 'px',
            top: p + 10 + 'px',
        }).show();
        "" === textInput || null === textInput ? this.textInput.focus() : (this.linkInput.select(),
        this.linkInput.focus());
        // 点击其他区域时自动隐藏
        $(document).on('mousedown touchstart', function(e){
            if(!$(e.target).hasClass("link-modify-wrap") && !$(e.target).parents(".link-modify-wrap").length){
                linkModifyWrap.hide();
            }
        });
        // 锁定jExcel状态
        $.fn.jexcel.currentBak = $.fn.jexcel.current;
        $.fn.jexcel.current = null;
    },hide: function(){
        this.wrap.hide(),
        this.hideWarnning(),
        $.fn.jexcel.current = $.fn.jexcel.currentBak;
    }, hideWarnning: function() {
        this.warnning.css('visibility', "hidden"),
        this.linkInput.removeClass("link-modify-input-warn"),
        this.textInput.removeClass("link-modify-input-warn")
    }, showWarnning: function(c, e) {
        if(!c || !e){
            return false;
        }
        e.focus(),
        e.addClass("link-modify-input-warn"),
        this.warnning.css('visibility', "visible").html(c)
    }
}
// 数据绑定提交修改
linkModifyWrap.applyBtn.click(function(){
    var is_vd = function(col, row){
        var start = parseInt(immTableSheet.jExcel.config.lineNum);
        var end = start + parseInt(immTableSheet.jExcel.config.lineCount);
        return (row >= start && row < end);
    }
    var cellHead = linkModifyWrap.headeInput.val();
    var fieldName = linkModifyWrap.textInput.val();
    var fieldCode = linkModifyWrap.linkInput.val();
    var formatValue = linkModifyWrap.formatInput.val();
    var cssClass = linkModifyWrap.cssClass.val();

    var cellArr = $.fn.jexcel('getIdFromColumnName', cellHead, true);
    // 定义了字段说明或者自定义单元格属性后必须绑定字段
     if((fieldName || formatValue) && !fieldCode){
        linkModifyWrap.showWarnning('请输入字段名称', linkModifyWrap.linkInput);
        return false;
     }
    // 设置
    if(!fieldCode && !fieldName){
        immTableSheet.jExcel.cellDataArray[cellArr[1]][cellArr[0]]['link'] = null;
    }else{
        immTableSheet.jExcel.cellDataArray[cellArr[1]][cellArr[0]]['link'] = [fieldCode, fieldName];
    }
    // cssClass自定义类,用来添加class绑定的事件
    immTableSheet.jExcel.cellDataArray[cellArr[1]][cellArr[0]]['cssClass'] = cssClass;
    // format自定义单元格格式
    immTableSheet.jExcel.cellDataArray[cellArr[1]][cellArr[0]]['format'] = formatValue;
    // 先解除只读属性
    $(immTableSheet.jExcel.div).jexcel('getCell', cellHead).removeClass('readonly');
    // 将format属性写入当前单元格的内容值里面，方便查看效果
    if(formatValue){
        $(immTableSheet.jExcel.div).jexcel('setValue', $(immTableSheet.jExcel.div).jexcel('getCell', cellHead), formatValue);
    }else if(fieldCode){
        if(immTableSheet.jExcel.data[cellArr[1]][cellArr[0]].indexOf('=') == -1){
            $(immTableSheet.jExcel.div).jexcel('setValue', $(immTableSheet.jExcel.div).jexcel('getCell', cellHead), fieldCode.split(".")[1]);
        }
    }
    // 是否只读,勾选只读复选框或者定义自定义格式后不再允许编辑内容
    var readOnly = !!formatValue || linkModifyWrap.readOnly.is(':checked');
    immTableSheet.jExcel.cellDataArray[cellArr[1]][cellArr[0]]['readOnly'] = readOnly;
    // 修改只读状态
    if(readOnly){
        $(immTableSheet.jExcel.div).jexcel('getCell', cellHead).addClass('readonly');
    }else{
        $(immTableSheet.jExcel.div).jexcel('getCell', cellHead).removeClass('readonly');
    }
    // 同步更新配置区域显示信息
    var rulesCell = $(".switch-rules .cell."+cellHead);
    if(rulesCell.length){
        var rulesTR = $(rulesCell).parents('tr');
        $(rulesTR).find('.fieldCode').text(fieldCode);
        $(rulesTR).find('.fieldName').text(fieldName);
    }else{
        var data = {
            cell: cellHead,
            fieldCode: fieldCode,
            fieldName: fieldName
        };
        linkViewer.add_link_data(data);
    }
    // 
    padutils.showToolToast("设置成功", {zIndex: 99999}, {toolToastCloseTime: 3e3}),
    linkModifyWrap.wrap.hide();
});

// 内容编辑框inputboxwrap
if (!window.padinputbox){
    var padinputbox = {};
}
padinputbox.box = function() {
    var e = {
        isInputFocuse: !1,
        resizeInputbox: null,
        isInput: !1,
        init: function() {
            var t = $(".cell-input")
              , n = $(".inputboxwrap");
            t.remove(),
            $('<div contenteditable="true" class="cell-input"></div>').appendTo(n),
            t = $(".cell-input");
            var o = $("#inputboxwithpc")
              , i = $("#inputboxResize")
              , r = this;
            if (e.$TEXTAREA = t,
            e.$TEXTAREA_PARENT = o,
            e.TEXTAREA = t.get(0),
            e.TEXTAREA_PARENT = o.get(0),
            window.jExcelID) {
                var a = immTableSheet.jExcel;
                o.show(),
                (t.on("mousedown", function(e) {
                    e.stopPropagation()
                }),
                t.on("keydown", function(n) {
                    var o = n || window.event;
                    // 回车键
                    if (13 === o.keyCode) {
                        if (!(o.altKey || o.ctrlKey || o.metaKey)){
                            return o.preventDefault(), t.blur(), !1;
                        }
                        if (navigator.userAgent.indexOf("msie") > 0)
                            insertHtml("<br />");
                        else {
                            var i = window.getSelection()
                              , r = i.getRangeAt(0)
                              , s = document.createElement("br");
                            r.deleteContents(),
                            r.insertNode(s),
                            r.setStartAfter(s),
                            r.collapse(!1),
                            i.removeAllRanges(),
                            i.addRange(r)
                        }
                    }
                    // Tab键
                    9 === o.keyCode && (o.preventDefault(), t.blur());
                })),
                t.on("input", function(t) {
                    t.preventDefault(),
                    e.isInput = !0;
                    var n = t.target.innerText || "";
                    // a && a.setCellEditorValue(n)
                    $(".cell-head").text() && $(immTableSheet.jExcel.div).jexcel('setValue', $(".cell-head").text(), n);
                }),
                i.hover(function() {
                    !r.resizeInputbox && padinputbox && padinputbox.resize && (r.resizeInputbox = new padinputbox.resize,
                    r.resizeInputbox.isEnable = !0,
                    r.resizeInputbox.init(i, o, $("#canvasContainer22")))
                }),
                t.focus(function(){
                    // 锁定jExcel状态
                    $.fn.jexcel.currentBak = $.fn.jexcel.current;
                    $.fn.jexcel.current = null;
                }),
                t.blur(function(){
                    // 解除锁定jExcel状态
                    $.fn.jexcel.current = $.fn.jexcel.currentBak;
                })
            }
        },
        setCellValue: function(t) {
            if (e.isInput)
                return void (e.isInput = !1);
            t = 0 === t ? t + "" : t,
            $(".cell-input").text(t || "")
        },
        bindHotEvents: function(e) {}
    };
    return e
}
padinputbox.resize = function() {
    return {
        is_mouse_down: !1,
        isEnable: !1,
        $inputboxResize: null,
        $resizeObj: null,
        $editor: null,
        init: function(e, t, o) {
            this.isEnable && (this.$inputboxResize = e,
            this.$resizeObj = t,
            this.$editor = o,
            this.registerResize())
        },
        registerResize: function() {
            var e = this;
            this.$inputboxResize.mousedown(function(t) {
                e.is_mouse_down = !0
            }),
            $(document).bind("click mouseup", function(t) {
                e.is_mouse_down && (e.is_mouse_down = !1)
            }),
            $(document).mousemove(function(t) {
                e.doResize(t, !0)
            })
        },
        doResize: function(e, t) {
            var o, n, i, r = parseInt(this.$resizeObj.css("top")), a = parseInt(this.$editor.css("padding-top"));
            this.is_mouse_down && (o = t ? (o = e.clientY - r + 10 > 25 ? e.clientY - r + 10 : 25) <= 75 ? o : 75 : (o = e.clientY + a - r + 10 > 25 ? e.clientY + a - r + 10 : 25) <= 75 ? o : 75,
                this.$resizeObj.height(o),
                this.$resizeObj.css("line-height", o + "px"),
                this.$editor.css("margin-top", o),
                i = this.$editor[0].getBoundingClientRect().top,
                n = window.innerHeight - i - 40,
                this.$editor.css("height", n),$(immTableSheet.jExcel.div).jexcel("resize"),
                // 调整标尺位置
                $.fn.pageLiner && $.fn.pageLiner.resize && $.fn.pageLiner.resize($(immTableSheet.jExcel.div).find('thead .jexcel_label'))
            )
        },
        resetMouseDown: function() {
            this.is_mouse_down = !1
        }
    }
}
var pad_box = new padinputbox.box;
pad_box.init();


$(document).ready(function(){
    // 标题修改
    var e = function(){
        immTableSheet.jExcel.title = $(this).val();
        var span = document.createElement('span');
        $(span).css({
            position: "absolute",
            top: "-200px",
            left: "-200px",
            "font-size": "20px"
        }).html($(this).val());
        $("body").append($(span));
        $(this).css("width", parseInt($(span).width()) + 15 + "px");
        $(span).remove();
    }
    $('#padAloneTitle').on("blur", e),
    $('#padAloneTitle').on("keydown", e),
    $("#padAloneTitle").data("tooltip", "重命名").val(immTableSheet.jExcel.title).trigger("blur");
    padutils.tooltip("#padAloneTitle", {tooltipClass: "editor-state-tip"});
    padutils.tooltip("#editor-last-changed", {tooltipClass: "editor-state-tip"});
    // 
    $(immTableSheet.jExcel.div).on("foucs", "td.edition input", function(){
        toolbarController.disabled();
    });
});




// 右键菜单  后期优化
var menu = function(e, t, n) {
    "use strict";
    var f = void 0
        , d = !!window.navigator.userAgent.match(/(TencentDocs)/i)
        , h = null != window.navigator.userAgent.match(/iPad/i)
        , p = new w
        , g = new w
        , m = {
            all: [{
                name: "copy",
                validClass: "component-vaild menu-component",
                invalidClass: "component-invaild menu-component",
                init: function(e) {
                    e.className = this.validClass,
                    e.innerText = "复制"
                },
                callback: function() {
                    f.doCopy(),
                    window.updateUndoRedoButtonState && window.updateUndoRedoButtonState()
                }
            }]
        }
        , v = {
            all: [
                {
                    name: "cut",
                    validClass: "component-vaild menu-component",
                    invalidClass: "component-invaild menu-component",
                    init: function(e) {
                        e.className = this.validClass,
                        e.innerText = "剪切";
                        // var t = !!f.excel.selectData.pickShape;
                        // h && t && (e.style.display = "none")
                    },
                    callback: function() {
                        window.immTableSheet.sheetHot.copyPaste.doCopy("cut"),
                        window.updateUndoRedoButtonState && window.updateUndoRedoButtonState()
                    }
                }, {
                    name: "copy",
                    validClass: "component-vaild menu-component",
                    invalidClass: "component-invaild menu-component",
                    init: function(e) {
                        e.className = this.validClass,
                        e.innerText = "复制";
                        // var t = !!f.excel.selectData.pickShape;
                        // h && t && (e.style.display = "none")
                    },
                    callback: function() {
                        window.immTableSheet.sheetHot.copyPaste.doCopy(),
                        window.updateUndoRedoButtonState && window.updateUndoRedoButtonState()
                    }
                }, {
                    name: "paste",
                    validClass: "component-vaild menu-component",
                    invalidClass: "component-invaild menu-component",
                    init: function(e) {
                        var t = window.immTableSheet.sheetHot.canPaste;
                        e.className = t ? this.validClass : this.invalidClass,
                        e.innerText = "粘贴";
                        var n = !!f.excel.selectData.pickShape;
                        h && n && (e.style.display = "none")
                    },
                    callback: function() {
                        if (d)
                            mqq.invoke("docx", "readSystemClipboard", {}, function(e, t) {
                                if (0 == e) {
                                    var n = JSON.parse(JSON.stringify(t));
                                    f.doClipboardPaste(n)
                                }
                            });
                        else {
                            var e = (0,
                            l.getCopyHTMLType)()
                              , t = (0,
                            l.getCopyPlainCache)().html;
                            window.immTableSheet.sheetHot.copyPaste.isImageTextPaste(e, t) ? window.immTableSheet.sheetHot.copyPaste.doImageTextPaste(e, t) : window.immTableSheet.sheetHot.copyPaste.doPaste(null, t),
                            window.updateUndoRedoButtonState && window.updateUndoRedoButtonState()
                        }
                        localStorage && null == localStorage.getItem("pasteTip") && (localStorage.setItem("pasteTip", "true"),
                        padutils.hideToolToast(),
                        padutils.toolToast("右键不支持粘贴外部内容，请使用Ctrl+V粘贴。", void 0, {
                            isNeedBtn: !0,
                            btnText: "我知道了",
                            btnTextAfter: "",
                            btnCallBack: function() {
                                padutils.hideToolToast()
                            }
                        }))
                    }
                }, {
                    name: "copyLine",
                    validClass: "component-vaild menu-component",
                    invalidClass: "component-invaild menu-component",
                    init: function(e) {
                        var t = window.immTableSheet.sheetHot.canPaste
                          , n = !f.excel.selectData.select.selectCol && !f.excel.selectData.select.selectRow
                          , r = t && "cut" == (0,
                        a.getCacheData)().pasteType
                          , o = window.immTableSheet.sheetHot.getState().get("filter") && null != window.immTableSheet.sheetHot.getState().get("filter").get("range")
                          , i = (0,
                        a.getCacheData)()
                          , l = i && i.from && i.from.yRange && i.from.yRange[1] - i.from.yRange[0] + 1 == f.excel.rowCount
                          , s = i && i.from && i.from.xRange && i.from.xRange[1] - i.from.xRange[0] + 1 == f.excel.colCount
                          , u = !(l || s || i && 1 == i.isSingle && 0 == i.isMerge);
                        e.className = t ? this.validClass : this.invalidClass,
                        (!t || n || r || u || o) && (e.style.display = "none"),
                        e.innerText = "插入复制的单元格";
                        var c = !!f.excel.selectData.pickShape;
                        h && c && (e.style.display = "none")
                    },
                    callback: function() {
                        var e = (0,
                        a.getCacheData)();
                        f.excel.selectData.select.selectRow ? (immTableSheet.sheetHot.actionCenter.createRowCol(immTableSheet.sheetHot, f.excel, {
                            type: "above",
                            amount: e.changes.length || 1,
                            pasteData: e
                        }),
                        window.updateUndoRedoButtonState && window.updateUndoRedoButtonState()) : f.excel.selectData.select.selectCol && (immTableSheet.sheetHot.actionCenter.createRowCol(immTableSheet.sheetHot, f.excel, {
                            type: "left",
                            amount: e.changes[0].length || 1,
                            pasteData: e
                        }),
                        window.updateUndoRedoButtonState && window.updateUndoRedoButtonState())
                    }
                }, {
                    name: "copyCutLine",
                    validClass: "component-vaild menu-component",
                    invalidClass: "component-invaild menu-component",
                    init: function(e) {
                        var t = window.immTableSheet.sheetHot.canPaste
                          , n = !f.excel.selectData.select.selectCol && !f.excel.selectData.select.selectRow
                          , r = t && "cut" == (0,
                        a.getCacheData)().pasteType
                          , o = window.immTableSheet.sheetHot.getState().get("filter") && null != window.immTableSheet.sheetHot.getState().get("filter").get("range")
                          , i = (0,
                        a.getCacheData)()
                          , l = i && i.from && i.from.yRange && i.from.yRange[1] - i.from.yRange[0] + 1 == f.excel.rowCount
                          , s = i && i.from && i.from.xRange && i.from.xRange[1] - i.from.xRange[0] + 1 == f.excel.colCount
                          , u = !(l || s || i && 1 == i.isSingle && 0 == i.isMerge);
                        e.className = t ? this.validClass : this.invalidClass,
                        (!t || n || !r || o || u) && (e.style.display = "none"),
                        e.innerText = "插入剪切的单元格";
                        var c = !!f.excel.selectData.pickShape;
                        h && c && (e.style.display = "none")
                    },
                    callback: function() {
                        var e = (0,
                        a.getCacheData)()
                          , t = "cut" == (0,
                        a.getCacheData)().pasteType;
                        if (e && 1 == e.isSingle && 0 == e.isMerge) {
                            if (f.excel.selectData.select.selectRow && t) {
                                var n = e.from.xRange[0]
                                  , r = f.excel.selectData.select.yRange[0];
                                if (f.excel.tableData.data[r] && f.excel.tableData.data[r][n]) {
                                    var o = f.excel.tableData.data[r][n];
                                    if (o.merged && !(o.parent.y >= r && o.parent.y < r + 1))
                                        return void showDialog("提示", "您不能执行与合并单元格相交的粘贴，建议取消合并单元格。")
                                }
                                immTableSheet.sheetHot.actionCenter.createRowCol(immTableSheet.sheetHot, f.excel, {
                                    type: "above",
                                    amount: 1,
                                    nostyle: !0
                                });
                                var i = {
                                    xRange: [e.from.xRange[0], e.from.xRange[1]],
                                    yRange: [f.excel.selectData.select.yRange[0], f.excel.selectData.select.yRange[0]]
                                };
                                (0,
                                a.onPaste)(i, immTableSheet.sheetHot, e),
                                f.excel.closeCopy(),
                                e && (window.immTableSheet.sheetHot.canvasView.canPaste = !1),
                                window.updateUndoRedoButtonState && window.updateUndoRedoButtonState()
                            } else if (f.excel.selectData.select.selectCol && t) {
                                n = f.excel.selectData.select.xRange[0],
                                r = e.from.yRange[0];
                                if (f.excel.tableData.data[r] && f.excel.tableData.data[r][n]) {
                                    var l = f.excel.tableData.data[r][n];
                                    if (l.merged && !(l.parent.x >= n && l.parent.x < n + 1))
                                        return void showDialog("提示", "您不能执行与合并单元格相交的粘贴，建议取消合并单元格。")
                                }
                                immTableSheet.sheetHot.actionCenter.createRowCol(immTableSheet.sheetHot, f.excel, {
                                    type: "left",
                                    amount: 1,
                                    nostyle: !0
                                });
                                var s = {
                                    xRange: [f.excel.selectData.select.xRange[0], f.excel.selectData.select.xRange[0]],
                                    yRange: [e.from.yRange[0], e.from.yRange[1]]
                                };
                                setTimeout(function() {
                                    (0,
                                    a.onPaste)(s, immTableSheet.sheetHot, e),
                                    f.excel.closeCopy()
                                }, 300),
                                e && (window.immTableSheet.sheetHot.canvasView.canPaste = !1),
                                window.updateUndoRedoButtonState && window.updateUndoRedoButtonState()
                            }
                        } else if (e && e.from.xRange && e.from.xRange[1] - e.from.xRange[0] + 1 == f.excel.colCount) {
                            if (f.excel.selectData.select.yRange[0] > e.from.yRange[0] && f.excel.selectData.select.yRange[0] <= e.from.yRange[1])
                                return void showDialog("提示", "无法粘贴，因为复制区域与粘贴区域大小不一致。");
                            if (f.excel.selectData.select.selectCol)
                                return void showDialog("提示", "无法粘贴，因为复制区域与粘贴区域大小不一致。");
                            f.excel.selectData.select.selectRow && t && (immTableSheet.sheetHot.actionCenter.createRowCol(immTableSheet.sheetHot, f.excel, {
                                type: "above",
                                amount: e.changes.length || 1,
                                pasteData: e
                            }),
                            window.updateUndoRedoButtonState && window.updateUndoRedoButtonState())
                        } else {
                            if (!e || !e.from.yRange || e.from.yRange[1] - e.from.yRange[0] + 1 != f.excel.rowCount)
                                return void showDialog("提示", "无法粘贴，因为复制区域与粘贴区域大小不一致。");
                            if (f.excel.selectData.select.xRange[0] > e.from.xRange[0] && f.excel.selectData.select.xRange[0] <= e.from.xRange[1])
                                return void showDialog("提示", "无法粘贴，因为复制区域与粘贴区域大小不一致。");
                            if (f.excel.selectData.select.selectRow)
                                return void showDialog("提示", "无法粘贴，因为复制区域与粘贴区域大小不一致。");
                            f.excel.selectData.select.selectCol && t && (immTableSheet.sheetHot.actionCenter.createRowCol(immTableSheet.sheetHot, f.excel, {
                                type: "left",
                                amount: e.changes[0].length || 1,
                                pasteData: e
                            }),
                            window.updateUndoRedoButtonState && window.updateUndoRedoButtonState())
                        }
                    }
                }, {
                    name: "line",
                    class: "component-line",
                    init: function(e) {
                        var t = !!f.excel.selectData.pickShape;
                        h && t && (e.style.display = "none")
                    }
                }/*, {
                    name: "insertRowAbove",
                    validClass: "component-vaild menu-component",
                    invalidClass: "component-invaild menu-component",
                    init: function(e) {
                        var t = !f.excel.selectData.select.selectCol;
                        e.className = t ? this.validClass : this.invalidClass,
                        t && !x(immTableSheet.sheetHot, "row") || (e.style.display = "none");
                        var n = document.createTextNode("插入");
                        p.setValue(b("row"));
                        var r = document.createElement("div")
                          , o = document.createTextNode("行");
                        r.style.float = "right",
                        r.appendChild(p.dom),
                        r.appendChild(o),
                        e.appendChild(n),
                        e.appendChild(r);
                        var i = !!f.excel.selectData.pickShape;
                        h && i && (e.style.display = "none")
                    },
                    callback: function() {
                        var e = Number(p.getValue());
                        ((window.immTableSheet && immTableSheet.sheetHot.countRows()) + e) * (window.immTableSheet && immTableSheet.sheetHot.countCols()) < s.MAX_CELL ? (immTableSheet.sheetHot.actionCenter.createRowCol(immTableSheet.sheetHot, f.excel, {
                            type: "above",
                            amount: e
                        }),
                        window.updateUndoRedoButtonState && window.updateUndoRedoButtonState()) : window.$ && window.$.Custom_Dialog && window.$.Custom_Dialog("已达最大行列限制，是否为你新建一个工作表？", {
                            type: top.$.Custom_Dialog.Type.Question,
                            title: "插入行列失败",
                            overlay_close: !1,
                            buttons: [{
                                caption: "取消",
                                callback: function() {
                                    return !0
                                }
                            }, {
                                caption: "新建",
                                callback: function() {
                                    return window.getPadMultiSheetController && window.getPadMultiSheetController().addNewSheet(),
                                    !0
                                }
                            }]
                        })
                    }
                }, {
                    name: "insertColLeft",
                    validClass: "component-vaild menu-component",
                    invalidClass: "component-invaild menu-component",
                    init: function(e) {
                        var t = !f.excel.selectData.select.selectRow;
                        e.className = t ? this.validClass : this.invalidClass,
                        t && !x(immTableSheet.sheetHot, "col") || (e.style.display = "none");
                        var n = document.createTextNode("插入");
                        g.setValue(b("col"));
                        var r = document.createElement("div")
                          , o = document.createTextNode("列");
                        r.style.float = "right",
                        r.appendChild(g.dom),
                        r.appendChild(o),
                        e.appendChild(n),
                        e.appendChild(r);
                        var i = !!f.excel.selectData.pickShape;
                        h && i && (e.style.display = "none")
                    },
                    callback: function() {
                        var e = Number(g.getValue())
                          , t = window.immTableSheet && immTableSheet.sheetHot.countRows();
                        ((window.immTableSheet && immTableSheet.sheetHot.countCols()) + e) * t < s.MAX_CELL ? (immTableSheet.sheetHot.actionCenter.createRowCol(immTableSheet.sheetHot, f.excel, {
                            type: "left",
                            amount: Number(g.getValue())
                        }),
                        window.updateUndoRedoButtonState && window.updateUndoRedoButtonState()) : window.$ && window.$.Custom_Dialog && window.$.Custom_Dialog("已达最大行列限制，是否为你新建一个工作表？", {
                            type: top.$.Custom_Dialog.Type.Question,
                            title: "插入行列失败",
                            overlay_close: !1,
                            buttons: [{
                                caption: "取消",
                                callback: function() {
                                    return !0
                                }
                            }, {
                                caption: "新建",
                                callback: function() {
                                    return window.getPadMultiSheetController && window.getPadMultiSheetController().addNewSheet(),
                                    !0
                                }
                            }]
                        })
                    }
                }, {
                    name: "hideRow",
                    validClass: "component-vaild menu-component",
                    invalidClass: "component-invaild menu-component",
                    init: function(e) {
                        var t = f.excel.selectData.select.selectRow;
                        e.className = this.validClass,
                        t || (e.style.display = "none"),
                        e.innerText = "隐藏行";
                        var n = !!f.excel.selectData.pickShape;
                        h && n && (e.style.display = "none")
                    },
                    callback: function() {
                        immTableSheet.sheetHot.actionCenter.hideRowCol(immTableSheet.sheetHot, f.excel, {
                            type: "row"
                        }),
                        window.updateUndoRedoButtonState && window.updateUndoRedoButtonState()
                    }
                }, {
                    name: "showRow",
                    validClass: "component-vaild menu-component",
                    invalidClass: "component-invaild menu-component",
                    init: function(e) {
                        var t = f.excel.selectData.select
                          , n = f.excel.tableData.hideRowMap
                          , r = t.selectRow
                          , o = t.yRange[0]
                          , i = t.yRange[1]
                          , a = !1;
                        for (var l in n)
                            +l >= o && +l <= i && (a = !0);
                        e.style.display = "none",
                        e.className = this.validClass,
                        r && a && (e.style.display = "block"),
                        e.innerText = "取消隐藏行";
                        var s = !!f.excel.selectData.pickShape;
                        h && s && (e.style.display = "none")
                    },
                    callback: function() {
                        var e = []
                          , t = f.excel.selectData.select
                          , n = f.excel.tableData.hideRowMap
                          , r = (t.selectRow,
                        t.yRange[0])
                          , o = t.yRange[1];
                        for (var i in n)
                            +i >= r && +i <= o && e.push(+i);
                        immTableSheet.sheetHot.actionCenter.showRowCol(immTableSheet.sheetHot, f.excel, {
                            type: "row",
                            list: e
                        }),
                        window.updateUndoRedoButtonState && window.updateUndoRedoButtonState()
                    }
                }, {
                    name: "hideCol",
                    validClass: "component-vaild menu-component",
                    invalidClass: "component-invaild menu-component",
                    init: function(e) {
                        var t = f.excel.selectData.select.selectCol;
                        e.className = this.validClass,
                        t || (e.style.display = "none"),
                        e.innerText = "隐藏列";
                        var n = !!f.excel.selectData.pickShape;
                        h && n && (e.style.display = "none")
                    },
                    callback: function() {
                        immTableSheet.sheetHot.actionCenter.hideRowCol(immTableSheet.sheetHot, f.excel, {
                            type: "col"
                        }),
                        window.updateUndoRedoButtonState && window.updateUndoRedoButtonState()
                    }
                }, {
                    name: "showCol",
                    validClass: "component-vaild menu-component",
                    invalidClass: "component-invaild menu-component",
                    init: function(e) {
                        var t = f.excel.selectData.select
                          , n = f.excel.tableData.hideColMap
                          , r = t.selectCol
                          , o = t.xRange[0]
                          , i = t.xRange[1]
                          , a = !1;
                        for (var l in n)
                            +l >= o && +l <= i && (a = !0);
                        e.style.display = "none",
                        e.className = this.validClass,
                        r && a && (e.style.display = "block"),
                        e.innerText = "取消隐藏列";
                        var s = !!f.excel.selectData.pickShape;
                        h && s && (e.style.display = "none")
                    },
                    callback: function() {
                        var e = []
                          , t = f.excel.selectData.select
                          , n = f.excel.tableData.hideColMap
                          , r = (t.selectCol,
                        t.xRange[0])
                          , o = t.xRange[1];
                        for (var i in n)
                            +i >= r && +i <= o && e.push(+i);
                        immTableSheet.sheetHot.actionCenter.showRowCol(immTableSheet.sheetHot, f.excel, {
                            type: "col",
                            list: e
                        }),
                        window.updateUndoRedoButtonState && window.updateUndoRedoButtonState()
                    }
                }, {
                    name: "line",
                    class: "component-line",
                    init: function(e) {
                        var t = !!f.excel.selectData.pickShape;
                        h && t && (e.style.display = "none")
                    }
                }, {
                    name: "removeRow",
                    validClass: "component-vaild menu-component",
                    invalidClass: "component-invaild menu-component",
                    init: function(e) {
                        var t = !f.excel.selectData.select.selectCol;
                        e.className = t ? this.validClass : this.invalidClass,
                        e.innerText = "删除所在行";
                        var n = !!f.excel.selectData.pickShape;
                        h && n && (e.style.display = "none")
                    },
                    callback: function() {
                        immTableSheet.sheetHot.actionCenter.removeRowCol(immTableSheet.sheetHot, f.excel, "row"),
                        window.updateUndoRedoButtonState && window.updateUndoRedoButtonState()
                    }
                }, {
                    name: "removeCol",
                    validClass: "component-vaild menu-component",
                    invalidClass: "component-invaild menu-component",
                    init: function(e) {
                        var t = !f.excel.selectData.select.selectRow;
                        e.className = t ? this.validClass : this.invalidClass,
                        e.innerText = "删除所在列";
                        var n = !!f.excel.selectData.pickShape;
                        h && n && (e.style.display = "none")
                    },
                    callback: function() {
                        immTableSheet.sheetHot.actionCenter.removeRowCol(immTableSheet.sheetHot, f.excel, "col"),
                        window.updateUndoRedoButtonState && window.updateUndoRedoButtonState()
                    }
                }, {
                    name: "rowResize",
                    validClass: "component-vaild menu-component",
                    hideClass: "component-hide menu-component",
                    invalidClass: "component-invaild menu-component",
                    init: function(e) {
                        var t = f.excel.selectData.select
                          , n = t.xRange[1] - t.xRange[0] + 1 !== f.excel.tableData.col.widths.length;
                        e.className = n ? this.hideClass : this.validClass,
                        e.innerText = "设置行高";
                        var r = !!f.excel.selectData.pickShape;
                        h && r && (e.style.display = "none")
                    },
                    callback: function() {
                        var e = f.excel.configData.rowHeight
                          , t = f.excel.selectData.select
                          , n = _(t.yRange[0], t.yRange[1], "height");
                        r.default.create({
                            title: "设置行高",
                            info: "行高 ( 默认值: " + e + "像素 )",
                            defaultValue: n,
                            min: e,
                            errorInfo: "有效范围：" + e + "-1000",
                            callback: function(e) {
                                for (var n = {
                                    from: t.yRange[0],
                                    to: t.yRange[1]
                                }, r = [], o = n.from, a = n.to; o <= a; o++)
                                    r.push(f.excel.tableData.row.getHeight(o));
                                immTableSheet.sheetHot.undoRedo.dispatchAction(new i.default(n,e,r)),
                                window.updateUndoRedoButtonState && window.updateUndoRedoButtonState()
                            }
                        })
                    }
                }, {
                    name: "columnResize",
                    validClass: "component-vaild menu-component",
                    hideClass: "component-hide menu-component",
                    invalidClass: "component-invaild menu-component",
                    init: function(e) {
                        var t = f.excel.selectData.select
                          , n = t.yRange[1] - t.yRange[0] + 1 !== f.excel.tableData.row.heights.length;
                        e.className = n ? this.hideClass : this.validClass,
                        e.innerText = "设置列宽";
                        var r = !!f.excel.selectData.pickShape;
                        h && r && (e.style.display = "none")
                    },
                    callback: function() {
                        var e = f.excel.selectData.select
                          , t = _(e.xRange[0], e.xRange[1], "width");
                        r.default.create({
                            title: "设置列宽",
                            info: "列宽 ( 默认值: " + f.excel.configData.colWidth + "像素 )",
                            defaultValue: t,
                            min: 19,
                            errorInfo: "有效范围：19-1000",
                            callback: function(t) {
                                for (var n = {
                                    from: e.xRange[0],
                                    to: e.xRange[1]
                                }, r = [], i = n.from, a = n.to; i <= a; i++)
                                    r.push(f.excel.tableData.col.getWidth(i));
                                immTableSheet.sheetHot.undoRedo.dispatchAction(new o.default(n,t,r)),
                                window.updateUndoRedoButtonState && window.updateUndoRedoButtonState()
                            }
                        })
                    }
                }, {
                    name: "line",
                    class: "component-line",
                    init: function(e) {
                        var t = !!f.excel.selectData.pickShape;
                        h && t && (e.style.display = "none")
                    }
                }, {
                    name: "mergeCell",
                    validClass: "component-vaild menu-component",
                    invalidClass: "component-invaild menu-component",
                    init: function(e) {
                        var t = void 0
                          , n = void 0;
                        switch (f.rangeType(f.excel.selectData.select)) {
                        case "single":
                            t = !1,
                            n = "合并单元格";
                            break;
                        case "merge":
                            t = !0,
                            n = "取消合并单元格";
                            break;
                        case "range":
                            t = !0,
                            n = "合并单元格"
                        }
                        e.className = t ? this.validClass : this.invalidClass,
                        e.innerText = n;
                        var r = !!f.excel.selectData.pickShape;
                        h && r && (e.style.display = "none")
                    },
                    callback: function() {
                        var e = f.rangeType(f.excel.selectData.select);
                        immTableSheet.sheetHot.actionCenter.mergeCells(immTableSheet.sheetHot, f.excel, "merge" === e ? "unmerge" : "merge"),
                        window.updateUndoRedoButtonState && window.updateUndoRedoButtonState()
                    }
                }, {
                    name: "line",
                    class: "component-line",
                    init: function(e) {
                        var t = !!f.excel.selectData.pickShape;
                        h && t && (e.style.display = "none")
                    }
                }, {
                    name: "insertImage",
                    validClass: "component-vaild menu-component",
                    invalidClass: "component-invaild menu-component",
                    init: function(e) {
                        e.className = this.validClass,
                        e.innerText = "插入图片";
                        var t = !!f.excel.selectData.pickShape;
                        h && t && (e.style.display = "none")
                    },
                    callback: function() {
                        document.getElementById("sheet-insert-image") || ($("#toobarMoreButton").trigger("click"),
                        $("#toobarMoreButton").trigger("click")),
                        document.getElementById("sheet-insert-image").click()
                    }
                }, {
                    name: "insertLink",
                    validClass: "component-vaild menu-component",
                    invalidClass: "component-invaild menu-component",
                    init: function(e) {
                        var t = !0
                          , n = immTableSheet.sheetHot.getSelectedRange();
                        n.isSingle() || n.isSingleMergeCell() || (t = !1),
                        e.className = t ? this.validClass : this.invalidClass,
                        e.innerText = "插入链接";
                        var r = !!f.excel.selectData.pickShape;
                        h && r && (e.style.display = "none")
                    },
                    callback: function() {
                        immTableSheet.sheetHot.canvasView.excel.insertLink(),
                        window.updateUndoRedoButtonState && window.updateUndoRedoButtonState()
                    }
                }, {
                    name: "line",
                    class: "component-line",
                    init: function(e) {
                        var t = !!f.excel.selectData.pickShape;
                        h && t && (e.style.display = "none")
                    }
                }, {
                    name: "cleanStyles",
                    validClass: "component-vaild menu-component",
                    invalidClass: "component-invaild menu-component",
                    init: function(e) {
                        e.className = this.validClass,
                        e.innerText = "清除格式";
                        var t = !!f.excel.selectData.pickShape;
                        h && t && (e.style.display = "none")
                    },
                    callback: function() {
                        immTableSheet.sheetHot.actionCenter.cleanStyle(immTableSheet.sheetHot, f.excel),
                        window.updateUndoRedoButtonState && window.updateUndoRedoButtonState()
                    }
                }, {
                    name: "cleanContents",
                    validClass: "component-vaild menu-component",
                    invalidClass: "component-invaild menu-component",
                    init: function(e) {
                        e.className = this.validClass,
                        e.innerText = "清除内容";
                        var t = !!f.excel.selectData.pickShape;
                        h && t && (e.style.display = "none")
                    },
                    callback: function() {
                        immTableSheet.sheetHot.actionCenter.cleanContent(immTableSheet.sheetHot, f.excel),
                        window.updateUndoRedoButtonState && window.updateUndoRedoButtonState()
                    }
                }, {
                    name: "deleteShape",
                    validClass: "component-vaild menu-component",
                    invalidClass: "component-invaild menu-component",
                    init: function(e) {
                        e.className = this.validClass,
                        e.innerText = "删除";
                        var t = !!f.excel.selectData.pickShape;
                        h && t || (e.style.display = "none")
                    },
                    callback: function() {
                        var e = !!f.excel.selectData.pickShape;
                        if (h && e) {
                            var t = window.immTableSheet.sheetHot.canvasView.excel.selectData.pickShape
                              , n = immTableSheet.sheetHot.getStore().getState().getIn(["shapes", t]);
                            n && (window.immTableSheet.sheetHot.undoRedo.dispatchAction(new u.default(n.toJS())),
                            window.updateUndoRedoButtonState && window.updateUndoRedoButtonState())
                        } else
                            immTableSheet.sheetHot.actionCenter.cleanContent(immTableSheet.sheetHot, f.excel),
                            window.updateUndoRedoButtonState && window.updateUndoRedoButtonState()
                    }
                }, {
                    name: "line",
                    class: "component-line"
                }, {
                    name: "setFormat",
                    validClass: "component-vaild menu-component",
                    invalidClass: "component-invaild menu-component",
                    init: function(e) {
                        e.className = this.validClass,
                        e.innerText = "设置单元格格式"
                    },
                    callback: function() {
                        immTableSheet.sheetHot && immTableSheet.sheetHot.popupDataformatDialogFrom("contextmenu")
                    }
                }*/
            ]
        }
        , y = ".menu-component { padding: 2px 30px; font-size: 14px; height: 32px; line-height: 2; user-select: none;}.component-vaild {height: 30px;cursor: pointer;}.component-vaild:hover {background-color: #e9e9e9;}.component-invaild {height: 30px; color: #999999; cursor: default; pointer-events: none;} .component-hide {display: none;} .component-line {height: 1px; width: 100%; background: #cccccc; opacity: .4;}";
    
    function w(e) {
        this.initDom = function() {
            var e = document.createElement("input");
            return e.style.border = "none",
            e.style.width = "34px",
            e.style.height = "20px",
            e.style.border = "1px solid #dddddd",
            e.style.textAlign = "center",
            e.style.marginRight = "4px",
            e
        }
        ,
        this.bindEvent = function() {
            var e = this
              , t = this;
            this.dom.addEventListener("click", function(e) {
                e.preventDefault(),
                e.stopPropagation()
            }),
            this.dom.addEventListener("focus", function() {
                t.dom.select(),
                t.dom.style.border = "1px solid #0188fb"
            }),
            this.dom.addEventListener("blur", function() {
                t.dom.style.border = "1px solid #dddddd"
            }),
            this.dom.addEventListener("input", function(e) {
                var n = e.target.value;
                /[0-9]+$/.test(n) || (n = void 0 === n || "" === n ? "" : t.defaultValue || 1),
                n > 999 && (n = 999),
                t.dom.value = n
            }),
            this.dom.addEventListener("keydown", function(t) {
                if (13 === t.keyCode) {
                    t.preventDefault();
                    var n = document.createEvent("HTMLEvents");
                    n.initEvent("click", !1, !0),
                    e.dom.parentNode.parentNode.dispatchEvent(n)
                }
            })
        }
        ,
        this.dom = this.initDom(),
        this.bindEvent()
    }
    function b(e) {
        var t;
        t = "row" === e ? "y" : "x";
        var n = f.excel.selectData.select
          , r = 1;
        if (n) {
            var o = n[t + "Range"][0];
            r = n[t + "Range"][1] - o + 1
        }
        return r
    }
    function _(e, t, n) {
        var r;
        if ("width" === n ? r = f.excel.tableData.col.widths : "height" === n && (r = f.excel.tableData.row.heights),
        !r)
            return "";
        for (var o = r[e], i = e; i <= t; i++)
            if (o !== r[i])
                return "";
        return o
    }
    function x(e, t) {
        var n = void 0
          , r = e.canvasView.excel
          , o = r.selectData.select
          , i = r.colCount
          , a = r.rowCount
          , l = e.getState().get("mergeCells")
          , s = !1;
        if (l && l.size > 0) {
            n = l.toJS();
            for (var u = 0; u < n.length; u++)
                "row" === t ? 0 === n[u].row && n[u].rowspan === a && n[u].col + n[u].colspan >= o.xRange[0] && n[u].col <= o.xRange[1] && (s = !0) : 0 === n[u].col && n[u].colspan === i && n[u].row + n[u].rowspan >= o.yRange[0] && n[u].row <= o.yRange[1] && (s = !0)
        }
        return s
    }
    w.prototype.getValue = function() {
        return this.dom.value
    }
    ,
    w.prototype.setType = function(e) {
        ["button", "checkbox", "file", "hidden", "image", "password", "radio", "reset", "submit", "text", "number"].indexOf(e) > -1 ? this.dom.type = e : this.dom.type = "text"
    }
    ,
    w.prototype.setValue = function(e) {
        isNaN(e) || (this.dom.value = this.defaultValue = e)
    }
    ,
    t.default = function(e) {
        return f = e,
        {
            config: window.gEditable ? v : m,
            styles: y
        }
    }
    return t.default();
}
var menuContent = new menu(window, {});


var contextMenu = {
    _show: function(o) {
        this._hide(),
        this._addComponent(this.wrap),
        this.wrap.style.display = "block";
        var e, t;
        e = o.pageX,
        t = o.pageY;
        var n = void 0
          , c = this.wrap.getBoundingClientRect();
        this.wrap.style.maxHeight = "999px";
        var u = void 0;
        var n = {
            left: e,
            top: t
        }

        // u =  immTableSheet.jExcel.div.height() - t,
        // this.boxWrapper.style.maxHeight = this.wrap.style.maxHeight = u + "px",
        // this.boxWrapper.style.overflowY = "auto",


        this.wrap.style.left = n.left + "px",
        this.wrap.style.top = n.top + "px",
        document.body.addEventListener("mousedown", contextMenu._hide, !1)
    }
    ,_hide: function() {
        for (contextMenu.wrap.style.display = "none"; contextMenu.wrap.children[0]; )
            contextMenu.wrap.removeChild(contextMenu.wrap.children[0]);
        document.body.removeEventListener("mousedown", contextMenu._hide, !1)
    }
    ,init: function(e) {
        var t = document.createElement("div");
        t.className = "table-menu",
        this.wrap = t,
        $("body").append(t)
    }
    ,initStyle: function() {
        $("style:last").append(menuContent.styles)
    }
    ,initEvent: function() {
        // s.default.listen("tableMenuShow", this._show),
        // s.default.listen("tableMenuHide", this._hide)
    }
    ,initMouse: function() {
        this.wrap.addEventListener("contextmenu", function(e) {
            e.preventDefault()
        }),
        this.wrap.addEventListener("mousedown", function(e) {
            e.stopPropagation()
        })
    }
    ,_addComponent: function(e) {
        var t = this
          , o = void 0
          , n = void 0
          , r = menuContent.config.all
          , a = void 0
          , s = document.createElement("div");
        for (this.boxWrapper = s,
        n = 0; n < r.length; n++)
            a = r[n],
            o = document.createElement("div"),
            a.init ? a.init(o) : this.className = a.class,
            a.callback && ($(this.wrap).on("mousedown", function(e) {
                e.stopPropagation()
            }),
            $(this.wrap).on("click", function() {
                t._hide(),
                t.stage.input.focus()
            }),
            $(this.wrap).on("click", a.callback.bind(a))),
            s.appendChild(o);
        e.appendChild(s)
    }
}

// $(document).ready(function(){
//     contextMenu.initStyle(),
//     contextMenu.init(),
//     contextMenu.initMouse(),
//     contextMenu.initEvent(),
//     // 右键菜单
//     // $.fn.jexcel.defaults['my'].contextMenu=
//     immTableSheet.jExcel.contextMenu = function(e){
//         contextMenu._show(e);
//         e.preventDefault();
//     }
// })



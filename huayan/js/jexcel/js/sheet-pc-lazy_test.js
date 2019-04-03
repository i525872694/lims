var padeditbar = function() {
    var e = {
        closeDropDown: function(e) {
            var t = document.createEvent("CustomEvent");
            t.initCustomEvent("closedropdown", !0, !0, {}),
            e.target.dispatchEvent(t)
        },
        init: function() {
            function t(t, n) {
                return function(o) {
                    return o.preventDefault ? o.preventDefault() : o.returnValue = !1,
                    e.closeDropDown(o),
                    n ? e.toolbarDblClick(t, o) : e.toolbarClick(t, o),
                    !1
                }
            }
            $("#toolbar").mousedown(function(e) {
                e.preventDefault ? e.preventDefault() : e.returnValue = !1
            }),
            $("#sheet-savebutton").click(t("sheet-save")),
            $("#sheet-undobutton").click(t("sheet-undo")),
            $("#sheet-redobutton").click(t("sheet-redo")),
            $("#sheet-format-painter").click(t("sheet-format-painter")),
            $("#sheet-format-painter").dblclick(t("sheet-format-painter", !0)),
            $("#sheet-clear-format").click(t("sheet-clear-format")),
            $("#sheet-exportbutton").click(t("sheet-export")),
            $("#sheet-border").click(t("sheet-border")),
            $("#sheet-formatcurrencychsbutton").click(t("sheet-format-currency-chs")),
            $("#sheet-formatpercentagebutton").click(t("sheet-format-percentage-int")),
            $("#sheet-addthousandsseparatorbutton").click(t("sheet-add-thousands-separator")),
            $("#sheet-decimaldecreasebutton").click(t("sheet-decimal-decrease")),
            $("#sheet-decimalincreasebutton").click(t("sheet-decimal-increase")),
            $("#sheet-boldbutton").click(t("sheet-bold")),
            $("#sheet-italicsbutton").click(t("sheet-italic")),
            $("#sheet-underlinebutton").click(t("sheet-underline")),
            $("#sheet-strikebutton").click(t("sheet-strike")),
            $("#sheet-horizontal-align").click(t("sheet-horizontal-align")),
            $("#sheet-vertical-align").click(t("sheet-vertical-align")),
            $("#sheet-font-size").click(t("sheet-font-size")),
            $("#sheet-font-family").click(t("sheet-font-family")),
            $("#sheet-calculate-more").click(t("sheet-calculate")),
            $("#sheet-calculate-button").click(function() {
                padeditbar_utils.sheetButtonClick("sheet-formula-sum")
            }),
            $("#sheet-format").click(t("sheet-format")),
            $("#sheet-merge-cell").click(t("sheet-merge")),
            $("#sheet-insert-link").click(t("sheet-insert-link")),
            $("#sheet-textwrapbutton").click(t("sheet-textwrap")),
            $("#sheet-search").click(t("sheet-search")),
            $("#savebutton").mousedown(t("save")),
            $("#printbutton").click(t("print")),
            $("#undobutton").mousedown(t("undo")),
            $("#redobutton").mousedown(t("redo")),
            $("#formatPainterButton").click(t("formatpainter")),
            $("#formatPainterButton").dblclick(t("formatpainter", !0)),
            $("#clearFormattingButton").mousedown(t("clearformatting")),
            $("#boldbutton").mousedown(t("bold")),
            $("#italicsbutton").mousedown(t("italic")),
            $("#underlinebutton").mousedown(t("underline")),
            $("#strikebutton").mousedown(t("strikethrough")),
            $("#font-family").mousedown(t("font-family")),
            $("#font-size").mousedown(t("font-size")),
            $("#horizontal-align").mousedown(t("horizontal-align")),
            $("#bulletlistbutton").mousedown(t("insertunorderedlist")),
            $("#bulletlistmore").mousedown(t("moreunorderedlist")),
            $("#numberedlistbutton").mousedown(t("insertorderedlist")),
            $("#numberedlistmore").mousedown(t("moreorderedlist")),
            $("#taskbutton").mousedown(t("inserttasklist")),
            $("#commentbutton").mousedown(t("insertcomment")),
            $("#indentbutton").mousedown(t("indent")),
            $("#outdentbutton").mousedown(t("outdent")),
            $("#lineheight").mousedown(t("lineheight")),
            $("#codebutton").click(t("code")),
            $("#insertline").mousedown(t("insertline")),
            $("#inserttable").mousedown(t("inserttable")),
            $("#translate").mousedown(t("translate")),
            $("#insertlink").mousedown(t("insertlink")),
            $("#watermark").mousedown(t("watermark")),
            $("#attachbutton").click(function() {}),
            $("#editbutton").click(function() {
                $("#toolbar").toggleClass("open"),
                $("#editbutton").toggleClass("open"),
                $("body").toggleClass("edit-mode")
            }),
            $("body").on("click", e.editMode),
            $("#createpadentry").on("focus", function() {
                $("body").addClass("search-focused")
            }),
            $("#createpadentry").on("blur", function() {
                setTimeout(function() {
                    $("body").removeClass("search-focused")
                }, 100)
            }),
            $(".toolbar-main").on("click", function(e) {
                $("#toolbar [data-type!=" + $(e.currentTarget).attr("data-type") + "]").removeClass("toolbar-show"),
                $("#toolbar [data-type=" + $(e.currentTarget).attr("data-type") + "]").toggleClass("toolbar-show")
            }),
            $("#editbar .editbarbutton").attr("unselectable", "on"),
            $("#editbar").removeClass("disabledtoolbar").addClass("enabledtoolbar")
        },
        isToolbarControllerEnabled: function() {
            return toolbarController.isEnable()
        },
        hide: function() {
            try {
                toolbarController.hide()
            } catch (e) {
                $("#toolbar").css("display", "none")
            }
        },
        show: function() {
            try {
                toolbarController.show()
            } catch (e) {
                $("#toolbar").css("display", "inline-block")
            }
        },
        disable: function() {
            window.toolbarController && toolbarController.disable()
        },
        enable: function() {
            window.toolbarController && toolbarController.enable()
        },
        dataReport: function(e) {
            // 
        },
        isToolbarClickable: function(e) {
            if (window.toolbarController)
                return !!toolbarController.isToolBarButtonEnable(e)
        },
        toolbarClick: function(t, n) {
            var o = this.isToolbarClickable(t);
            if (!o){
                return o;
            }
            e.isToolbarControllerEnabled() 
            && (
                    "save" === t 
                    || "sheet-save" === t 
                    || "innersheetsave" === t 
                        ? onceCtrlS(n) : "print" === t 
                            ? e.print() : padeditor.ace.callWithAce(
                                function(o) {
                if ("font-family" == t)
                    toolbarController.toolbarbuttons.fontfamilybutton.handle(n);
                else if ("font-size" == t)
                    toolbarController.toolbarbuttons.fontsizebutton.handle(n);
                else if ("insertimage" == t)
                    o.insertImage();
                else if ("insertline" == t)
                    o.insertline();
                else if ("inserttable" == t)
                    o.inserttable();
                else if ("insertlink" == t)
                    o.insertlink();
                else if ("lineheight" == t)
                    toolbarController.toolbarbuttons.lineheightbutton.handle(n);
                else if ("undo" == t || "redo" == t)
                    o.doUndoRedo(t);
                else if ("formatpainter" == t)
                    n.stopPropagation(),
                    o.isFormatPainterModeLocked() || o.isFormatPainterMode() ? o.exitFormatPainterMode() : o.enterFormatPainterMode();
                else if ("clearformatting" == t)
                    o.clearAllNodeFormat();
                else if ("fontsizezoom" == t)
                    ;
                else if ("fontsizeshrink" == t)
                    ;
                else if ("export" == t)
                    o.doExport2();
                else if ("horizontal-align" == t)
                    toolbarController.toolbarbuttons.horizontalalign.handle(n);
                else if ("insertunorderedlist" == t)
                    o.doInsertUnorderedList();
                else if ("moreunorderedlist" == t)
                    toolbarController.toolbarbuttons.bulletlistbutton.handle(n);
                else if ("insertorderedlist" == t)
                    o.doInsertOrderedList();
                else if ("moreorderedlist" == t)
                    toolbarController.toolbarbuttons.numberedlistbutton.handle(n);
                else if ("inserttasklist" == t)
                    o.doInsertTaskList();
                else if ("code" == t)
                    o.doInsertCodeList();
                else if ("insertcomment" == t)
                    o.doInsertComment();
                else if ("alignleft" == t)
                    padeditor.ace.callWithAce(function(e) {
                        e.doTextAlign("left")
                    });
                else if ("aligncenter" == t)
                    padeditor.ace.callWithAce(function(e) {
                        e.doTextAlign("center")
                    });
                else if ("alignright" == t)
                    padeditor.ace.callWithAce(function(e) {
                        e.doTextAlign("right")
                    });
                else if ("alignjustify" == t)
                    padeditor.ace.callWithAce(function(e) {
                        e.doTextAlign("justify")
                    });
                else if ("indent" == t)
                    o.doSetIndentLevel({
                        bias: 1
                    });
                else if ("outdent" == t)
                    o.doSetIndentLevel({
                        bias: -1
                    });
                else if ("sheet-horizontal-align" == t)
                    toolbarController.toolbarbuttons.sheethorizontalalign.handle(n);
                else if ("sheet-vertical-align" == t)
                    toolbarController.toolbarbuttons.sheetverticalalign.handle(n);
                else if ("sheet-font-size" == t)
                    toolbarController.toolbarbuttons.sheetfontsize.handle(n);
                else if ("sheet-font-family" == t)
                    toolbarController.toolbarbuttons.sheetfontfamily.handle(n);
                else if ("sheet-calculate" == t)
                    toolbarController.toolbarbuttons.sheetcalculate.handle(n);
                else if ("sheet-format" == t)
                    toolbarController.toolbarbuttons.sheetformat.handle(n);
                else if ("sheet-border" == t)
                    toolbarController.toolbarbuttons.sheetborder.handle(n);
                else if ("clearauthorship" == t) {
                    if (o.getRep().selStart && o.getRep().selEnd && !o.isCaret())
                        o.setAttributeOnSelection("author", "");
                    else if (window.confirm("Clear authorship colors on entire document?")) {
                        var r = o.getRep().lines;
                        o.performDocumentApplyAttributesToRange([0, 0], [r.length, r[r.length - 1]], [["author", ""]])
                    }
                } else if ("header-1" == t || "header-2" == t)
                    o.doSetHeadingLevel(t.split("-")[1]);
                else if ("sheet-insert-link" == t || "sheet-bold" == t || "sheet-italic" == t || "sheet-underline" == t || "sheet-strike" == t || "sheet-undo" == t || "sheet-redo" == t || "sheet-merge" == t || "sheet-fix-row-col" == t || "sheet-format-painter" == t || "sheet-clear-format" == t)
                    padeditbar_utils.sheetButtonClick(t);
                else if ("sheet-add-thousands-separator" === t)
                    padeditbar_utils.sheetButtonClick(t);
                else if ("sheet-decimal-decrease" === t)
                    padeditbar_utils.sheetButtonClick(t);
                else if ("sheet-decimal-increase" === t)
                    padeditbar_utils.sheetButtonClick(t);
                else if ("sheet-format-currency-chs" === t)
                    padeditbar_utils.sheetButtonClick(t);
                else if ("sheet-format-percentage" === t)
                    padeditbar_utils.sheetButtonClick(t);
                else if ("sheet-format-percentage-int" === t)
                    padeditbar_utils.sheetButtonClick(t);
                else if ("sheet-textwrap" === t)
                    padeditbar_utils.sheetButtonClick(t);
                else
                    "sheet-search" === t ? (console.log("sheet-search 工具栏点击"),
                    searchCenter.isShow() ? searchCenter.hide() : searchCenter.show()) : "watermark" === t && (watermarkDialog.show(),
                    tdwReport({
                        opername: "doc_person",
                        module: "watermark",
                        action: "clk_entry"
                    }),
                    window.QReport && QReport.monitor(3201347));
                "font-size" != t && e.setSelectionState(t),
                e.dataReport(t)
            }, t, !0))
        },
        toolbarDblClick: function(t, n) {
            var o = this.isToolbarClickable(t);
            if (!o)
                return o;
            if(e.isToolbarControllerEnabled()){
                switch (t) {
                    case "formatpainter":
                        n.stopPropagation(),
                        e.enterFormatPainterMode(),
                        e.lockFormatPainterMode();
                        break;
                    case "sheet-format-painter":
                        padeditbar_utils.sheetButtonDblClick(t);
                        break;
                }
            }
        },
        forEachAttr: function(e, t) {
            for (var n in e)
                if (n == t)
                    return !0;
            return !1
        },
        setSelectionState: function(e) {
            window.immTableSheet.selectionState = e;
            // console.log('setSelectionState:'+e)
            // 
        },
        editMode: function(e) {
            !($(e.target).parents("body > header").length || $(e.target).parents("#padeditor").length || $(e.target).parents("#padsidebar").length || $(e.target).parents("#mainmodals").length || $(e.target).is("#modaloverlay") || $(e.target).parents("#modaloverlay").length || $(e.target).is(".lightbox-container") || $(e.target).parents(".lightbox-container").length) || $(e.target).is("#padeditor") || $(e.target).is("#createpadform2") || $(e.target).parents("#createpadform2").length ? $("body").removeClass("edit-mode") : ($(e.target).is("#editor") || $(e.target).parents("#editor").length) && $("body").addClass("edit-mode")
        },
        setSyncStatus: function(e) {
            "done" == e && $("#last-saved-timestamp").attr("time", dateFormat(new Date))
        },
        print: function() {
            window.navigator.userAgent.indexOf("Safari") > -1 && window.navigator.userAgent.indexOf("Chrome") < 1 && padeditor.ace.getRoot().blur(),
            window.print && window.print()
        }
    };
    return e
}()
  , padmobilecooperation = function() {
    return {}
}();


function ToolBarButton(e) {
    this.toolbarbutton = null,
    this.icon = null,
    this.initToolBarButton(e)
}
function ToolBarMenuButton(e, t, o, n, i) {
    this.data = null,
    this.dropdown = null,
    this.container = null,
    this.isSeparation = !1,
    this.needCheck = !1,
    this.toolBarMenuButton = null,
    this.initToolBarMenuButton(e, t, o, n, i)
}
function ToolBarImageMenuButton(e, t, o, n) {
    this.initToolBarImageMenuButton(e, t, o, n)
}
function ToolBarContentMenuButton(e, t, o, n, i) {
    this.initToolBatContentMenuButton(e, t, o, n, i)
}
function ToolBarFontColorButton(e) {
    this.initToolBarFontColorMenuButton(e)
}
function ToolBarFontBgColorButton(e) {
    this.initToolBarFontBgColorMenuButton(e)
}
function ToolBarPaintBrushColorButton(e) {
    this.initToolBarPaintBrushColorMenuButton(e)
}
function ToolBarBorderButton(e, t, o) {
    this.container = t,
    this.onItemClickCallback = o,
    this.initToolBarBorderButton(e, o)
}
function ToolBarGridMenuButton(e, t, o, n, i) {
    this.initToolBarGridMenuButton(e, t, o, n, i)
}


ToolBarButton.prototype._initStatus = function() {
    this.toolbarbutton && this.toolbarbutton.on("mousedown", function() {
        this.toolbarbutton.addClass("pressed").one("mouseup", function() {
            this.toolbarbutton.removeClass("pressed")
        }.bind(this)),
        this.toolbarbutton.one("mouseleave", function() {
            this.toolbarbutton.removeClass("pressed")
        }.bind(this));
        var e = $("#tooltip"),
        t = e && e.data("target-dom");
        t && this.toolbarbutton.find(t) && e.remove()
    }.bind(this))
},
ToolBarButton.prototype.isEnable = function() {
    return ! this.toolbarbutton.hasClass("toolbar-button-wrapper-disabled")
},
ToolBarButton.prototype.toggleCheck = function() {
    this.toolbarbutton.hasClass("toolbar-button-wrapper-checked") ? this.toolbarbutton.removeClass("toolbar-button-wrapper-checked") : this.toolbarbutton.addClass("toolbar-button-wrapper-checked")
},
ToolBarButton.prototype.check = function() {
    this.toolbarbutton.hasClass("toolbar-button-wrapper-checked") || this.toolbarbutton.addClass("toolbar-button-wrapper-checked")
},
ToolBarButton.prototype.uncheck = function() {
    this.toolbarbutton.hasClass("toolbar-button-wrapper-checked") && this.toolbarbutton.removeClass("toolbar-button-wrapper-checked")
},
ToolBarButton.prototype.isChecked = function() {
    return !! this.toolbarbutton.hasClass("toolbar-button-wrapper-checked")
},
ToolBarButton.prototype.disable = function() {
    this.toolbarbutton.hasClass("toolbar-button-wrapper-disabled") || this.toolbarbutton.addClass("toolbar-button-wrapper-disabled")
},
ToolBarButton.prototype.enable = function() {
    this.toolbarbutton.hasClass("toolbar-button-wrapper-disabled") && this.toolbarbutton.removeClass("toolbar-button-wrapper-disabled")
},
ToolBarButton.prototype.isHidden = function() {
    return ! this.toolbarbutton.hasClass("toolbar-button-wrapper-hidden")
},
ToolBarButton.prototype.hide = function() {
    this.toolbarbutton.hasClass("toolbar-button-wrapper-hidden") || this.toolbarbutton.addClass("toolbar-button-wrapper-hidden")
},
ToolBarButton.prototype.show = function() {
    this.toolbarbutton.hasClass("toolbar-button-wrapper-hidden") && this.toolbarbutton.removeClass("toolbar-button-wrapper-hidden")
},
ToolBarButton.prototype.removeIconClassName = function(e) {
    this.icon.removeClass(e)
},
ToolBarButton.prototype.addIconClassName = function(e) {
    this.icon.addClass(e)
},
ToolBarButton.prototype.resetIconClassName = function() {
    this.icon.attr("class", ""),
    this.icon.addClass("docx-icon-container docx-icon-img-container")
},
ToolBarButton.prototype.initToolBarButton = function(e) {
    if (!e || !e.find) return null;
    this.toolbarbutton = $(e),
    this.icon = $(this.toolbarbutton.find(".docx-icon-container")[0]),
    this._initStatus()
},
ToolBarButton.prototype.registerSelectionState = function(e) {
    e.key || (e.key = this.toolbarbutton.attr("id"));
    try {
        console.log(selectionState)
        selectionState.registerSelectionState(e)
    } catch(t) {
        $(window).bind("SelectionStateInitFinished",
        function() {
            selectionState.registerSelectionState(e)
        })
    }
},
ToolBarMenuButton.prototype = new ToolBarButton, ToolBarMenuButton.prototype.initToolBarMenuButton = function(e, t, o, n, i) {
    this.data = t,
    this.container = o,
    this.toolBarMenuButton = e,
    this.compatibility = n,
    this.needCheck = i,
    this.initToolBarButton(e)
},
ToolBarMenuButton.prototype.toggleIcon = function(e) {},
ToolBarMenuButton.prototype.dropdownClickHandler = function(e) {
    var t = this;
    return function(o) {
        t.toggleIcon(e),
        !e.clickSetNoIdentity && t.toolbarbutton.attr("identity", e.identity),
        t.dropdown && t.dropdown.hide(),
        "function" == typeof e.rowclickcallback ? e.rowclickcallback(o) : "function" == typeof e.clickcallback && e.clickcallback(o)
    }
},
ToolBarMenuButton.prototype.prepareDropDownData = function(e) {},
ToolBarMenuButton.prototype.initDropDown = function(e, t, o, n) {
    this.dropdown = new DropDown(e, t, o, n);
    var i = this;
    $(window).bind("SheetMouseDown", function(e) {
        i.dropdown.hide()
    }),
    $(window).bind("SheetScroll", function(e) {
        i.dropdown.hide()
    }),
    $(window).bind("mousedown", function(e) {
        3 === e.which && i.dropdown.hide()
    }),
    $(window).bind("AfterSelectionState", function(e) {
        i.dropdown.refreshSelItem()
    })
},
ToolBarMenuButton.prototype.handle = function(e) {
    if (!this.isSeparation || !$(e.target).parents(".toolbar-menu-button-icon").length && e.target != this.toolbarbutton.find(".toolbar-menu-button-icon")[0] && (e.target != this.toolbarbutton[0] || e.target == this.toolbarbutton[0] && e.offsetX > 22)) {
        if ($("#tooltip").remove(), !this.dropdown) {
            var t = this.prepareDropDownData(this.data);
            this.initDropDown(t, this.container, this.toolBarMenuButton, this.needCheck)
        }
        "lineheight" === this.toolbarbutton.attr("id") && console.log("click lineheightbutton");
        this.dropdown.toggleDropDown()
    } else {
        this.dropdown && this.dropdown.hide();
        for (var l = 0; l < this.data.length; l++) {
            var c = this.data[l];
            if (this.toolbarbutton.attr("identity") == c.identity) {
                if ("function" == typeof c.iconclickcallback) {
                    c.iconclickcallback();
                    break
                }
                if ("function" == typeof c.clickcallback) {
                    c.clickcallback();
                    break
                }
            }
        }
    }
},
ToolBarMenuButton.prototype.check = function(e) {
    if (e && e.indexOf("lineheight") > -1) return this.toggleIcon(this.data[0]),
    this.toolbarbutton.attr("identity", e.split("|")[0] || e),
    this.data[0];
    if (e && e.indexOf("line-list-type") > -1) return this.toolbarbutton.hasClass("toolbar-button-wrapper-checked") || this.toolbarbutton.addClass("toolbar-button-wrapper-checked"),
    this.data[1];
    for (var t = null,
    o = 0; o < this.data.length; o++) {
        if (t = this.data[o], "string" == typeof e && "string" == typeof t.identity ? e.toLowerCase() === t.identity.toLowerCase() : e === t.identity) return this.toggleIcon(t),
        this.toolbarbutton.attr("identity", t.identity),
        t;
        t = null
    }
    return this.compatibility && null !== (t = this.compatibility(e)) ? (this.toggleIcon(t), this.toolbarbutton.attr("identity", t.identity), t) : (this.resetIconClassName(), "toolbar-lineheight-group" != this.container.attr("id") && this.resetIconClassName(), this.toolbarbutton.attr("identity", ""), null)
},
ToolBarImageMenuButton.prototype = new ToolBarMenuButton, ToolBarImageMenuButton.prototype.initToolBarImageMenuButton = function(e, t, o, n) {
    this.initToolBarMenuButton(e, t, o, null, n)
},
ToolBarImageMenuButton.prototype.prepareDropDownData = function(e) {
    for (var t = [], o = 0; o < e.length; o++) {
        var n = e[o];
        if ("sheet-format-custom" !== n.identity && "sheet-format-special" !== n.identity) {
            var i = {
                iconClsName: n.rowIconClsName,
                image: n.image,
                content: n.content,
                fontFamily: n.fontFamily,
                hover: n.hover,
                template: n.template,
                clickcallback: this.dropdownClickHandler(n),
                contentClsName: n.contentClsName,
                identity: n.identity
            };
            t.push(i)
        }
    }
    return t
},
ToolBarImageMenuButton.prototype.toggleIcon = function(e) {
    this.resetIconClassName(),
    this.addIconClassName(e.iconClassName)
},
ToolBarContentMenuButton.prototype = new ToolBarMenuButton, ToolBarContentMenuButton.prototype.initToolBatContentMenuButton = function(e, t, o, n, i) {
    this.initToolBarMenuButton(e, t, o, n, i)
},
ToolBarContentMenuButton.prototype.prepareDropDownData = function(e) {
    for (var t = [], o = 0; o < e.length; o++) {
        var n = e[o];
        if ("sheet-format-custom" !== n.identity && "sheet-format-special" !== n.identity) {
            var i = {
                iconClsName: n.rowIconClsName,
                image: n.image,
                content: n.content,
                hover: n.hover,
                fontFamily: n.fontFamily,
                disable: n.disable,
                clickcallback: this.dropdownClickHandler(n),
                title: n.title,
                detail: n.detail,
                template: n.template,
                contentClsName: n.contentClsName,
                identity: n.identity
            };
            t.push(i)
        }
    }
    return t
},
ToolBarContentMenuButton.prototype.toggleIcon = function(e) {
    this.resetIconContent(),
    this.setIconContent(e),
    e.fontFamily && this.setFontFamily(e)
},
ToolBarContentMenuButton.prototype.resetIconClassName = function() {},
ToolBarContentMenuButton.prototype.resetIconContent = function() {
    this.icon[0].innerText = ""
},
ToolBarContentMenuButton.prototype.setIconContent = function(e) {
    this.icon[0].innerText = e.iconContent || e.content || ""
},
ToolBarContentMenuButton.prototype.setFontFamily = function(e) {
    this.toolbarbutton.find(".toolbar-menu-button-icon").css("font-family", e.fontFamily)
},
ToolBarContentMenuButton.prototype.check = function(e) {
    var t = ToolBarMenuButton.prototype.check.call(this, e);
    return t || this.resetIconContent(),
    t
},
ToolBarFontColorButton.prototype = new ToolBarButton, ToolBarFontColorButton.prototype.initToolBarFontColorMenuButton = function(e) {
    this.initToolBarButton(e)
},
ToolBarFontColorButton.prototype.check = function(e) {
    e = e || "transparent"
},
ToolBarFontBgColorButton.prototype = new ToolBarButton, ToolBarFontBgColorButton.prototype.initToolBarFontBgColorMenuButton = function(e) {
    this.initToolBarButton(e)
},
ToolBarPaintBrushColorButton.prototype = new ToolBarButton, ToolBarPaintBrushColorButton.prototype.initToolBarPaintBrushColorMenuButton = function(e) {
    this.initToolBarButton(e)
},
ToolBarPaintBrushColorButton.prototype.check = function(e) {
    e = e || "transparent"
},
ToolBarBorderButton.prototype = new ToolBarButton, ToolBarBorderButton.prototype.initToolBarBorderButton = function(e) {
    this.initToolBarButton(e)
},
ToolBarBorderButton.prototype.initBorderMenu = function(e) {
    this.borderMenu = new BorderMenu(e, this.onItemClickCallback)
},
ToolBarBorderButton.prototype.check = function(e) {},
ToolBarBorderButton.prototype.handle = function(e) {
    this.borderMenu ? this.borderMenu.togglePalette(e) : (this.initBorderMenu(this.container), this.borderMenu.showPalette())
},
ToolBarGridMenuButton.prototype = new ToolBarMenuButton, ToolBarGridMenuButton.prototype.initToolBarGridMenuButton = function(e, t, o, n, i) {
    this.columns = n,
    this.initToolBarMenuButton(e, t, o, null, i)
},
ToolBarGridMenuButton.prototype.prepareDropDownData = function(e) {
    for (var t = [], o = 0; o < e.length; o++) {
        var n = e[o],
        i = {
            content: n.content,
            templateFunc: n.templateFunc,
            clickcallback: this.dropdownClickHandler(n),
            identity: n.identity
        };
        t.push(i)
    }
    return t
},
ToolBarGridMenuButton.prototype.initDropDown = function(e, t, o, n) {
    this.dropdown = new DropDownGrid(e, t, this.columns, o, n);
    var i = this;
    $(window).bind("SheetMouseDown",
    function(e) {
        i.dropdown.hide()
    }),
    $(window).bind("SheetScroll",
    function(e) {
        i.dropdown.hide()
    })
},
ToolBarGridMenuButton.prototype.toggleIcon = function(e) {};
var isMac = !!window.navigator.platform.match(/(Mac)/i), isPad = !!window.navigator.platform.match(/(iPad)/i), fontFamilyConfigDataWin = [["Microsoft YaHei", "微软雅黑"], ["SimSun", "宋体"], ["SimHei", "黑体"], ["FangSong", "仿宋"], ["KaiTi", "楷体"], ["DFKai-SB", "标楷体"], ["STFangsong", "华文仿宋"], ["STKaiti", "华文楷体"], ["STSong", "华文宋体"], ["NSimSun", "新宋体"], ["Microsoft JhengHei", "微软正黑体"], ["PMingLiU", "新明细体"], "Arial", "Times New Roman", "Times New Roman Special", "Calibri", "Comic Sans MS", "Courier New", "Georgia", "Microsoft Uighur", "Impact", "Trebuchet MS", "Verdana", "Abadi MT Condensed", "Agency FB", "Aharoni", "Aldhabi", "Algerian", "Almanac MT", "American Uncial", "Andale Mono", "Andalus", "Andy", "AngsanaUPC", "Angsana New", "Aparajita", "Arabic Transparent", "Arabic Typesetting", "Arial Black", "Arial Narrow", "Arial Narrow Special", "Arial Rounded MT", "Arial Special", "Arial Unicode MS", "Augsburger Initials", "Baskerville Old Face", "Batang", "BatangChe", "Bauhaus", "Beesknees ITC", "Bell MT", "Berlin Sans FB", "Bernard MT Condensed", "Bickley Script", "Blackadder ITC", "Bodoni MT", "Bodoni MT Condensed", "Bon Apetit MT", "Bookman Old Style", "Bookshelf Symbol", "Book Antiqua", "Bradley Hand ITC", "Braggadocio", "BriemScript", "Britannic Bold", "Broadway", "BrowalliaUPC", "Browallia New", "Brush Script MT", "Californian FB", "Calisto MT", "Cambria", "Cambria Math", "Candara", "Cariadings", "Castellar", "Centaur", "Century", "Century Gothic", "Century Schoolbook", "Chiller", "Colonna MT", "Consolas", "Constantia", "Contemporary Brush", "Cooper Black", "Copperplate Gothic", "Corbel", "CordiaUPC", "Cordia New", "Curlz MT", "DaunPenh", "David", "Desdemona", "DilleniaUPC", "Directions MT", "DokChampa", "Dotum", "DotumChe", "Ebrima", "Eckmann", "Edda", "Edwardian Script ITC", "Elephant", "Engravers MT", "Enviro", "Eras ITC", "Estrangelo Edessa", "EucrosiaUPC", "Euphemia", "Eurostile", "Felix Titling", "Fine Hand", "Fixed Miriam Transparent", "Flexure", "Footlight MT", "Forte", "Franklin Gothic", "Franklin Gothic Medium", "FrankRuehl", "FreesiaUPC", "Freestyle Script", "French Script MT", "Futura", "Gabriola", "Gadugi", "Garamond", "Garamond MT", "Georgia Ref", "Gigi", "Gill Sans MT", "Gill Sans MT Condensed", "Gisha", "Gloucester", "Goudy Old Style", "Goudy Stout", "Gradl", "Gulim", "GulimChe", "Gungsuh", "GungsuhChe", "Haettenschweiler", "Harlow Solid Italic", "Harrington", "High Tower Text", "Holidays MT", "Imprint MT Shadow", "Informal Roman", "IrisUPC", "Iskoola Pota", "JasmineUPC", "Jokerman", "Juice ITC", "Kalinga", "Kartika", "Keystrokes MT", "Khmer UI", "Kino MT", "KodchiangUPC", "Kokila", "Kristen ITC", "Kunstler Script", "Lao UI", "Latha", "LCD", "Leelawadee", "Levenim MT", "LilyUPC", "Lucida Blackletter", "Lucida Bright", "Lucida Bright Math", "Lucida Calligraphy", "Lucida Console", "Lucida Fax", "Lucida Handwriting", "Lucida Sans", "Lucida Sans Typewriter", "Lucida Sans Unicode", "Magneto", "Maiandra GD", "Malgun Gothic", "Malgun Gothic Semilight", "Mangal", "Map Symbols", "Marlett", "Matisse ITC", "Matura MT Script Capitals", "McZee", "Mead Bold", "Meiryo", "Meiryo UI", "Mercurius Script MT Bold", "Microsoft Himalaya", "Microsoft JhengHei UI", "Microsoft JhengHei UI Light", "Microsoft New Tai Lue", "Microsoft PhagsPa", "Microsoft Sans Serif", "Microsoft Tai Le", "Microsoft YaHei UI", "Microsoft YaHei UI Light", "Microsoft Yi Baiti", "MingLiU-ExtB", "MingLiU_HKSCS-ExtB", "MingLiU_HKSCS", "Miriam", "Miriam Fixed", "Mistral", "Minion Web", "Modern No.", "Mongolian Baiti", "Monotype.com", "Monotype Corsiva", "Monotype Sorts", "MoolBoran", "MS Gothic", "MS LineDraw", "MS Mincho", "MS Outlook", "MS PGothic", "MS PMincho", "MS Reference", "MS UI Gothic", "MT Extra", "MV Boli", "Myanmar Text", "Narkisim", "News Gothic MT", "New Caledonia", "Niagara", "Nirmala UI", "Nyala", "OCR-B-Digits", "OCRB", "OCR A Extended", "Old English Text MT", "Onyx", "Palace Script MT", "Palatino Linotype", "Papyrus", "Parade", "Parchment", "Parties MT", "Peignot Medium", "Pepita MT", "Perpetua", "Perpetua Titling MT", "Placard Condensed", "Plantagenet Cherokee", "Playbill", "PMingLiU-ExtB", "Poor Richard", "Pristina", "Raavi", "Rage Italic", "Ransom", "Ravie", "RefSpecialty", "Rockwell", "Rockwell Condensed", "Rockwell Extra Bold", "Rod", "Runic MT Condensed", "Sakkal Majalla", "Script MT Bold", "Segoe Chess", "Segoe Print", "Segoe Pseudo", "Segoe Script", "Segoe UI Symbol", "Shonar Bangla", "Showcard Gothic", "Shruti", "Signs MT", "Simplified Arabic Fixed", "Snap ITC", "Sports MT", "Stencil", "Stop", "Sylfaen", "Symbol", "Tahoma", "Tempo Grunge", "Tempus Sans ITC", "Temp Installer Font", "Traditional Arabic", "Transport MT", "Tunga", "Tw Cen MT", "Tw Cen MT Condensed", "Urdu Typesetting", "Utsaah", "Vacation MT", "Vani", "Verdana Ref", "Vijaya", "Viner Hand ITC", "Vivaldi", "Vixar ASCI", "Vladimir Script", "Vrinda", "Webdings", "Westminster", "Wide Latin", "Wingdings", "Yu Gothic", "Yu Gothic Light", "Yu Gothic Medium", "Yu Gothic UI", "Yu Gothic UI Light", "Yu Gothic UI Semibold", "Yu Gothic UI Semilight"], fontFamilyConfigDataMac = [["Microsoft YaHei", "微软雅黑"], ["SimSun", "宋体"], ["SimHei", "黑体"], ["Hiragino Sans GB", "冬青黑简体中文"], ["PingFang SC", "苹方"], ["STSong", "华文宋体"], ["STFangsong", "华文仿宋"], ["STKaiti", "华文楷体"], "Arial", "Times New Roman", "Calibri", "Comic Sans MS", "Courier New", "Georgia", "Microsoft Uighur", "Impact", "Trebuchet MS", "Verdana", "Al Bayan", "Al Nile", "Al Tarikh", "American Typewriter", "Andale Mono", "Arial Black", "Arial Hebrew", "Arial Hebrew Scholar", "Arial Narrow", "Arial Rounded MT Bold", "Arial Unicode MS", "Athelas", "Avenir", "Avenir Next", "Avenir Next Condensed", "Ayuthaya", "Baghdad", "Bangla MN", "Bangla Sangam MN", "Baskerville", "Beirut", "Big Caslon", "Bodoni", "Bodoni 72 Oldstyle", "Bodoni 72 Smallcaps", "Bodoni Ornaments", "Bradley Hand", "Brush Script MT", "Chalkboard", "Chalkboard SE", "Chalkduster", "Charter", "Cochin", "Copperplate", "Corsiva Hebrew", "Courier", "Damascus", "DecoType Naskh", "Devanagari MT", "Devanagari Sangam MN", "Didot", "DIN Alternate", "DIN Condensed", "Diwan Kufi", "Diwan Thuluth", "Euphemia UCAS", "Farah", "Farisi", "Futura", "GB18030 Bitmap", "Geeza Pro", "Geneva", "Gill Sans", "Gujarati MT", "Gujarati Sangam MN", "Gurmukhi MN", "Gurmukhi MT", "Gurmukhi Sangam MN", "Hei", "Helvetica", "Helvetica Neue", "Herculanum", "Hiragino Kaku Gothic Pro", "Hiragino Kaku Gothic ProN", "Hiragino Kaku Gothic Std", "Hiragino Kaku Gothic StdN", "Hiragino Maru Gothic Pro", "Hiragino Maru Gothic ProN", "Hiragino Mincho Pro", "Hiragino Mincho ProN", "Hiragino Sans", "Hoefler Text", "InaiMathi", "Iowan Old Style", "ITF Devanagari", "ITF Devanagari Marathi", "Kai", "Kailasa", "Kannada MN", "Kannada Sangam MN", "Kefa", "Khmer MN", "Khmer Sangam MN", "Kohinoor Bangla", "Kohinoor Devanagari", "Kohinoor Telugu", "Kokonor", "Krungthep", "KufiStandardGK", "Lao MN", "Lao Sangam MN", "Lucida Grande", "Luminari", "Malayalam MN", "Malayalam Sangam MN", "Marion", "Marker Felt", "Menlo", "Microsoft Sans Serif", "Mishafi", "Mishafi Gold", "Monaco", "Mshtakan", "Muna", "Myanmar MN", "Myanmar Sangam MN", "Nadeem", "New Peninim MT", "Noteworthy", "Noto Nastaliq Urdu", "Optima", "Oriya MN", "Oriya Sangam MN", "Palatino", "Papyrus", "Phosphate", "Plantagenet Cherokee", "PT Mono", "PT Sans", "PT Sans Caption", "PT Sans Narrow", "PT Serif", "PT Serif Caption", "Raanana", "Sana", "Sathu", "Savoye LET", "Seravek", "Shree Devanagari", "SignPainter", "Silom", "Sinhala MN", "Sinhala Sangam MN", "Skia", "Snell Roundhand", "STIXGeneral", "STIXIntegralsD", "STIXIntegralsSm", "STIXIntegralsUp", "STIXIntegralsUpD", "STIXIntegralsUpSm", "STIXNonUnicode", "STIXSizeFiveSym", "STIXSizeFourSym", "STIXSizeOneSym", "STIXSizeThreeSym", "STIXSizeTwoSym", "STIXVariants", "Sukhumvit Set", "Superclarendon", "Symbol", "Tahoma", "Tamil MN", "Tamil Sangam MN", "Telugu MN", "Telugu Sangam MN", "Thonburi", "Times", "Trattatello", "Waseem", "Webdings", "Wingdings", "Zapf Dingbats", "Zapfino", "Apple Braille", "Apple Chancery", "Apple Color Emoji", "Apple SD Gothic Neo", "Apple Symbols", "AppleGothic", "AppleMyungjo"], fontFamilyConfigDataPad = [["Microsoft YaHei", "微软雅黑"], "Times New Roman", "Courier New", "Futrua", "Papyrus", "Rockwell", "Trebuchet MS", "Verdana"], fontFamilyConfigData = isMac ? fontFamilyConfigDataMac: isPad ? fontFamilyConfigDataPad: fontFamilyConfigDataWin;

var ToolBar = function() {
    function e(e, t) {
        var o = !1;
        if (e && e.length && t) for (var n = 0; n < e.length; n++) if (t == e[n]) {
            o = !0;
            break
        }
        return o
    }
    this.toolbarbuttons = {},
    self.toolbar = $("#toolbar"),
    this.iteratethrough = function(t, o) {
        for (var n = Object.getOwnPropertyNames(this.toolbarbuttons), i = 0; i < n.length; i++) {
            var r = this.toolbarbuttons[n[i]];
            r && r[t] && "function" == typeof r[t] && !e(o, n[i]) && r[t]()
        }
    },
    this.disable = function() {
        this.iteratethrough("disable")
    },
    this.enable = function() {
        this.iteratethrough("enable")
    },
    this.hide = function() {
        self.toolbar.removeClass("toolbar-shown").addClass("toolbar-hidden"),
        this.iteratethrough("hide")
    },
    this.show = function() {
        self.toolbar.removeClass("toolbar-hidden").addClass("toolbar-shown"),
        this.iteratethrough("show")
    },
    this.isEnable = function() {
        for (var e = Object.getOwnPropertyNames(this.toolbarbuttons), t = 0; t < e.length; t++) {
            var o = this.toolbarbuttons[e[t]];
            if (o && o.isEnable && o.isHidden && !o.isEnable() && !o.isHidden()) return ! 1
        }
        return ! 0
    },
    this.checkSingle = function(e, t) {
        for (var o = 0; o < t.length; o++) {
            var n = this.toolbarbuttons[t[o]];
            if (n && 0 == e.indexOf(n.toolbarbutton.attr("id"))) return n.check && n.check(e.split("&")[1]),
            o
        }
        return - 1
    },
    this.uncheck = function(e) {
        this.iteratethrough("uncheck", e)
    },
    this.setDefaultCheck = function(e, t) {
        if (e instanceof Array || "string" == typeof e) {
            e = [].concat(e);
            for (var o = 0; o < e.length; o++) for (var n = e[o], i = 0; i < t.length; i++) if ( - 1 != n.indexOf(t[i])) {
                t.splice(i, 1);
                break
            }
            return e.concat(t)
        }
        return e
    },
    this.addIgnoreCheckItem = function(e) {
        this.ignoreCheckList || (this.ignoreCheckList = []),
        this.ignoreCheckList.push(e)
    },
    this.check = function(e) {
        this.uncheck(this.ignoreCheckList);
        var t = Object.getOwnPropertyNames(this.toolbarbuttons);
        if (window.isSheet || "BB08J2" != padeditor.ace.getSubId() || (e = this.setDefaultCheck(e, ["font-family", "font-size", "toolbar-font-color-group", "horizontal-align", "lineheight&lineheight-custome"])), e instanceof Array) for (var o = 0; o < e.length; o++) {
            var n = this.checkSingle(e[o], t); - 1 != n && t.splice(n, 1)
        } else "string" == typeof e ? this.checkSingle(e, t) : this.iteratethrough("check")
    },
    this.registerBaseListeners = function() {
        var e = this;
        $(window).bind("AfterSelectionState", function(t) {
            var o = t.detail.state;
            o = o || [],
            e.check(o)
        })
    }
},
ToolBarSheet = function() {
    var e = {
        "sheet-save": "sheetsavebutton",
        "sheet-undo": "sheetundobutton",
        "sheet-redo": "sheetredobutton",
        "sheet-font-family": "sheetfontfamily",
        "sheet-font-size": "sheetfontsize",
        "sheet-format-percentage": "sheetformatpercentagebutton",
        "sheet-format-currency-chs": "sheetformatcurrencychsbutton",
        "sheet-decimaldecrease": "sheetdecimaldecreasebutton",
        "sheet-decimalincrease": "sheetdecimalincreasebutton",
        "sheet-bold": "sheetboldbutton",
        "sheet-italic": "sheetitalicsbutton",
        "sheet-underline": "sheetunderlinebutton",
        "sheet-strike": "sheetstrikebutton",
        "sheet-horizontal-align": "sheethorizontalalign",
        "sheet-vertical-align": "sheetverticalalign",
        "sheet-merge": "sheetmergecell",
        "sheet-calculate": "sheetcalculate",
        "sheet-border": "sheetborder",
        "sheet-insert-link": "sheetinsertlink",
        "sheet-format": "sheetformat",
        "sheet-format-painter": "sheetformatpainterbutton",
        "sheet-clear-format": "sheetclearformatbutton",
        "sheet-textwrap": "sheettextwrapbutton",
        "sheet-search": "sheetsearch"
    },
    t = {
        init: function() {
            t.inhert(),
            t.stopHeaderPropagation(),
            t.initToolBarButtons(),
            t.registerBaseListeners(),
            t.registerListeners()
        },
        inhert: function() {
            this.temp = ToolBar,
            this.temp(),
            delete this.temp
        },
        stopHeaderPropagation: function() {
            $("body>header").on("mousemove",
            function(e) {
                e.stopPropagation()
            })
        },
        registerListeners: function() {
            var e = this;
            $(window).on("networkOnline", function() {
                // 网络链接成功
            }),
            $(window).on("networkOffline", function() {
                // 网络链接断开
                // e.toolbarbuttons.sheetinsertimage.disable()
            })
        },
        initToolBarButtons: function() {
            t.toolbarbuttons.sheetundobutton = new ToolBarButton($("#sheet-undobutton")),
            t.toolbarbuttons.sheetredobutton = new ToolBarButton($("#sheet-redobutton")),
            t.toolbarbuttons.sheetsavebutton = new ToolBarButton($("#sheet-savebutton")),
            t.initFormatPainterButton(),
            t.initClearFormatButton(),
            t.initFormatPercentageButton(),
            t.initFormatCurrencyChsButton(),
            t.initAddThousandSseparatorButton(),
            t.initDecimalDecreaseButton(),
            t.initDecimalIncreaseButton(),
            t.initBoldButton(),
            t.initItalicsButton(),
            t.initUnderlineButton(),
            t.initStrikeButton(),
            t.initHorizontalAlign(),
            t.initVerticalAlign(),
            t.initFontColorButton(),
            t.initPaintBrushButton(),
            t.initFontSizeButton(),
            t.initFontFamilyButton(),
            t.initMergeCellButton(),
            t.initFormatButton(),
            t.initCalculateButton(),
            t.initBorderButton(),
            t.initLinkButton(),
            t.initTextwrapButton(),
            t.initSearchButton()
        },
        initFormatPainterButton: function() {
            var e = $("#sheet-format-painter");
            t.toolbarbuttons.sheetformatpainterbutton = new ToolBarButton(e),
            t.addIgnoreCheckItem("sheetformatpainterbutton");
            var o = e.attr("data-tooltip");
            function n() {
                immTableSheet && immTableSheet.jExcel && (t.toolbarbuttons.sheetformatpainterbutton.uncheck(),
                $(immTableSheet.div).removeClass("pointer_format"),
                e.attr("data-tooltip", o),
                immTableSheet.actionCenter.isPainting && immTableSheet.actionCenter.closeCopy(),
                immTableSheet.actionCenter.isPainting = !1,
                immTableSheet.actionCenter.isPaintingLocked = !1)
            }
            $(window).on("click", function(e) {
                immTableSheet || immTableSheet.actionCenter.isPaintingLocked || 0 == $(e.target).parents("#sheet-format-painter").length && n()
            }),
            t.checkFormatPainter = function() {
                immTableSheet && immTableSheet.jExcel && (t.toolbarbuttons.sheetformatpainterbutton.check(),
                immTableSheet.actionCenter.doCopy(),
                void 0 !== immTableSheet && $(immTableSheet.div).addClass("pointer_format"),
                e.attr("data-tooltip", "退出格式刷(Esc)"))
            }
            ,
            t.uncheckFormatPainter = n
        },
        initClearFormatButton: function() {
            t.toolbarbuttons.sheetclearformatbutton = new ToolBarButton($("#sheet-clear-format")),
            t.addIgnoreCheckItem("sheetclearformatbutton")
        },
        initFormatPercentageButton: function() {
            t.toolbarbuttons.sheetformatpercentagebutton = new ToolBarButton($("#sheet-formatpercentagebutton"))
        },
        initAddThousandSseparatorButton: function() {
            t.toolbarbuttons.sheetaddthousandsseparatorbutton = new ToolBarButton($("#sheet-addthousandsseparatorbutton"))
        },
        initFormatCurrencyChsButton: function() {
            t.toolbarbuttons.sheetformatcurrencychsbutton = new ToolBarButton($("#sheet-formatcurrencychsbutton"))
        },
        initDecimalDecreaseButton: function() {
            t.toolbarbuttons.sheetdecimaldecreasebutton = new ToolBarButton($("#sheet-decimaldecreasebutton"))
        },
        initDecimalIncreaseButton: function() {
            t.toolbarbuttons.sheetdecimalincreasebutton = new ToolBarButton($("#sheet-decimalincreasebutton"))
        },
        initBoldButton: function() {
            var e = function() {
                "bold" === immTableSheet.sheetHot.getCellStyle().bold ? t.toolbarbuttons.sheetboldbutton.check() : t.toolbarbuttons.sheetboldbutton.uncheck()
            }
            t.toolbarbuttons.sheetboldbutton = new ToolBarButton($("#sheet-boldbutton"));
            $(window.jExcelDiv).bind("mouseup", e),
            $(window).bind("keyup", e);
        },
        initItalicsButton: function() {
            t.toolbarbuttons.sheetitalicsbutton = new ToolBarButton($("#sheet-italicsbutton")),
            t.toolbarbuttons.sheetitalicsbutton.registerSelectionState({
                judge: function(e) {
                    if ("italic" == $(e).css("font-style")) return "sheet-italicsbutton"
                }
            })
        },
        initUnderlineButton: function() {
            t.toolbarbuttons.sheetunderlinebutton = new ToolBarButton($("#sheet-underlinebutton")),
            t.toolbarbuttons.sheetunderlinebutton.registerSelectionState({
                judge: function(e) {
                    if ( - 1 != $(e).css("text-decoration").indexOf("underline")) return "sheet-underlinebutton"
                }
            })
        },
        initStrikeButton: function() {
            t.toolbarbuttons.sheetstrikebutton = new ToolBarButton($("#sheet-strikebutton"))
        },
        initBorderButton: function() {
            t.toolbarbuttons.sheetborder = new ToolBarBorderButton($("#sheet-border"), $("#toolbar-sheet-border-group"), function(e, t) {
                if (e && t) {
                    var o = "sheetborder:" + e + ":" + t;
                    padeditbar_utils.sheetButtonClick(o)
                } else console.log("setborder error borderCmd or selectedColor is null")
            })
        },
        initLinkButton: function() {
            function e() {
                padeditbar_utils.isSingleCell() || padeditbar_utils.isSingleMergeCell() ? t.toolbarbuttons.sheetinsertlink.enable() : t.toolbarbuttons.sheetinsertlink.disable()
            }
            t.toolbarbuttons.sheetinsertlink = new ToolBarButton($("#sheet-insert-link")),
            $(window.jExcelDiv).bind("mouseup", e),
            $(window).bind("keyup", e);
        },
        initHorizontalAlign: function() {
            t.toolbarbuttons.sheethorizontalalign = new ToolBarImageMenuButton($("#sheet-horizontal-align"), [{
                rowIconClsName: "docx-icon-common toolbar-icon-sheet-left-align-row",
                hover: "左对齐",
                identity: "sheet-horizontal-align-left",
                iconClassName: "docx-icon-common toolbar-icon-sheet-left-align",
                clickcallback: function() {
                    padeditbar_utils.sheetButtonClick("sheet-left-align")
                }
            },
            {
                rowIconClsName: "docx-icon-common toolbar-icon-sheet-center-align-row",
                hover: "居中对齐",
                identity: "sheet-horizontal-align-center",
                iconClassName: "docx-icon-common toolbar-icon-sheet-center-align",
                clickcallback: function() {
                    padeditbar_utils.sheetButtonClick("sheet-center-align")
                }
            },
            {
                rowIconClsName: "docx-icon-common toolbar-icon-sheet-right-align-row",
                hover: "右对齐",
                identity: "sheet-horizontal-align-right",
                iconClassName: "docx-icon-common toolbar-icon-sheet-right-align",
                clickcallback: function() {
                    padeditbar_utils.sheetButtonClick("sheet-right-align")
                }
            }], $("#toolbar-sheet-horizontal-align-group")),
            t.toolbarbuttons.sheethorizontalalign.registerSelectionState({
                judge: function(e) {
                    return ["left", "center", "right"].contains($(e).css("text-align")) ? "sheet-horizontal-align&sheet-horizontal-align-" + $(e).css("text-align") : "sheet-horizontal-align&sheet-horizontal-align-left"
                }
            })
        },
        initVerticalAlign: function() {
            t.toolbarbuttons.sheetverticalalign = new ToolBarImageMenuButton($("#sheet-vertical-align"), [{
                rowIconClsName: "docx-icon-common toolbar-icon-sheet-top-align-row",
                hover: "顶端对齐",
                identity: "sheet-vertical-align-top",
                iconClassName: "docx-icon-common toolbar-icon-sheet-top-align",
                clickcallback: function() {
                    padeditbar_utils.sheetButtonClick("sheet-top-align")
                }
            },
            {
                rowIconClsName: "docx-icon-common toolbar-icon-sheet-middle-align-row",
                hover: "居中对齐",
                identity: "sheet-vertical-align-middle",
                iconClassName: "docx-icon-common toolbar-icon-sheet-middle-align",
                clickcallback: function() {
                    padeditbar_utils.sheetButtonClick("sheet-middle-align")
                }
            },
            {
                rowIconClsName: "docx-icon-common toolbar-icon-sheet-bottom-align-row",
                hover: "底端对齐",
                identity: "sheet-vertical-align-bottom",
                iconClassName: "docx-icon-common toolbar-icon-sheet-bottom-align",
                clickcallback: function() {
                    padeditbar_utils.sheetButtonClick("sheet-bottom-align")
                }
            }], $("#toolbar-sheet-vertical-align-group")),
            t.toolbarbuttons.sheetverticalalign.registerSelectionState({
                judge: function(e) {
                    return ["top", "middle", "bottom"].contains($(e).css("vertical-align")) ? "sheet-vertical-align&sheet-vertical-align-" + $(e).css("vertical-align") : "sheet-vertical-align&sheet-vertical-align-middle"
                }
            })
        },
        initFontColorButton: function() {
            t.toolbarbuttons.sheetfontcolorbutton = new ToolBarFontColorButton($("#toolbar-sheet-font-color-group"));
            $("#sheet-fontcolor-button").colorPicker({
                colorButton: t.toolbarbuttons.sheetfontcolorbutton, onColorChange: function(e) {
                    var t = "fontcolor:" + e;
                    padeditbar_utils.sheetButtonClick(t)
                }
            });
            $("#toolbar-colorPicker-picker-wrapper").on("mousedown", function(e) {
                e.preventDefault()
            })
        },
        initPaintBrushButton: function() {
            t.toolbarbuttons.sheetpaintbrushcolorbutton = new ToolBarPaintBrushColorButton($("#toolbar-sheet-paint-brush-color-group"));
            $("#sheet-paint-brush-color-button").colorPicker({
                colorButton: t.toolbarbuttons.sheetpaintbrushcolorbutton,
                pickerType: 2, onColorChange: function(e) {
                    var t = "cellcolor:" + e;
                    padeditbar_utils.sheetButtonClick(t)
                }
            });
            $("#toolbar-colorPicker-picker-paint-wrapper").on("mousedown", function(e) {
                e.preventDefault()
            })
        },
        initFontSizeButton: function() {
            var e = [["8", "sheet-font-size-8", "8", "8px"], ["9", "sheet-font-size-9", "9", "9px"], ["10", "sheet-font-size-10", "10", "10px"], ["11", "sheet-font-size-11", "11", "11px"], ["12", "sheet-font-size-12", "12", "12px"], ["14", "sheet-font-size-14", "14", "14px"], ["16", "sheet-font-size-16", "16", "16px"], ["18", "sheet-font-size-18", "18", "18px"], ["20", "sheet-font-size-20", "20", "20px"], ["22", "sheet-font-size-22", "22", "22px"], ["24", "sheet-font-size-24", "24", "24px"], ["26", "sheet-font-size-26", "26", "26px"], ["28", "sheet-font-size-28", "28", "28px"], ["36", "sheet-font-size-36", "36", "36px"], ["42", "sheet-font-size-42", "42", "42px"], ["48", "sheet-font-size-48", "48", "48px"], ["72", "sheet-font-size-72", "72", "72px"]].map(function(e) {
                return {
                    content: e[0],
                    identity: e[1],
                    iconContent: e[2],
                    iconClassName: "docx-icon-common toolbar-icon-sheet-middle-align",
                    contentClsName: "dropdown-c-content-fontsize",
                    clickcallback: function() {
                        padeditbar_utils.sheetButtonClick("fontsize:" + e[3])
                    }
                }
            });
            t.toolbarbuttons.sheetfontsize = new ToolBarContentMenuButton($("#sheet-font-size"), e, $("#toolbar-sheet-font-size-group"), function(e) {
                var t = e.split("-"),
                o = t[t.length - 1];
                return {
                    content: o,
                    iconClassName: "docx-icon-common toolbar-icon-sheet-middle-align",
                    iconContent: o,
                    identity: e
                }
            }, !0),
            t.toolbarbuttons.sheetfontsize.registerSelectionState({
                judge: function(e) {
                    var t, o, n = padeditbar_utils.getSelectedRange();
                    // return void 0;
                    
                    var a = n && (t = immTableSheet.sheetHot.getCellStyle()) && t.fontsize ? (o = parseFloat(t.fontsize), "sheet-font-size&sheet-font-size-" + (o = isNaN(o) ? 10 : (10 * o >> 0) / 10)) : $(e).css("font-size") ? (o = parseFloat($(e).css("font-size")) - 4, "sheet-font-size&sheet-font-size-" + (o = isNaN(o) ? 10 : (10 * o >> 0) / 10)) : void 0;
                    console.log(a)
                    return a;
                }
            });
            var e = function() {
                var u = immTableSheet.sheetHot.getCellStyle();
                // 字体大小
                $("#dropdown-wrapper-0 li .dropdown-c-content-fontsize").each(function(){
                    console.log(u.fontsize)
                    if(parseInt($(this).text()) >= parseInt(u.fontsize)){
                        $("#dropdown-wrapper-0 li").removeClass("li-dropdown-selected");
                        $(this).parents("li").addClass("li-dropdown-selected");
                        $("#toolbar-sheet-font-size-group .toolbar-menu-button-content-font-size").html(parseInt(u.fontsize));
                        return false;
                    }
                });
            }
            $(window.jExcelDiv).bind("mouseup", e),
            $(window).bind("keyup", e);
        },
        initFontFamilyButton: function() {
            var e = fontFamilyConfigData.map(function(e) {
                var t, o;
                return Array.isArray(e) ? (t = e[0], o = e[1]) : (t = e, o = e),
                {
                    content: o,
                    identity: "sheet-font-family-" + t,
                    iconClassName: "docx-icon-common toolbar-icon-sheet-top-align",
                    fontFamily: t,
                    contentClsName: "dropdown-c-content-fontfamily",
                    clickcallback: function() {
                        padeditbar_utils.sheetButtonClick("fontfamily:" + t)
                    }
                }
            });
            t.toolbarbuttons.sheetfontfamily = new ToolBarContentMenuButton($("#sheet-font-family"), e, $("#toolbar-sheet-font-family-group"),
            function(e) {
                var t = e.split("-"),
                o = t[t.length - 1];
                return {
                    content: o,
                    identity: e,
                    iconClassName: "docx-icon-common toolbar-icon-sheet-top-align",
                    fontFamily: o + " Microsoft YaHei",
                    contentClsName: "dropdown-c-content-normal"
                }
            },
            !0),
            t.toolbarbuttons.sheetfontfamily.registerSelectionState({
                judge: function(e) {
                    var t = $(e).css("font-family");
                    t = (t = t.split(",")[0]).replace(/\"/g, "");
                    for (var o = 0; o < fontFamilyConfigData.length; o++) {
                        var n = fontFamilyConfigData[o];
                        if (Array.isArray(n) && (n = n[0]), t === n) return "sheet-font-family&sheet-font-family-" + n
                    }
                    return "sheet-font-family&sheet-font-family-" + t
                }
            })
        },
        initMergeCellButton: function() {
            function e() {
                padeditbar_utils.isSingleCell() ? t.toolbarbuttons.sheetmergecell.disable() : t.toolbarbuttons.sheetmergecell.enable()
            }
            t.toolbarbuttons.sheetmergecell = new ToolBarButton($("#sheet-merge-cell")),
            $(window.jExcelDiv).bind("mouseup", e),
            $(window).bind("keyup", e);
            t.toolbarbuttons.sheetmergecell.registerSelectionState({
                judge: function(e) {
                    if (padeditbar_utils.isSingleMergeCell()) return "sheet-merge-cell"
                }
            }),
            t.toolbarbuttons.sheetmergecell.checkSheetMergeCellEnable = e
        },
        initCalculateButton: function() {
            var e = window._formulaConfig || {},
            o = function(t) {
                var o = e[t.toLowerCase()];
                return o && o.abbr || ""
            },
            n = [{
                content: "SUM",
                identify: "sheet-formula-sum",
                hover: o("SUM"),
                iconClassName: "docx-icon-common toolbar-icon-formula",
                clickcallback: function() {
                    padeditbar_utils.sheetButtonClick("sheet-formula-sum")
                }
            },
            {
                content: "AVERAGE",
                identify: "sheet-formula-average",
                hover: o("AVERAGE"),
                iconClassName: "docx-icon-common toolbar-icon-formula",
                clickcallback: function() {
                    padeditbar_utils.sheetButtonClick("sheet-formula-average")
                }
            },
            {
                content: "COUNT",
                identify: "sheet-formula-count",
                hover: o("COUNT"),
                iconClassName: "docx-icon-common toolbar-icon-formula",
                clickcallback: function() {
                    padeditbar_utils.sheetButtonClick("sheet-formula-count")
                }
            },
            {
                content: "MAX",
                identify: "sheet-formula-max",
                hover: o("MAX"),
                iconClassName: "docx-icon-common toolbar-icon-formula",
                clickcallback: function() {
                    padeditbar_utils.sheetButtonClick("sheet-formula-max")
                }
            },
            {
                content: "MIN",
                identify: "sheet-formula-min",
                hover: o("MIN"),
                iconClassName: "docx-icon-common toolbar-icon-formula",
                clickcallback: function() {
                    padeditbar_utils.sheetButtonClick("sheet-formula-min")
                }
            },
            {
                content: "STOCK",
                identify: "sheet-formula-stock",
                hover: o("STOCK"),
                iconClassName: "docx-icon-common toolbar-icon-formula",
                clickcallback: function() {
                    padeditbar_utils.sheetButtonClick("sheet-formula-stock")
                }
            },
            {
                content: "更多函数",
                identify: "sheet-formula-more formula-editor-drop-first",
                template: "template-dropdown-content-separator-row",
                hover: "插入更多函数",
                iconClassName: "docx-icon-common toolbar-icon-formula sheet-formula-more",
                clickcallback: function(e) {
                    $("#tooltip").remove(),
                    window.formulaEditor.show(),
                    window.ToolBarAdaption && ToolBarAdaption.hide()
                }
            }],
            i = new ToolBarImageMenuButton($("#sheet-calculate"), n, $("#toolbar-sheet-calculate-group"));
            t.toolbarbuttons.sheetcalculate = i;
            var r = t.toolbarbuttons.sheetcalculate.handle ||
            function() {};
            t.toolbarbuttons.sheetcalculate.handle = function() {
                r.apply(t.toolbarbuttons.sheetcalculate, arguments)
            }
        },
        initFormatButton: function() {
            t.toolbarbuttons.sheetformat = new ToolBarContentMenuButton($("#sheet-format"), [{
                title: "常规",
                iconContent: "常规",
                template: "template-dropdown-celltype-row",
                identity: "sheet-format-default",
                contentClsName: "dropdown-c-content-normal",
                clickcallback: function() {
                    padeditbar_utils.sheetButtonClick("sheet-format-default")
                }
            },
            {
                title: "文本",
                iconContent: "文本",
                template: "template-dropdown-celltype-row",
                identity: "sheet-format-text",
                contentClsName: "dropdown-c-content-normal",
                clickcallback: function() {
                    padeditbar_utils.sheetButtonClick("sheet-format-text")
                }
            },
            {
                template: "template-dropdown-separator-row"
            },
            {
                title: "数值",
                iconContent: "数值",
                detail: "0.95",
                template: "template-dropdown-celltype-row",
                identity: "sheet-format-numeral",
                contentClsName: "dropdown-c-content-normal",
                clickcallback: function() {
                    padeditbar_utils.sheetButtonClick("sheet-format-numeral")
                }
            },
            {
                title: "百分比",
                iconContent: "百分比",
                detail: "90.00%",
                template: "template-dropdown-celltype-row",
                identity: "sheet-format-percentage",
                contentClsName: "dropdown-c-content-normal",
                clickcallback: function() {
                    padeditbar_utils.sheetButtonClick("sheet-format-percentage")
                }
            },
           /* {
                title: "分数",
                iconContent: "分数",
                detail: "1/2",
                template: "template-dropdown-celltype-row",
                identity: "sheet-format-fraction",
                contentClsName: "dropdown-c-content-normal",
                clickcallback: function() {
                    padeditbar_utils.sheetButtonClick("sheet-format-fraction")
                }
            },
            {
                title: "科学记数",
                iconContent: "科学记数",
                detail: "9.50E-01",
                template: "template-dropdown-celltype-row",
                identity: "sheet-format-exponential",
                contentClsName: "dropdown-c-content-normal",
                clickcallback: function() {
                    padeditbar_utils.sheetButtonClick("sheet-format-exponential")
                }
            },*/
            {
                template: "template-dropdown-separator-row"
            },
            {
                title: "日期",
                iconContent: "日期",
                detail: "2018/4/18",
                template: "template-dropdown-celltype-row",
                identity: "sheet-format-date",
                contentClsName: "dropdown-c-content-normal",
                clickcallback: function() {
                    padeditbar_utils.sheetButtonClick("sheet-format-date")
                }
            },
            {
                title: "日期",
                iconContent: "日期",
                detail: "2018/4/18 14:30:30",
                template: "template-dropdown-celltype-row",
                identity: "sheet-format-date-time",
                contentClsName: "dropdown-c-content-normal",
                clickcallback: function() {
                    padeditbar_utils.sheetButtonClick("sheet-format-date-time")
                }
            },
            {
                title: "时间",
                iconContent: "时间",
                detail: "14:30:30",
                template: "template-dropdown-celltype-row",
                identity: "sheet-format-time",
                contentClsName: "dropdown-c-content-normal",
                clickcallback: function() {
                    padeditbar_utils.sheetButtonClick("sheet-format-time")
                }
            }/*,
            {
                title: "其他",
                iconContent: "自定义",
                detail: "自定义格式",
                template: "template-dropdown-celltype-row",
                identity: "sheet-format-custom",
                contentClsName: "dropdown-c-content-normal",
                clickcallback: function() {}
            },
            {
                title: "特殊",
                iconContent: "特殊",
                detail: "特殊",
                template: "template-dropdown-celltype-row",
                identity: "sheet-format-special",
                contentClsName: "dropdown-c-content-normal",
                clickcallback: function() {}
            },
            {
                template: "template-dropdown-separator-row"
            },
            {
                title: "更多格式",
                iconContent: "更多格式",
                template: "template-dropdown-celltype-row",
                contentClsName: "dropdown-c-content-normal",
                clickcallback: function() {
                    immTableSheet.popupDataformatDialogFrom && immTableSheet.popupDataformatDialogFrom("toolbar")
                }
            }*/], $("#toolbar-sheet-format-group")),
            t.toolbarbuttons.sheetformat.registerSelectionState({
                judge: function(e) {
                    var t, o = padeditbar_utils.getSelectedRange();
                    if (o) {
                        var n = (t = immTableSheet.sheetHot.getCellStyle(o.col, o.row)) && t.format,
                        i = immTableSheet.numericMap && immTableSheet.numericMap.getNumericResult(o.highlight.row, o.highlight.col),
                        r = i && i.autoType,
                        a = n || r;
                        if (a) return "thousand-percentage" === a ? "sheet-format&sheet-format-default": "sheet-format&sheet-format-" + a
                    }
                    return "sheet-format&sheet-format-default"
                }
            })
        },
        initTextwrapButton: function() {
            var e = function() {
                "nowrap" === immTableSheet.sheetHot.getCellStyle().textwrap ? t.toolbarbuttons.sheettextwrapbutton.check() : t.toolbarbuttons.sheettextwrapbutton.uncheck()
            }
            t.toolbarbuttons.sheettextwrapbutton = new ToolBarButton($("#sheet-textwrapbutton")),
            t.toolbarbuttons.sheettextwrapbutton.registerSelectionState({
                judge: function(e) {
                    var  o = (e = immTableSheet.sheetHot.getCellStyle(), immTableSheet.sheetHot.getCellStyle(e[0], e[1]));
                    if (o && "normal" === o.textwrap) return "sheet-textwrapbutton"
                }
            })
            $(window.jExcelDiv).bind("mouseup", e),
            $(window).bind("keyup", e);
        },
        initSearchButton: function() {
            t.toolbarbuttons.sheetsearch = new ToolBarButton($("#sheet-search"))
        },
        isToolBarButtonEnable: function(o) {
            return ! e[o] || t.toolbarbuttons[e[o]].isEnable()
        },
        bindHotEvents: function(e) {
            window.toolbarController && toolbarController.enable();
            t.toolbarbuttons.sheetundobutton.disable();
            t.toolbarbuttons.sheetredobutton.disable();
            t.toolbarbuttons.sheetsavebutton.disable();
            t.toolbarbuttons.sheetinsertlink.disable();
            window.updateUndoRedoButtonState = function() {
                // 撤销，重做
                immTableSheet.jExcel.historyIndex >= 0 ? t.toolbarbuttons.sheetundobutton.enable() : t.toolbarbuttons.sheetundobutton.disable(),
                immTableSheet.jExcel.historyIndex < immTableSheet.jExcel.history.length-1 ? t.toolbarbuttons.sheetredobutton.enable() : t.toolbarbuttons.sheetredobutton.disable();
                // 保存按钮
                if(immTableSheet.jExcel.saveIndex != immTableSheet.jExcel.historyIndex){
                    t.toolbarbuttons.sheetsavebutton.enable();
                    window.onbeforeunload = function () {
                        return "请确认信息是否已保存！";
                    };
                }else{
                    window.onbeforeunload = null;
                    t.toolbarbuttons.sheetsavebutton.disable();
                }
            };
        }
    };
    return t.init(),
    t
},
toolbarController = null;

(function(e) {
    var t, o, n, i, r = 0,
    a = {
        control: e('<div id="toolbar-colorPicker-picker-wrapper" data-tooltip="字体颜色" class="hp-ui-button toolbar-menu-button-wrapper toolbar-colorPicker-menu-button-wrapper"><div id="toolbar-colorPicker-picker-outer-container" class="toolbar-menu-button-outer-container toolbar-menu-button-colorPicker-outer-container toolbar-inline-block" style="user-select: none;"><div id="toolbar-colorPicker-picker-inner-container" class="toolbar-button-inner-container toolbar-inline-block" style="user-select;none;"><div id="toolbar-colorPicker-picker-caption" class="toolbar-colorPicker-picker-caption toolbar-inline-block" style="user-select: none;"> <div id="toolbar-colorPicker-picker-text-outer" class="toolbar-colorPicker-picker-text-outer" style="user-select: none;"> <div id="toolbar-colorPicker-picker-text-bg" class="toolbar-colorPicker-picker-text-background" style="user-select: none;"> <div id="toolbar-colorPicker-picker-text-char" class="toolbar-colorPicker-picker-text-char toolbar-inline-block" style="user-select: none;">A</div></div></div></div><div id="toolbar-colorPicker-picker-icon-outer" class="toolbar-colorPicker-picker-icon-outer" style="user-select: none;"><div id="toolbar-colorPicker-picker-icon" class="toolbar-colorPicker-picker-icon toolbar-inline-block" style="user-select: none;"></div> </div></div></div></div>'),
        docControl: e('<div id="toolbar-colorPicker-picker-wrapper" data-tooltip="字体颜色" class="hp-ui-button toolbar-menu-button-wrapper toolbar-colorPicker-menu-button-wrapper"><div id="toolbar-colorPicker-picker-outer-container" class="toolbar-menu-button-outer-container toolbar-menu-button-colorPicker-outer-container toolbar-inline-block" style="user-select: none;"><div id="toolbar-colorPicker-picker-inner-container" class="toolbar-button-inner-container toolbar-inline-block" style="user-select;none;"><div id="toolbar-colorPicker-picker-caption" class="toolbar-colorPicker-picker-caption toolbar-inline-block" style="user-select: none;"> <div id="toolbar-colorPicker-picker-docx-text-outer" class="toolbar-colorPicker-picker-text-outer" style="user-select: none;"> <div id="toolbar-colorPicker-picker-text-bg" class="toolbar-colorPicker-picker-text-background" style="user-select: none;"> <div id="toolbar-colorPicker-picker-text-char" class="toolbar-colorPicker-picker-text-char toolbar-inline-block" style="user-select: none;">A</div></div></div></div><div id="toolbar-colorPicker-picker-icon-outer" class="toolbar-colorPicker-picker-icon-outer" style="user-select: none;"><div id="toolbar-colorPicker-picker-icon" class="toolbar-colorPicker-picker-icon toolbar-inline-block" style="user-select: none;"></div> </div></div></div></div>'),
        paintBrushControl: e('<div id="toolbar-colorPicker-picker-paint-wrapper" data-tooltip="填充颜色" class="hp-ui-button toolbar-menu-button-wrapper toolbar-colorPicker-menu-button-wrapper"><div id="toolbar-colorPicker-picker-paint-outer-container" class="toolbar-menu-button-outer-container toolbar-menu-button-colorPicker-outer-container toolbar-inline-block" style="user-select: none;"><div id="toolbar-colorPicker-picker-paint-inner-container" class="toolbar-button-inner-container toolbar-inline-block" style="user-select;none;"><div id="toolbar-colorPicker-picker-paint-caption" class="toolbar-colorPicker-picker-caption toolbar-inline-block" style="user-select: none;"> <div id="toolbar-colorPicker-picker-paint-text-outer" class="toolbar-colorPicker-picker-text-outer" style="user-select: none;"> <div id="toolbar-colorPicker-picker-paint-text-bg" class="toolbar-colorPicker-picker-text-background" style="user-select: none;"> <div id="toolbar-colorPicker-picker-paint-text-char" class="toolbar-colorPicker-picker-text-char docx-icon-common toolbar-colorPicker-picker-paint-brush toolbar-inline-block" style="user-select: none;"></div></div></div></div><div id="toolbar-colorPicker-picker-paint-icon-outer" class="toolbar-colorPicker-picker-icon-outer" style="user-select: none;"><div id="toolbar-colorPicker-picker-paint-icon" class="toolbar-colorPicker-picker-icon toolbar-inline-block" style="user-select: none;"></div> </div></div></div></div>'),
        paintDocBrushControl: e('<div id="toolbar-colorPicker-picker-paint-wrapper" data-tooltip="突出显示" class="hp-ui-button toolbar-menu-button-wrapper toolbar-colorPicker-menu-button-wrapper"><div id="toolbar-colorPicker-picker-paint-outer-container" class="toolbar-menu-button-outer-container toolbar-menu-button-colorPicker-outer-container toolbar-inline-block" style="user-select: none;"><div id="toolbar-colorPicker-picker-paint-inner-container" class="toolbar-button-inner-container toolbar-inline-block" style="user-select;none;"><div id="toolbar-colorPicker-picker-paint-caption" class="toolbar-colorPicker-picker-caption toolbar-inline-block" style="user-select: none;"> <div id="toolbar-colorPicker-picker-paint-docx-text-outer" class="toolbar-colorPicker-picker-text-outer" style="user-select: none;"> <div id="toolbar-colorPicker-picker-paint-text-bg" class="toolbar-colorPicker-picker-text-background" style="user-select: none;"> <div id="toolbar-colorPicker-picker-paint-text-char" class="toolbar-colorPicker-picker-text-char docx-icon-common toolbar-colorPicker-picker-doc-paint-brush toolbar-inline-block" style="user-select: none;"></div></div></div></div><div id="toolbar-colorPicker-picker-paint-icon-outer" class="toolbar-colorPicker-picker-icon-outer" style="user-select: none;"><div id="toolbar-colorPicker-picker-paint-icon" class="toolbar-colorPicker-picker-icon toolbar-inline-block" style="user-select: none;"></div> </div></div></div></div>'),
        palette: e('<div id="colorPicker_palette"  class="colorPicker-palette common-web-popup-boxshadow" />'),
        swatch: e('<div class="colorPicker-swatch" data-tooltip="">&nbsp;</div>'),
        tableRow: e('<tr class="colorPicker-palette-table-row" style="user-select: none;"></tr>'),
        paletteTable: e('<div style="user-select;none;" > <div class="colorPiker-Palette-noColor" style="display: none;">无颜色</div><table  cellspacing="0" cellpadding="0" style="user-select: none;" ><tbody class="colorPicker-palette-table-body" id="colorPicker_tableBody" style="user-select: none;"></tbody> </table></div>')
    },
    s = {},
    l = "#000000",
    c = "#00b0f0",
    u = "#000000",
    d = "#00b0f0",
    f = {},
    h = {},
    g = {};
    e.fn.colorPicker = function(t) {
        return e.fn.colorPicker.initColorPicker(t),
        this.each(function() {
            var o, p, m, v, C, b = e(this),
            A = e.extend({},
            e.fn.colorPicker.defaults, t),
            I = e.fn.colorPicker.toHex(A.pickerDefault),
            y = e.fn.colorPicker.toHex(A.brushPickerDefault),
            w = a.palette.clone().attr("id", "colorPicker_palette-" + r),
            x = w[0].id,
            k = a.paletteTable.clone(),
            _ = k.find("#colorPicker_tableBody");
            f[x] = A.pickerType,
            s[x] = A,
            t.colorButton && (1 == A.pickerType ? n = t.colorButton: 2 == A.pickerType && (i = t.colorButton)),
            1 == A.pickerType && A.isDoc ? o = a.docControl.clone() : 1 != A.pickerType || A.isDoc ? 2 == A.pickerType && A.isDoc ? o = a.paintDocBrushControl.clone() : 2 != A.pickerType || A.isDoc || (o = a.paintBrushControl.clone()) : o = a.control.clone(),
            e.each(A.colors,
            function(t) {
                t % 10 == 0 && (m && m.appendTo(_), m = a.tableRow.clone()),
                p = a.swatch.clone(),
                t < 10 && p.css("margin-bottom", "7px");
                var o = "#" + this;
                1 == A.pickerType ? (p.attr("id", "colorPicker-swatch-" + t), h[o] = "#" + p.attr("id")) : 2 == A.pickerType && (p.attr("id", "colorPicker-swatch-paint-" + t), g[o] = "#" + p.attr("id")),
                "transparent" === A.colors[t] ? (p.addClass("transparent").text("X"), e.fn.colorPicker.bindPalette(p, "transparent")) : (p.css("background-color", o), e.fn.colorPicker.bindPalette(p)),
                p.appendTo(m)
            }),
            m.appendTo(_),
            k.appendTo(w),
            e("body").append(w),
            w.hide(),
            b.after(o),
            1 == A.pickerType ? (l = I, u = I, A.isDoc ? e("#toolbar-colorPicker-picker-docx-text-outer").css("border-bottom-color", I) : e("#toolbar-colorPicker-picker-text-outer").css("border-bottom-color", I)) : 2 == A.pickerType && (c = y, d = y, A.isDoc ? e("#toolbar-colorPicker-picker-paint-docx-text-outer").css("border-bottom-color", y) : e("#toolbar-colorPicker-picker-paint-text-outer").css("border-bottom-color", y)),
            v = o.find(".toolbar-colorPicker-picker-caption"),
            C = o.find(".toolbar-colorPicker-picker-icon-outer"),
            v && v.bind("click",
            function() {
                b.is(":not(:disabled)") && e.fn.colorPicker.applyColor(e("#" + x), o)
            }),
            C && C.bind("click",
            function() {
                b.is(":not(:disabled)") && e.fn.colorPicker.togglePalette(e("#" + x), o)
            }),
            t && t.onColorChange ? o.data("onColorChange", t.onColorChange) : o.data("onColorChange",
            function() {}),
            r++
        }),
        padutils.tooltip(".colorPicker-palette [data-tooltip]"),
        this
    },
    e.extend(!0, e.fn.colorPicker, {
        initColorPicker: function(t) {
            t.colorPickerClass && a.control.addClass(t.colorPickerClass),
            window.addEventListener("SheetMouseDown",
            function(t) {
                e.fn.colorPicker.hidePalette()
            }),
            e(window).bind("SheetScroll",
            function(t) {
                e.fn.colorPicker.hidePalette()
            }),
            window.addEventListener("closedropdown",
            function(t) {
                e.fn.colorPicker.hidePalette()
            })
        },
        toHex: function(e) {
            if (e.match(/[0-9A-F]{6}|[0-9A-F]{3}$/i)) return "#" === (e = e.toLowerCase()).charAt(0) ? e: "#" + e;
            if (!e.match(/^rgb\(\s*(\d{1,3})\s*,\s*(\d{1,3})\s*,\s*(\d{1,3})\s*\)$/)) return ! 1;
            var t = [parseInt(RegExp.$1, 10), parseInt(RegExp.$2, 10), parseInt(RegExp.$3, 10)],
            o = function(e) {
                if (e.length < 2) for (var t = 0,
                o = 2 - e.length; t < o; t++) e = "0" + e;
                return e
            };
            return 3 === t.length ? "#" + o(t[0].toString(16)) + o(t[1].toString(16)) + o(t[2].toString(16)) : void 0
        },
        checkMouse: function(n, i) {
            n.preventDefault();
            var r = o,
            a = e(n.target).parents("#" + r.attr("id")).length;
            if (! (n.target === t[0] || a > 0)) {
                var s = !1,
                l = n.target.id;
                l.length > 0 && l.indexOf("toolbar-colorPicker-picker") >= 0 && (s = !0),
                s || e.fn.colorPicker.hidePalette()
            }
        },
        hidePalette: function() {
            o && (e(".colorPiker-Palette-noColor").unbind(), o.unbind("mousedown", e.fn.colorPicker.checkMouse), e(document).unbind("mousedown", e.fn.colorPicker.checkMouse), e(window).unbind("resize", e.fn.colorPicker.hidePalette), o.removeClass("open"), o.hide())
        },
        showPalette: function(o) {
            var r = e.fn.colorPicker.getCurrentPickerType(),
            a = e.fn.colorPicker.getCurrentOptsType();
            if (1 == r) {
                if (!n.isEnable()) return;
                a.isDoc ? e.fn.colorPicker.addSwatchDownClass(1, l) : e.fn.colorPicker.addSwatchDownClass(1, u),
                e(".colorPiker-Palette-noColor").hide()
            } else if (2 == r) {
                if (!i.isEnable()) return;
                a.isDoc ? (e.fn.colorPicker.addSwatchDownClass(2, c), e.fn.colorPicker.bindPaletteForNoColorSwatch(), e(".colorPiker-Palette-noColor").show()) : e.fn.colorPicker.addSwatchDownClass(2, d)
            }
            e("#tooltip").remove();
            var s = t.offset().left + o.outerWidth() < e(document.body).width() ? t.offset().left: t.offset().left + t.outerWidth() - o.outerWidth();
            o.css({
                top: t.offset().top + t.outerHeight() + 4,
                left: s
            }),
            o && (o.addClass("open"), o.show(), o.bind("mousedown", e.fn.colorPicker.checkMouse), e(document).bind("mousedown", e.fn.colorPicker.checkMouse), e(window).bind("resize", e.fn.colorPicker.hidePalette))
        },
        togglePalette: function(n, i) {
            i && (t = i);
            var r = (o = n).attr("id");
            for (var a in f) r !== a && (e("#" + a).unbind("mousedown", e.fn.colorPicker.checkMouse), e("#" + a).removeClass("open"), e("#" + a).hide());
            o.hasClass("open") ? e.fn.colorPicker.hidePalette() : e.fn.colorPicker.showPalette(n)
        },
        applyColor: function(r, a) {
            o = r;
            var s, f = e.fn.colorPicker.getCurrentOptsType(),
            h = e.fn.colorPicker.getCurrentPickerType();
            if (1 == h) {
                if (!n.isEnable()) return;
                f.isDoc ? e.fn.colorPicker.addSwatchDownClass(1, l) : e.fn.colorPicker.addSwatchDownClass(1, u)
            } else if (2 == h) {
                if (!i.isEnable()) return;
                f.isDoc ? e.fn.colorPicker.addSwatchDownClass(2, c) : e.fn.colorPicker.addSwatchDownClass(2, d)
            }
            e.fn.colorPicker.hidePalette(),
            a && (t = a),
            1 == (h = e.fn.colorPicker.getCurrentPickerType()) ? s = f.isDoc ? l: u: 2 == h && (s = f.isDoc ? c: d),
            e.fn.colorPicker.hidePalette(),
            t && t.data("onColorChange").call(t, s)
        },
        changeColor: function(o, n) {
            var i = e.fn.colorPicker.getCurrentPickerType(),
            r = e.fn.colorPicker.getCurrentOptsType();
            1 == i ? r.isDoc ? e("#toolbar-colorPicker-picker-docx-text-outer").css("border-bottom-color", o) : e("#toolbar-colorPicker-picker-text-outer").css("border-bottom-color", o) : 2 == i && (r.isDoc ? e("#toolbar-colorPicker-picker-paint-docx-text-outer").css("border-bottom-color", o) : e("#toolbar-colorPicker-picker-paint-text-outer").css("border-bottom-color", o)),
            e.fn.colorPicker.hidePalette(),
            t.data("onColorChange").call(t, o, n)
        },
        getCurrentPickerType: function() {
            if (o && f) {
                var e = o.attr("id");
                return f[e]
            }
            return 0
        },
        getCurrentOptsType: function() {
            if (o && s) {
                var e = o.attr("id");
                return s[e]
            }
            return 0
        },
        removeSwatchDownClass: function(e, t) {
            if (t) if (1 == e) {
                var n = o.find(h[t]);
                h[t] && n.hasClass("colorPicker-swatch-down") && n.removeClass("colorPicker-swatch-down")
            } else {
                var i = o.find(g[t]);
                g[t] && i.hasClass("colorPicker-swatch-down") && i.removeClass("colorPicker-swatch-down")
            }
        },
        addSwatchDownClass: function(t, n) {
            if (n) if (n = e.fn.colorPicker.toHex(n), 1 == t) {
                var i = o.find(h[n]);
                h[n] && !i.hasClass("colorPicker-swatch-down") && i.addClass("colorPicker-swatch-down")
            } else if (2 == t) {
                var r = o.find(g[n]);
                g[n] && !r.hasClass("colorPicker-swatch-down") && r.addClass("colorPicker-swatch-down")
            }
        },
        bindPalette: function(t, o) {
            o = o || e.fn.colorPicker.toHex(t.css("background-color")),
            t.bind({
                click: function(t) {
                    var n = e.fn.colorPicker.getCurrentPickerType(),
                    i = e.fn.colorPicker.getCurrentOptsType();
                    1 == n ? i.isDoc ? (e.fn.colorPicker.removeSwatchDownClass(1, l), l = o) : (e.fn.colorPicker.removeSwatchDownClass(1, u), u = o) : 2 == n && (i.isDoc ? (e.fn.colorPicker.removeSwatchDownClass(2, c), c = o) : (e.fn.colorPicker.removeSwatchDownClass(2, d), d = o)),
                    e.fn.colorPicker.changeColor(o, t)
                },
                mouseover: function(t) {
                    e(this).hasClass("colorPicker-swatch-down") || e(this).addClass("colorPicker-swatch-hover")
                },
                mouseout: function(t) {
                    e(this).hasClass("colorPicker-swatch-hover") && e(this).removeClass("colorPicker-swatch-hover")
                }
            })
        },
        bindPaletteForNoColorSwatch: function() {
            e(".colorPiker-Palette-noColor").bind({
                click: function(t) {
                    var o = e.fn.colorPicker.getCurrentPickerType(),
                    n = e.fn.colorPicker.getCurrentOptsType();
                    1 != o && (2 == o && (n.isDoc ? (e.fn.colorPicker.removeSwatchDownClass(2, c), c = "") : (e.fn.colorPicker.removeSwatchDownClass(2, d), d = "")), e.fn.colorPicker.changeColor("", t))
                },
                mouseover: function(t) {
                    e(this).hasClass("colorPiker-Palette-noColor-down") || e(this).addClass("colorPiker-Palette-noColor-hover")
                },
                mouseout: function(t) {
                    e(this).hasClass("colorPiker-Palette-noColor-hover") && e(this).removeClass("colorPiker-Palette-noColor-hover")
                }
            })
        }
    }),
    e.fn.colorPicker.changePickerColor = function(t, o, n) {
        if (t) {
            var i = e.fn.colorPicker.toHex(t);
            if (o) {
                var r = e.fn.colorPicker.toHex(e.fn.colorPicker.defaults.pickerDefault);
                e.fn.colorPicker.removeSwatchDownClass(1, l),
                l = r,
                e.fn.colorPicker.addSwatchDownClass(1, l),
                e("#toolbar-colorPicker-picker-text-outer").css("border-bottom-width", "0")
            } else if (i) {
                e.fn.colorPicker.removeSwatchDownClass(1, l),
                l = i,
                e.fn.colorPicker.addSwatchDownClass(1, l),
                e("#toolbar-colorPicker-picker-text-outer").css("border-bottom-color", i),
                "0px" === e("#toolbar-colorPicker-picker-text-outer").css("border-bottom-width") && e("#toolbar-colorPicker-picker-text-outer").css("border-bottom-width", "4px")
            }
        }
        if (n) {
            var a = e.fn.colorPicker.toHex(n);
            a && (e.fn.colorPicker.removeSwatchDownClass(2, c), c = a, e.fn.colorPicker.addSwatchDownClass(2, c), e("#toolbar-colorPicker-picker-paint-text-outer").css("border-bottom-color", a))
        }
    },
    e.fn.colorPicker.isColorPaletteShow = function(t) {
        var n = !1;
        return t == e.fn.colorPicker.getCurrentPickerType() && o && o.hasClass("open") && (n = !0),
        n
    },
    e.fn.colorPicker.defaults = {
        pickerDefault: "#000000",
        brushPickerDefault: "#00b0f0",
        colors: ["c00000", "ff0000", "ffc003", "ffff00", "91d051", "00af50", "00b0f0", "0070c0", "002060", "70309f", "ffffff", "000000", "e7e6e6", "44546a", "4472c4", "ed7d31", "a5a5a5", "ffc003", "5b9bd5", "70ad47", "f4f5f8", "848484", "d0cece", "d6dce4", "d9e2f2", "fae5d5", "ededed", "fff2cc", "deebf6", "e2efd9", "d8d8d8", "595959", "afabab", "adb9ca", "b4c6e7", "f7cbac", "dbdbdb", "fee598", "bdd7ee", "c5e0b3", "bfbfbf", "3f3f3f", "757070", "8496b0", "8eaad8", "f4b183", "c9c9c9", "ffd964", "9dc2e5", "a8d08d", "a5a5a5", "262626", "3a3838", "333f4f", "2f5496", "c55b11", "7b7b7b", "bf9001", "2e75b5", "538135", "7e7e7e", "0c0c0c", "171616", "232a35", "1e3864", "833d0b", "525252", "7e6000", "1f4e79", "375623"],
        colorsTips: ["白", "黑", "红", "橙", "黄", "葱绿", "湖蓝", "天色", "紫", "白练", "鼠", "虹", "薄卵", "蒸栗", "白绿", "蓝白", "天空", "紫水晶", "白鼠", "墨", "甚三红", "雄黄", "金子", "薄青", "白群", "薄花", "紫苑", "灰青", "石墨", "红绯", "红金", "枯茶", "绿青", "浅葱", "薄缥", "紫霞", "薄纯", "漆黑", "朱绯", "褐", "黑茶", "深绿", "苍蓝", "琉璃", "葡萄"],
        colorPickerClass: "",
        pickerType: 1
    }
})(jQuery);
var BorderMenu = function(e, t) {
    // var o = ["toolbar-icon-sheet-border-all", "toolbar-icon-sheet-border-inner", "toolbar-icon-sheet-border-horizontal", "toolbar-icon-sheet-border-vertical", "toolbar-icon-sheet-border-outer", "toolbar-icon-sheet-border-left", "toolbar-icon-sheet-border-top", "toolbar-icon-sheet-border-right", "toolbar-icon-sheet-border-bottom", "toolbar-icon-sheet-border-clear"],
    // n = ["所有边框", "内部边框", "水平边框", "垂直边框", "外部边框", "左侧边框", "顶部边框", "右侧边框", "底部边框", "无边框"],
    // i = ["border-all", "border-inner", "border-horizontal", "border-vertical", "border-outer", "border-left", "border-top", "border-right", "border-bottom", "border-clear"],
    var o = ["toolbar-icon-sheet-border-all",  "toolbar-icon-sheet-border-outer", "toolbar-icon-sheet-border-clear"],
    n = ["所有边框", "外部边框", "无边框"],
    i = ["border-all", "border-inner", "border-clear"],
    r = null,
    a = {
        palette: $('<div id="toolbar_border_palette"  class="toolbar-border-palette" />'),
        tableCell: $('<td class="toolbar-border-palette-table-td toolbar-inline-block" data-tooltip="" style="user-select: none;"> <div class="toolbar-border-palette-icon-outer toolbar-inline-block"  style="user-select: none;"> <div class="toolbar-border-palette-icon-container" style="user-select: none;">&nbsp;</div> </div></td>'),
        tableRow: $('<tr class="toolbar-border-palette-table-row" style="user-select: none;"></tr>'),
        paletteTable: $('<div style="user-select;none;" > <table class="toolbar-border-palette-table" cellspacing="0" cellpadding="0" style="user-select: none;" ><tbody class="toolbar-border-palette-table-body" id="tableBody" style="user-select: none;"></tbody> </table></div>'),
        borderColorPicker: $('<div id = "toolbar-border-palette-color-picker" class="hide toolbar-border-palette-color-picker-outer" style="user-select:none"><div id="toolbar-border-palette-color-picker-caption" class="toolbar-border-palette-colorPicker-picker-caption toolbar-inline-block" style="user-select: none;"> <div id="toolbar-border-palette-color-picker-text-outer" class="toolbar-border-palette-colorPicker-picker-text-outer" style="user-select: none;"> <div id="toolbar-border-palette-color-picker-text-bg" class="toolbar-border-palette-colorPicker-picker-text-background" style="user-select: none;"> <div id="toolbar-border-palette-color-picker-text-char" class="docx-icon-common toolbar-border-palette-colorPicker-picker-border-color toolbar-inline-block" style="user-select: none;"></div></div></div></div><div class="toolbar-border-palette-color-picker-text toolbar-inline-block">边框颜色</div><div class="toolbar-border-palette-colorPicker-icon toolbar-inline-block"></div></div>')
    },
    s = {
        isSetColorCallback: !1,
        borderColorPickerPalette: null,
        lastSelectedColor: "#000000",
        togglePalette: function(e) {
            $("#toolbar_border_palette").hasClass("open") ? s.hidePalette(e) : s.showPalette(e)
        },
        checkMouse: function(t) {
            if (t) {
                if (t.preventDefault(), t.currentTarget && t.currentTarget.id) {
                    var o = t.currentTarget.id;
                    if (o.length > 0 && "toolbar_border_palette" == o) return
                }
                var n = $(t.target).parents(e.selector);
                if (n && n.attr("id") == e[0].id) return;
                var i = $(t.target).parents("#common-color-picker-palette");
                if (i && "common-color-picker-palette" == i.attr("id") || "common-color-picker-palette" == $(t.target).attr("id")) return
            }
            s.hidePalette(t)
        },
        hidePalette: function(e) {
            $(document).unbind("mousedown", s.checkMouse),
            $(window).unbind("resize", s.hidePalette),
            $("#toolbar_border_palette").removeClass("open"),
            $("#toolbar_border_palette").hide()
        },
        showPalette: function(t) {
            var o = $("#toolbar_border_palette");
            e && o.css({
                left: e.offset().left + o.outerWidth() < $(document.body).width() ? 0 : e.outerWidth() - o.outerWidth(),
                top: e.outerHeight() + 4
            }),
            null != r && $("#border-palette-swatch-" + r).removeClass("toolbar-border-palette-icon-selected"),
            s.lastSelectedColor && $("#toolbar-border-palette-color-picker-text-outer").css("border-bottom-color", s.lastSelectedColor),
            r = null,
            $("#toolbar_border_palette").addClass("open"),
            $("#toolbar_border_palette").show(),
            $(document).bind("mousedown", s.checkMouse),
            $(window).bind("resize", s.hidePalette)
        },
        bindColorPicker: function(e) {
            e && e.bind("click",
            function(e) {
                padutils.showToolToast("暂不支持修改边框颜色!", {zIndex: 99999}, {toolToastCloseTime: 3e3});
                return;
                s.isSetColorCallback || (s.isSetColorCallback = !0, CommonColorPicker.getInstance().setColorSelectedCallback("toolbar-border-menu",
                function(e) {
                    e && (s.lastSelectedColor = e, $("#toolbar-border-palette-color-picker-text-outer").css("border-bottom-color", e)),
                    null != t && t(null != r ? i[r] : "border-change", s.lastSelectedColor)
                }));
                var o = $("#toolbar_border_palette").offset(),
                n = o.top + $("#toolbar_border_palette").outerHeight(),
                a = o.left + 198 < $(document.body).width() ? o.left: o.left + $("#toolbar_border_palette").outerWidth() - 198;
                CommonColorPicker.getInstance().toggleColorPalette(s.lastSelectedColor, n, a, "toolbar-border-menu", e, !0)
            })
        },
        bindCell: function(e, o) {
            e.bind({
                click: function(e) {
                    null != r && $("#border-palette-swatch-" + r).removeClass("toolbar-border-palette-icon-selected"),
                    $(this).addClass("toolbar-border-palette-icon-selected"),
                    r = o,
                    t(i[o], s.lastSelectedColor)
                },
                mouseover: function(e) {
                    $(this).hasClass("toolbar-border-palette-icon-selected") || $(this).addClass("toolbar-border-palette-icon-hover")
                },
                mouseout: function(e) {
                    $(this).hasClass("toolbar-border-palette-icon-hover") && $(this).removeClass("toolbar-border-palette-icon-hover")
                }
            })
        },
        init: function(e) {
            for (var t, i, r, l = a.palette.clone(), c = a.paletteTable.clone(), u = a.borderColorPicker.clone(), d = 0; d < o.length; d++) {
                /*d % 5 == 0 &&*/ (t && (r = c.find("#tableBody"), t.appendTo(r)), t = a.tableRow.clone()),
                (i = a.tableCell.clone()).find(".toolbar-border-palette-icon-container").addClass("docx-icon-common " + o[d]);
                var f = n[d];
                i.attr("data-tooltip", f),
                i.children().first().attr("id", "border-palette-swatch-" + d),
                s.bindCell(i.children().first(), d),
                i.appendTo(t)
            }
            t.appendTo(r),
            c.appendTo(l),
            s.bindColorPicker(u),
            u.appendTo(l),
            e && (e.append(l), window.addEventListener("SheetMouseDown",
            function(e) {
                s.hidePalette(e)
            },
            !1), $(window).bind("SheetScroll",
            function(e) {
                s.hidePalette(e)
            })),
            padutils.tooltip("#toolbar_border_palette [data-tooltip]")
        }
    };
    return s.init(e),
    s
}; 

var ToolBarAdaption = {
    showMore: !1,
    toggleFlag: !1,
    collapseCurIndex: 0,
    $moreToolsView: null,
    init: function() {
        return this.$toobarMoreButton = $("#toobarMoreButton"),
        this.$moreBtnWrap = this.$toobarMoreButton.find(".toolbar-menu-button-wrapper"),
        this.$toobarMoreButton.data("tooltip", "更多功能"),
        padutils.tooltip(this.$toobarMoreButton),
        this.$toolbar = $("#toolbar"),
        this.$toolList = this.$toolbar.children(".hp-ui-button-group"),
        this.$cloneToolList = this.$toolList.clone(),
        this.len = this.$toolList.length,
        this.collapseCurIndex = this.len - 2,
        this.initMoreToolsComponent(),
        this.toolsTotalWidth = this.countToolTotalWidth(),
        this.aboveTools = [],
        this.addEvent(),
        this
    },
    hide: function() {
        this.toggleFlag = !1,
        this.$moreBtnWrap.removeClass("toolbar-button-wrapper-checked"),
        this.$moreToolsView.hide()
    },
    addEvent: function() {
        var e = this;
        this.$toobarMoreButton.click(function() {
            $("#tooltip").remove(),
            e.toggleFlag ? e.hide() : (e.toggleFlag = !0, e.$moreBtnWrap.addClass("toolbar-button-wrapper-checked"), e.moreViewResize())
        }),
        this.$toobarMoreButton.on("mousedown",
        function() {
            this.$toobarMoreButton.addClass("pressed").one("mouseup",
            function() {
                this.$toobarMoreButton.removeClass("pressed")
            }.bind(this)),
            this.$toobarMoreButton.one("mouseleave",
            function() {
                this.$toobarMoreButton.removeClass("pressed")
            }.bind(this))
        }.bind(this)),
        $(window).resize(this.debounce(function() {
            e.collapseTool(),
            e.hide()
        },
        58)),
        $(document).ready(function() {
            e.collapseTool()
        })
    },
    moreViewResize: function() {
        var e, t, o = this.$toobarMoreButton.offset(),
        n = $("#web_header_bottom").offset(),
        i = $("#web_header_bottom").height(),
        r = this.$toobarMoreButton.width(),
        a = this.$moreToolsView,
        s = this,
        l = a.children(".hp-ui-button-group"),
        c = l.length;
        l.each(function(e) {
            s.aboveTools.splice(s.len - 2 - (c - e), 1, $(this).detach())
        });
        for (var u = 0; u < this.aboveTools.length; u++) $(this.aboveTools[u]).css({
            display: "inline-block"
        }).appendTo(a);
        var d = $(a.children().get(0));
        d.hasClass("vertical-separator") && d.hide(),
        t = a.css({
            display: "block",
            visibility: "hidden",
            left: -1e3
        }).outerWidth(),
        e = o.left - (t - r),
        $(document.documentElement).width(),
        o.left + r < t && (e = 0),
        a.css({
            top: n.top + i,
            left: e,
            visibility: "visible"
        })
    },
    countToolTotalWidth: function() {
        var e = [],
        t = this.$toolList.length;
        return this.$toolList.slice(0, t - 2).each(function(t) {
            e.push($(this).outerWidth(!0))
        }),
        this.toolsWidths = e,
        e.reduce(function(e, t) {
            return e + t
        },
        0)
    },
    initMoreToolsComponent: function() {
        var e = null;
        return (e = $('<div id="moreToolsView" class="toolbar-more-wrap"></div>')).css({
            position: "absolute",
            background: "#f5f6f8",
            padding: "0 6px 5px 8px",
            "box-sizing": "border-box",
            top: 0,
            left: 0,
            zIndex: "499",
            display: "none",
            "box-shadow": "0 1px 3px rgba(0,0,0,.15)",
            border: " 1px solid",
            "border-color": "rgba(0,0,0,.15)",
            "border-radius": "2px",
            cursor: "default"
        }),
        this.$moreToolsView = e,
        e.appendTo(document.body),
        e
    },
    collapseTool: function() {
        var e = $("#web_header_bottom"),
        t = (e.width(), e.outerWidth(!0), e.width() - 118),
        o = this.len,
        n = 0,
        i = 0,
        r = this.$toolList,
        a = this.toolsWidths;
        this.aboveTools.forEach(function(e, t) {
            e && e.insertBefore(".before-more-separator")
        }),
        t - 42 > this.toolsTotalWidth ? (this.showMore = !1, i = o - 2) : (this.showMore = !0, this.$toolList.each(function(e) {
            t - 42 > n + 54 && (n += a[e], i = e)
        })),
        i < 4 && (i = 4);
        var s = 0,
        l = i;
        if (i < o - 2) {
            for (var c = i; c >= 3; c--) $(r.get(c)).show(),
            this.aboveTools[c] = void 0;
            for (; s < 54 && l > 3; l--) s += $(r.get(l)).width(),
            $(r.get(l)).hide(),
            this.aboveTools[l] = $(r.get(l)).detach();
            l % 2 != 0 && ($(r.get(l)).hide(), this.aboveTools[l] = $(r.get(l)).detach());
            for (var u = i; u < o - 2; u++) $(r.get(u)).hide(),
            this.aboveTools[u] = $(r.get(u)).detach();
            this.collapseCurIndex = l,
            this.showMore = !0,
            $(r.get(o - 2)).show(),
            $(r.get(o - 1)).show()
        } else {
            for (c = i; c >= 3; c--) $(r.get(c)).show(),
            this.aboveTools[c] = void 0;
            this.collapseCurIndex = o - 2,
            this.showMore = !1,
            $(r.get(o - 2)).hide(),
            $(r.get(o - 1)).hide()
        }
    },
    debounce: function(e, t, o) {
        var n, i, r, a, s, l, c, u, d = 0,
        f = !1,
        h = !1,
        g = !0;
        if ("function" != typeof e) throw new TypeError("Expected a function");
        function p(t) {
            var o = n,
            r = i;
            return n = i = void 0,
            d = t,
            a = e.apply(r, o)
        }
        function m(e) {
            var o = e - l;
            return void 0 === l || o >= t || o < 0 || h && e - d >= r
        }
        function v() {
            var e = Date.now();
            if (m(e)) return C(e);
            s = setTimeout(v,
            function(e) {
                var o = e - d,
                n = t - (e - l);
                return h ? Math.min(n, r - o) : n
            } (e))
        }
        function C(e) {
            return s = void 0,
            g && n ? p(e) : (n = i = void 0, a)
        }
        function b(e) {
            var o = Date.now(),
            r = m(o);
            if (n = e, i = this, l = o, r) {
                if (void 0 === s) return function(e) {
                    return d = e,
                    s = setTimeout(v, t),
                    f ? p(e) : a
                } (l);
                if (h) return s = setTimeout(v, t),
                p(l)
            }
            return void 0 === s && (s = setTimeout(v, t)),
            a
        }
        return t = +t || 0,
        u = typeof(c = o),
        null == c || "object" != u && "function" != u || (f = !!o.leading, r = (h = "maxWait" in o) ? Math.max( + o.maxWait || 0, t) : r, g = "trailing" in o ? !!o.trailing: g),
        b.cancel = function() {
            void 0 !== s && clearTimeout(s),
            d = 0,
            n = l = i = s = void 0
        },
        b.flush = function() {
            return void 0 === s ? a: C(Date.now())
        },
        b
    }
};
!function($) {
    $.Custom_Dialog = function() {
        var defaults = {
            animation_speed_hide: 250,
            animation_speed_show: 0,
            auto_close: !1,
            buttons: !0,
            center_buttons: !1,
            custom_class: !1,
            keyboard: !0,
            max_height: 0,
            message: "",
            modal: !0,
            overlay_close: !0,
            overlay_opacity: "1",
            position: "center",
            reposition_speed: 500,
            show_close_button: !0,
            source: !1,
            title: "",
            type: "information",
            show_icon: !1,
            vcenter_short_message: !0,
            width: 0,
            onClose: null,
            only_mobile: !1,
            border_top: !0,
            onCloseBeforeAnimate: null
        }, plugin = this, options = {}, timeout;
        plugin.settings = {},
        "string" == typeof arguments[0] && (options.message = arguments[0]),
        "object" != typeof arguments[0] && "object" != typeof arguments[1] || (options = $.extend(options, "object" == typeof arguments[0] ? arguments[0] : arguments[1])),
        plugin.init = function() {
            var e;
            plugin && (plugin.dialog && plugin.dialog.remove && plugin.dialog.remove(),
            plugin.overlay && plugin.dialog.remove && plugin.overlay.remove()),
            plugin.settings = $.extend({}, defaults, options),
            plugin.isIE6 = "explorer" == browser.name && 6 == browser.version || !1,
            plugin.settings.modal && (plugin.overlay = $("<div>", {
                class: "CustomDialogOverlay"
            }).css({
                position: (plugin.isIE6,
                "absolute"),
                left: 0,
                top: 0,
                opacity: plugin.settings.overlay_opacity,
                "z-index": "100004"
            }),
            plugin.settings.overlay_close && (plugin.overlay.bind("click", function(e) {
                plugin.close(void 0, e)
            }),
            plugin.overlay.bind("mousedown", function(e) {
                e.preventDefault()
            })),
            plugin.overlay.appendTo("body")),
            plugin.dialog = $("<div>", {
                class: "CustomDialog" + (plugin.settings.custom_class ? " " + plugin.settings.custom_class : "")
            }).css({
                position: (plugin.isIE6,
                "absolute"),
                left: 0,
                top: 0,
                visibility: "hidden",
                "z-index": "100004"
            }),
            plugin.settings.only_mobile && plugin.dialog.css("min-height", "163px"),
            plugin.settings.border_top || plugin.dialog.css("border-top", "none"),
            !plugin.settings.buttons && plugin.settings.auto_close && plugin.dialog.attr("id", "CustomDialog_" + Math.floor(9999 * Math.random()));
            var t = parseInt(plugin.settings.width, 10);
            !isNaN(t) && t == plugin.settings.width && t.toString() == plugin.settings.width.toString() && t > 0 && plugin.dialog.css({
                width: plugin.settings.width
            }),
            plugin.settings.title && (e = $("<h3>", {
                class: "CustomDialog_Title"
            }).html(plugin.settings.title),
            plugin.settings.only_mobile && e.css({
                "text-align": "center",
                "border-bottom": "none",
                "padding-top": "16px",
                "font-size": "20px",
                "font-weight": "normal",
                "padding-bottom": "8px"
            }),
            e.appendTo(plugin.dialog));
            var o = get_buttons()
              , n = $("<div>", {
                class: "CustomDialog_BodyOuter" + (plugin.settings.title ? "" : " CustomDialog_NoTitle") + (o ? "" : " CustomDialog_NoButtons")
            }).appendTo(plugin.dialog);
            if (plugin.message = $("<div>", {
                class: "CustomDialog_Body" + (!1 !== plugin.settings.show_icon ? " CustomDialog_Icon CustomDialog_" + get_type() : "") + (plugin.settings.body_padding ? " cus_body_padding" : "")
            }),
            plugin.settings.only_mobile && plugin.message.css({
                "padding-top": "0px",
                "padding-bottom": "20px"
            }),
            plugin.settings.max_height > 0 && (plugin.message.css("max-height", plugin.settings.max_height),
            plugin.isIE6 && plugin.message.attr("style", "height: expression(this.scrollHeight > " + plugin.settings.max_height + ' ? "' + plugin.settings.max_height + 'px" : "85px")')),
            plugin.settings.vcenter_short_message) {
                var i = $("<div>");
                plugin.settings.only_mobile && i.css("font-size", "16px"),
                i.html(plugin.settings.message).appendTo(plugin.message)
            } else
                plugin.message.html(plugin.settings.message);
            if (plugin.settings.source && "object" == typeof plugin.settings.source) {
                var r = plugin.settings.vcenter_short_message ? $("div:first", plugin.message) : plugin.message;
                for (var a in plugin.settings.source)
                    switch (a) {
                    case "ajax":
                        var s = "string" == typeof plugin.settings.source[a] ? {
                            url: plugin.settings.source[a]
                        } : plugin.settings.source[a]
                          , l = $("<div>").attr("class", "CustomDialog_Preloader").appendTo(r);
                        s.success = function(e) {
                            l.remove(),
                            r.append(e),
                            draw(!1)
                        }
                        ,
                        $.ajax(s);
                        break;
                    case "iframe":
                        var c = $.extend({
                            width: "100%",
                            height: "100%",
                            marginheight: "0",
                            marginwidth: "0",
                            frameborder: "0"
                        }, "string" == typeof plugin.settings.source[a] ? {
                            src: plugin.settings.source[a]
                        } : plugin.settings.source[a]);
                        r.append($("<iframe>").attr(c));
                        break;
                    case "inline":
                        r.append(plugin.settings.source[a])
                    }
            }
            if (plugin.message.appendTo(n),
            o) {
                o.reverse();
                var u = $("<div>", {
                    class: "CustomDialog_Buttons"
                }).appendTo(plugin.dialog);
                plugin.settings.only_mobile && u.css({
                    margin: "0",
                    width: "100%"
                }),
                $.each(o, function(e, t) {
                    var o;
                    o = plugin.settings.only_mobile ? $("<div>", 0 == e ? {
                        class: "ButtonMobile0"
                    } : {
                        class: "ButtonMobile1"
                    }) : $("<button>", {
                        type: "button",
                        class: "CustomDialog_Button_" + e
                    }),
                    $.isPlainObject(t) ? o.html(t.caption) : o.html(t),
                    plugin.settings.custom_class || plugin.settings.only_mobile || o.html() !== $.Custom_Dialog.DefaultButtons.Cancel || o.addClass("ZebraDialog_Buttons_Cancel"),
                    plugin.settings.only_mobile ? (o.on("touchstart", function() {
                        $(this).css("background", "#e9e9e9")
                    }),
                    o.on("touchend", function() {
                        $(this).css("background", "#fff")
                    })) : o.bind("mousedown", function(e) {
                        e.preventDefault(),
                        e.stopPropagation()
                    }),
                    o.bind("click", function(e) {
                        var o = !0;
                        void 0 !== t.callback && (o = t.callback(plugin.dialog, e)),
                        !1 !== o && plugin.close(void 0 !== t.caption ? t.caption : t, e)
                    }),
                    o.appendTo(u)
                }),
                plugin.settings.only_mobile ? u.wrap($("<div>").addClass("CustomDialog_ButtonsOuterMobile" + (plugin.settings.center_buttons ? " CustomDialog_Buttons_Centered" : ""))) : u.wrap($("<div>").addClass("CustomDialog_ButtonsOuter" + (plugin.settings.center_buttons ? " CustomDialog_Buttons_Centered" : "")))
            }
            if (plugin.dialog.appendTo("body"),
            plugin.settings.show_close_button) {
                var d = $('<span class="CustomDialog_Close">&times;</span>').bind("click", function(e) {
                    plugin.close(void 0, e)
                }).appendTo(e || plugin.message);
                d.bind("mousedown", function(e) {
                    e.preventDefault(),
                    e.stopPropagation()
                }),
                e && d.css({
                    right: parseInt(e.css("paddingRight"), 10),
                    top: (parseInt(e.css("height"), 10) + parseInt(e.css("paddingTop"), 10) + parseInt(e.css("paddingBottom"), 10) - d.height()) / 2
                })
            }
            return $(window).bind("resize.Custom_Dialog", function() {
                clearTimeout(timeout),
                timeout = setTimeout(function() {
                    draw()
                }, 100)
            }),
            plugin.settings.keyboard && $(document).bind("keyup.Custom_Dialog", function(e) {
                return 27 == e.which && plugin.close(void 0, e),
                !0
            }),
            plugin.isIE6 && $(window).bind("scroll.Custom_Dialog", function() {
                emulate_fixed_position()
            }),
            !1 !== plugin.settings.auto_close && (plugin.dialog.bind("click", function(e) {
                clearTimeout(plugin.timeout),
                plugin.close(void 0, e)
            }),
            plugin.dialog.bind("mousedown", function(e) {
                e.preventDefault(),
                e.stopPropagation()
            }),
            plugin.timeout = setTimeout(plugin.close, plugin.settings.auto_close)),
            draw(!1),
            plugin
        }
        ,
        plugin.close = function(e, t) {
            $(document).unbind(".Custom_Dialog"),
            $(window).unbind(".Custom_Dialog"),
            plugin.overlay && plugin.overlay.animate({
                opacity: 0
            }, plugin.settings.animation_speed_hide, function() {
                plugin.overlay.remove()
            }),
            plugin.settings.onCloseBeforeAnimate && "function" == typeof plugin.settings.onCloseBeforeAnimate && plugin.settings.onCloseBeforeAnimate(void 0 !== e ? e : "", t),
            plugin.dialog.animate({
                opacity: 0
            }, plugin.settings.animation_speed_hide, function() {
                plugin.dialog.remove(),
                plugin.settings.onClose && "function" == typeof plugin.settings.onClose && plugin.settings.onClose(void 0 !== e ? e : "", t)
            })
        }
        ;
        var draw = function() {
            var viewport_width = $(window).width()
              , viewport_height = $(window).height()
              , dialog_width = plugin.dialog.width()
              , dialog_height = plugin.dialog.height()
              , values = {
                left: 0,
                top: 0,
                right: viewport_width - dialog_width,
                bottom: viewport_height - dialog_height,
                center: (viewport_width - dialog_width) / 2,
                middle: (viewport_height - dialog_height) / 2
            };
            if (plugin.dialog_left = void 0,
            plugin.dialog_top = void 0,
            $.isArray(plugin.settings.position) && 2 == plugin.settings.position.length && "string" == typeof plugin.settings.position[0] && plugin.settings.position[0].match(/^(left|right|center)[\s0-9\+\-]*$/) && "string" == typeof plugin.settings.position[1] && plugin.settings.position[1].match(/^(top|bottom|middle)[\s0-9\+\-]*$/) && (plugin.settings.position[0] = plugin.settings.position[0].toLowerCase(),
            plugin.settings.position[1] = plugin.settings.position[1].toLowerCase(),
            $.each(values, function(index, value) {
                for (var i = 0; i < 2; i++) {
                    var tmp = plugin.settings.position[i].replace(index, value);
                    tmp != plugin.settings.position[i] && (0 === i ? plugin.dialog_left = eval(tmp) : plugin.dialog_top = eval(tmp))
                }
            })),
            void 0 !== plugin.dialog_left && void 0 !== plugin.dialog_top || (plugin.dialog_left = values.center,
            plugin.dialog_top = values.middle),
            plugin.settings.vcenter_short_message) {
                var message = plugin.message.find("div:first")
                  , message_height = message.height()
                  , container_height = plugin.message.height();
                message_height < container_height && message.css({
                    "padding-top": (container_height - message_height) / 2
                })
            }
            "boolean" == typeof arguments[0] && !1 === arguments[0] || 0 === plugin.settings.reposition_speed ? plugin.dialog.css({
                left: "50%",
                top: "50%",
                visibility: "visible",
                opacity: 0,
                transform: "translate3d(-50%, -50%, 0)",
                "-webkit-transform": "translate3d(-50%, -50%, 0)",
                "-moz-transform": "translate3d(-50%, -50%, 0)",
                "-ms-transform": "translate3d(-50%, -50%, 0)"
            }).animate({
                opacity: 1
            }, plugin.settings.animation_speed_show) : (plugin.dialog.stop(!0),
            plugin.dialog.css("visibility", "visible").animate({
                left: "50%",
                top: "50%"
            }, plugin.settings.reposition_speed)),
            plugin.dialog.find("a[class^=CustomDialog_Button]:first").focus(),
            plugin.isIE6 && setTimeout(emulate_fixed_position, 500)
        }
          , emulate_fixed_position = function() {
            var e = $(window).scrollTop()
              , t = $(window).scrollLeft();
            plugin.settings.modal && plugin.overlay.css({
                top: e,
                left: t
            }),
            plugin.dialog.css({
                left: "50%",
                top: "50%"
            })
        }
          , get_buttons = function() {
            if (!0 !== plugin.settings.buttons && !$.isArray(plugin.settings.buttons))
                return !1;
            if (!0 === plugin.settings.buttons)
                switch (plugin.settings.type) {
                case "question":
                    plugin.settings.buttons = [$.Custom_Dialog.DefaultButtons.OK, $.Custom_Dialog.DefaultButtons.Cancel];
                    break;
                default:
                    plugin.settings.buttons = [$.Custom_Dialog.DefaultButtons.OK]
                }
            return plugin.settings.buttons
        }
          , get_type = function() {
            var e = plugin.settings.type;
            switch (e) {
            case "confirmation":
            case "error":
            case "information":
            case "question":
            case "warning":
                break;
            default:
                e = "confirmation"
            }
            return e.charAt(0).toUpperCase() + e.slice(1).toLowerCase()
        }
          , browser = {
            init: function() {
                this.name = this.searchString(this.dataBrowser) || "",
                this.version = this.searchVersion(navigator.userAgent) || this.searchVersion(navigator.appVersion) || ""
            },
            searchString: function(e) {
                for (var t = 0; t < e.length; t++) {
                    var o = e[t].string
                      , n = e[t].prop;
                    if (this.versionSearchString = e[t].versionSearch || e[t].identity,
                    o) {
                        if (-1 != o.indexOf(e[t].subString))
                            return e[t].identity
                    } else if (n)
                        return e[t].identity
                }
            },
            searchVersion: function(e) {
                var t = e.indexOf(this.versionSearchString);
                if (-1 != t)
                    return parseFloat(e.substring(t + this.versionSearchString.length + 1))
            },
            dataBrowser: [{
                string: navigator.userAgent,
                subString: "MSIE",
                identity: "explorer",
                versionSearch: "MSIE"
            }]
        };
        return browser.init(),
        plugin.init()
    }
    ,
    $.Custom_Dialog.DefaultButtons = {
        OK: "确定",
        Cancel: "取消"
    },
    $.Custom_Dialog.Type = {
        Confirmation: "confirmation",
        Error: "error",
        Question: "question",
        Information: "information",
        Warning: "warning"
    }
}(jQuery);
$.optimizedResize = function() {
    var e = []
      , t = !1;
    function n() {
        t || (t = !0,
        window.requestAnimationFrame ? window.requestAnimationFrame(r) : setTimeout(r, 66))
    }
    function r() {
        e.forEach(function(e) {
            e()
        }),
        t = !1
    }
    return function(t) {
        e.length || window.addEventListener("resize", n),
        function(t) {
            t && e.push(t)
        }(t)
    }
}()

// 公式编辑
!function() {
    var e = window._formulaConfig || {}
      , t = Object.keys(e)
      , o = {}
      , n = {};
    (window._formulaCategory || []).forEach(function(e) {
        o[e.key] = e.values,
        e.values.forEach(function(t) {
            n[t] = e.key
        })
    }),
    Function.prototype.throttle || (Function.prototype.throttle = function(e) {
        var t, o = this, n = !0;
        return function() {
            var i = arguments
              , r = this;
            return n ? (o.apply(r, i),
            n = !1) : !t && void (t = setTimeout(function() {
                clearTimeout(t),
                t = null,
                o.apply(r, i)
            }, e))
        }
    }
    ),
    Array.prototype.equal || (Array.prototype.equal = function(e) {
        var t = e;
        if (this === t)
            return !0;
        if (this instanceof Array == !1 || t instanceof Array == !1 || this.length !== t.length)
            return !1;
        for (var o = 0; o < this.length; o++)
            if (this[o] !== t[o])
                return !1;
        return !0
    }
    );
    var i = {
        _show: !1,
        _dom: null,
        _toolbarDrop_selector: "#toolbar-sheet-calculate-group .dropdown-wrapper-box",
        _width: 308,
        _duration: 300,
        _easing: "linear",
        _formula: "",
        _formulaDetail: null,
        _searchText: "",
        _recentlyList: [],
        _searchList: [],
        _selectList: [],
        _list: null,
        _type: "select",
        _hasBind: !1,
        _setType: function(e) {
            if ("search" === e || "select" === e) {
                this._type !== e && (this._type = e);
                var t = this
                  , o = $(this._dom).find(".formula-editor-select")
                  , n = $(this._dom).find(".formula-editor-search");
                if ("search" === this._type) {
                    this._selectList = [],
                    o.hide(),
                    n.show();
                    var i = this._getFormulaList()
                      , r = []
                      , a = [];
                    i && i.forEach(function(e) {
                        var o = e.indexOf(t._searchText);
                        0 === o ? r.push(e) : o > 0 && a.push(e)
                    });
                    var s = r.concat(a);
                    this._setSearchList(s),
                    s.length ? (t._enabledInjectBtn(),
                    $(".formula-editor-search .formula-editor-list li:first-child").trigger("click")) : ($(".formula-editor-search .formula-editor-list").html('<li class="formula-item">无搜索结果。</li>'),
                    this._formula = "",
                    t._disabledInjectBtn(),
                    t._updateDetail(""))
                } else
                    this._searchList = [],
                    o.show(),
                    n.hide(),
                    $("#formula-editor-container .formula-editor-select .all").trigger("click")
            }
        },
        _getDetailObj: function(e) {
            var t = this._getFormulaDetail();
            return t[e] || t[e.toUpperCase()] || t[e.toLowerCase()]
        },
        _getFormulaDetail: function() {
            return this._formulaDetail ? this._formulaDetail : this._formulaDetail = e
        },
        _getFormulaList: function() {
            if (this._list)
                return this._list;
            var e = this._getFormulaDetail()
              , o = function() {
                var o = [];
                for (var n in e)
                    if (t.indexOf(n.toLowerCase()) > -1) {
                        var i = e[n];
                        o.push(i.title)
                    }
                return o
            }();
            return (o = o.join("").replace(/\(\*?\)/g, ",").split(",")).pop(),
            this._list = o,
            o
        },
        _setSearchList: function(e) {
            if (!this._searchList.equal(e)) {
                this._searchList = e;
                var t = $("#formula-editor-container .formula-editor-search .formula-editor-list")
                  , o = "";
                e.forEach(function(e) {
                    o += '<li class="formula-item">' + e + "</li>"
                }),
                t.html(o),
                t.find("li:first-child").trigger("click")
            }
        },
        _updateDetail: function(e) {
            var t = $(".formula-editor-detail-container")
              , o = $(".formula-detail-item-box");
            if (t.html(""),
            o.remove(),
            e) {
                var n = e.title.replace(/\(\*?\)/, "")
                  , i = e.subItem[1].detail
                  , r = function() {
                    for (var t = e.titleParams, o = [], n = 0; n < t.length; n++)
                        o.push(t[n].name);
                    return e.title.replace(/\*/, o.join(", "))
                }()
                  , a = e.subItem[0].detail
                  , s = '<div class="formula-detail-item"><p class="formula-item-title">' + n + '</p><p class="formula-item-sub-title formula-item-instruction">' + i + "</p></div>";
                o = $('<div class="formula-detail-item-box"><div class="formula-detail-item"><p class="formula-item-title">语法：</p><p class="formula-item-sub-title formula-item-grammer">' + r + '</p></div><div class="formula-detail-item"><p class="formula-item-title">示例：</p><p class="formula-item-sub-title formula-item-example">' + a + "</p></div><div>"),
                t.append($(s)),
                t.append(o);
                for (var l = 2; l < e.subItem.length; l++) {
                    var c = e.subItem[l]
                      , u = '<div class="formula-detail-item"><p class="formula-item-title">' + c.title + '：</p><p class="formula-item-sub-title formula-item-param">' + c.detail + "</p></div>";
                    o.append($(u))
                }
                this._formula = n,
                t.show()
            }
        },
        _disabledInjectBtn: function() {
            $("#formula-editor-inject-button").attr("disabled", "disabled")
        },
        _enabledInjectBtn: function() {
            $("#formula-editor-inject-button").removeAttr("disabled")
        },
        _updateRencentlyList: function() {
            if ("rencently" === this._typeCode) {
                var e = this._recentlyList;
                if (!this._selectList.equal(e)) {
                    this._selectList = this._recentlyList;
                    var t = $("#formula-editor-container .formula-editor-select .formula-editor-list");
                    if (e && e.length) {
                        var o = "";
                        e.forEach(function(e) {
                            o += '<li class="formula-item">' + e + "</li>"
                        }),
                        t.html(o),
                        t.find("li:first-child").trigger("click")
                    } else
                        t.html('<li class="formula-item">无</li>'),
                        this._updateDetail("")
                }
            }
        },
        gethistory: function() {
            // var e = this;
            // $.ajax({
            //     url: "/sdc/func/gethistory",
            //     type: "GET",
            //     success: function(t) {
            //         t = {retcode: 0, data: ["AND", "OR"]};
            //         var o = t.data || [];
            //         e._recentlyList = o,
            //         e._updateRencentlyList(),
            //         e._setToolbarLi()
            //     },
            //     error: function(e, t) {}
            // }),
            // e.bindToolBarEvent()
        },
        _updatehistory: function(e) {
            if (e) {
                var t = e.toUpperCase();
                $.ajax({
                    url: "/sdc/func/updatehistory",
                    type: "POST",
                    dataType: "json",
                    data: {
                        data: JSON.stringify([t.toUpperCase()])
                    },
                    success: function(e) {
                        var t = e.retcode
                          , o = e.tips;
                        0 != t && console.error("/sdc/func/updatehistory error: ", o)
                    },
                    error: function(e) {
                        console.error("更新最近使用函数列表出错。error: ", e)
                    }
                })
            }
        },
        _render: function() {
            var e = window.formulaEditorHTML
              , t = $(e)
              , o = $("body")
              , n = parseInt($("#web_header_bottom").top) ? parseInt($("#web_header_bottom").top) + 2 + "px" : "";
            t.css("top", n),
            $(o).append(t),
            this._dom = t[0]
        },
        _setToolbarLi: function() {
            var e, t = (e = [],
            this._recentlyList.forEach(function(t) {
                ["SUM", "MIN", "MAX", "COUNT", "AVERAGE", "STOCK"].indexOf(t.toUpperCase()) > -1 || e.push(t)
            }),
            e.slice(0, 3).concat().reverse()), o = $(this._toolbarDrop_selector);
            o.find("li").remove(".formula-editor-drop"),
            o.find(".j-dropdown-separator-wrapper").remove(),
            t && 0 !== t.length || o.find("li:last-child").addClass("formula-editor-drop-first");
            var n = o.children("li").length
              , i = o.find("li:last-child");
            t.length && i.before($('<div class="dropdown-separator-wrapper j-dropdown-separator-wrapper"><div class="dropdown-separator"></div></div>'));
            for (var r = t.length - 1; r >= 0; r--) {
                var a = t[r]
                  , s = this._getDetailObj(a)
                  , l = s && s.title.replace(/\(\*?\)/, "")
                  , c = s && s.abbr
                  , u = "li-dropdown-2-" + (n + (t.length - 1 - r))
                  , d = $('<li class="li-dropdown-c li-dropdown formula-editor-drop" data-tooltip="' + c + '" id="' + u + '"><div class="dropdown-c-content-wrapper dropdown-content-wrapper"><div class="dropdown-c-content dropdown-content">' + l + "</div></div></li>");
                i.before(d),
                padutils.horizontalTooltip(d, {
                    "min-width": 66,
                    left: 10
                })
            }
        },
        injectFormula: function(e) {
            e = e.toUpperCase(),
            immTableSheet.sheetHot.canvasView.excel.insertFormula({
                formula: e
            });
            for (var t = [], o = 0; o < this._recentlyList.length; o++)
                this._recentlyList[o] !== e && t.push(this._recentlyList[o]);
            t.unshift(e),
            this._recentlyList = t,
            this._setToolbarLi(),
            this._updateRencentlyList(),
            this._updatehistory(e),
            tdwReport({
                obj3: e,
                module: "insert",
                opername: "doc_function",
                action: function(e) {
                    return n[e]
                }(e)
            })
        },
        _bindEvent: function() {
            var e = this;
            $(document).on("click", "#formula-editor-close", function(t) {
                e.hide()
            });
            var t = $("#formula-editor-container .formula-editor-search-close");
            t.on("click", function() {
                e._setType("select"),
                $("#formula-editor-search-input").val(""),
                t.hide()
            }),
            $(document).on("input propertychange", "#formula-editor-search-input", function(o) {
                var n = o.target.value.toString().trim().toUpperCase();
                e._searchText = n,
                n ? (e._setType("search"),
                t.show()) : (e._setType("select"),
                t.hide())
            }
            .throttle(200)),
            $("#formula-editor-search-input").on("keyup", this._selectFormulaByKeyboard.bind(this)),
            $(document).on("click", "#formula-editor-inject-button", function(t) {
                var o = e._formula;
                e.injectFormula(o)
            }),
            $(document).on("click", "#formula-editor-container .formula-editor-list .formula-item", function(t) {
                var o = $(t.target)
                  , n = o.text().toLowerCase();
                o.parent().find(".active").removeClass("active"),
                o.addClass("active");
                var i = e._getDetailObj(n);
                e._updateDetail(i)
            }),
            $(document).on("click", ".formula-editor-select .all", function(t) {
                $(".formula-editor-select li").removeClass("active"),
                $(".formula-editor-select .all").addClass("active"),
                e._typeCode = "all";
                var o = e._getFormulaList();
                if (o && o.length ? e._enabledInjectBtn() : e._disabledInjectBtn(),
                !e._selectList.equal(o)) {
                    e._selectList = o.sort();
                    var n = $("#formula-editor-container .formula-editor-select .formula-editor-list")
                      , i = "";
                    o.forEach(function(e) {
                        i += '<li class="formula-item">' + e + "</li>"
                    }),
                    n.html(i),
                    n.find("li:first-child").trigger("click")
                }
            }),
            $(document).on("click", ".formula-editor-select .recently", function(t) {
                if ("rencently" !== e._typeCode) {
                    $(".formula-editor-select li").removeClass("active"),
                    $(".formula-editor-select .recently").addClass("active"),
                    e._typeCode = "rencently";
                    var o = e._recentlyList;
                    o && o.length ? e._enabledInjectBtn() : e._disabledInjectBtn(),
                    e._updateRencentlyList()
                }
            }),
            $(document).on("click", ".formula-editor-select .tab-type li", function(t) {
                $target = $(t.target),
                $(".formula-editor-select .left li").removeClass("active"),
                $target.addClass("active");
                var n = $target.data("formula-type");
                e._typeCode = n;
                var i = o[n] || [];
                if (i && i.length ? e._enabledInjectBtn() : e._disabledInjectBtn(),
                !e._selectList.equal(i)) {
                    e._selectList = i.sort();
                    var r = $("#formula-editor-container .formula-editor-select .formula-editor-list")
                      , a = "";
                    i.forEach(function(e) {
                        a += '<li class="formula-item">' + e + "</li>"
                    }),
                    r.html(a),
                    r.find("li:first-child").trigger("click")
                }
            }),
            $(window).resize($.optimizedResize(function() {
                e._show && e.updatePosition(!0)
            }))
        },
        _selectFormulaByKeyboard: function(e) {
            var t = e.keyCode
              , o = 38 === t
              , n = 40 === t;
            if (13 === t && this._formula)
                this.injectFormula(this._formula);
            else if (o || n) {
                var i = $(".formula-editor-search .formula-editor-list");
                if (!(i.children().length < 2)) {
                    var r = i.find(".active");
                    if (r.length && -1 !== r.index()) {
                        var a = o ? r.prev() : r.next();
                        if (a = 1 === a.length ? a : o ? i.find(".formula-item").last() : i.find(".formula-item").first()) {
                            a.addClass("active").siblings().removeClass("active");
                            var s = a.text();
                            e.target.value = s,
                            this._updateDetail(this._getDetailObj(s));
                            var l = $(".formula-editor-search.formula-editor-table")
                              , c = l.prop("scrollHeight")
                              , u = l.prop("clientHeight");
                            if (!(c <= u)) {
                                var d = a.index()
                                  , f = parseInt(u / a.height());
                                if (d + 1 > f) {
                                    var h = (d - f + 1) * a.height();
                                    l.scrollTop(h)
                                } else
                                    l.scrollTop(0)
                            }
                        }
                    }
                }
            }
        },
        _init: function() {
            this._render(),
            this._bindEvent(),
            this.gethistory(),
            this._setType("select")
        },
        bindToolBarEvent: function() {
            if (!this._hasBind) {
                var e = this;
                $(document).on("click", e._toolbarDrop_selector + " li.formula-editor-drop", function(t) {
                    var o = $(t.currentTarget).find(".dropdown-content").text();
                    e.injectFormula(o),
                    $("#dropdown-wrapper-2").removeClass("open")
                }),
                this._hasBind = !0
            }
        },
        getRecentlyFormulas: function() {
            return this._recentlyList
        },
        show: function() {
            if (!this._show) {
                this._show = !0,
                this._dom || this._init();
                var e = $("#formula-editor-container") || $(this._dom);
                this.updatePosition(),
                e.animate({
                    right: 0
                }, this._duration, this._easing),
                r(this._width - 3),
                $("#formula-editor-close").show()
            }
        },
        hide: function() {
            this._show = !1,
            $(this._dom).animate({
                right: -this._width
            }, this._duration, this._easing),
            r(0),
            this.restoreToolbarAfterPanelHide(),
            $("#formula-editor-close").hide()
        },
        adaptToolbarWithFormulaPanel: function(e) {
            return $("#web_header_bottom").css("width", $(document.documentElement).width() - this._width),
            e || window.ToolBarAdaption && ToolBarAdaption.collapseTool(),
            this
        },
        restoreToolbarAfterPanelHide: function() {
            return $("#web_header_bottom").css("width", "100%"),
            window.ToolBarAdaption && (ToolBarAdaption.collapseTool(),
            ToolBarAdaption.hide()),
            this
        },
        updatePosition: function(e) {
            if (this._show) {
                var t = $(".guidebar").height()
                  , o = $("#foldIcon").hasClass("hidefold")
                  , n = $("#web_header_top").height()
                  , i = t + (o ? 0 : n);
                $("#formula-editor-container").css({
                    top: i
                });
                var r = $("#formula-editor-line").position().top + 20;
                return $(".formula-editor-detail-container").css({
                    top: r
                }).show(),
                $("#formula-editor-close").css({
                    top: i + 12
                }),
                this.adaptToolbarWithFormulaPanel(e),
                this
            }
        },
        isShow: function() {
            return this._show
        },
        getWidth: function() {
            return this._width
        }
    };
    function r(e) {
        // window.immTableSheet.sheetHot.canvasView.excel.changeOffsetX(e)
    }
    $(document).ready(function() {
        window.formulaEditor = {
            show: i.show.bind(i),
            hide: i.hide.bind(i),
            gethistory: i.gethistory.bind(i),
            bindToolBarEvent: i.bindToolBarEvent.bind(i),
            getRecentlyFormulas: i.getRecentlyFormulas.bind(i),
            injectFormula: i.injectFormula.bind(i),
            updatePosition: i.updatePosition.bind(i),
            getWidth: i.getWidth.bind(i),
            isShow: i.isShow.bind(i)
        }
    })
}();
// 系统设置 linkViewer
!function(){
    var i = {
        _show: !1,
        _dom: null,
        _width: 308,
        _duration: 300,
        _easing: "linear",
        _hasBind: !1,
        _render: function() {
            var e = window.linkViewerHTML
              , t = $(e)
              , o = $("body")
              , n = parseInt($("#web_header_bottom").top) ? parseInt($("#web_header_bottom").top) + 2 + "px" : "";
            t.css("top", n),
            $(o).append(t),
            this._dom = t[0]
        },
        _init: function() {
            this._render(),
            this._bindEvent(),
            this._linksload()
        },
        _bindEvent: function() {
            var e = this;
            $(document).on("click", "#link-viewer-close", function(t) {
                e.hide()
            });
            $(window).resize($.optimizedResize(function() {
                e._show && e.updatePosition(!0)
            }))
        },
        show: function() {
            if (!this._show) {
                this._show = !0,
                this._dom || this._init();
                var e = $("#link-viewer-container") || $(this._dom);
                this.updatePosition(),
                e.animate({
                    right: 0
                }, this._duration, this._easing),
                $("#link-viewer-close").show(),
                linkWrap.hide(),
                linkModifyWrap.show();
            }
        },
        hide: function() {
            this._show = !1,
            $(this._dom).animate({
                right: -this._width
            }, this._duration, this._easing),
            this.restoreToolbarAfterPanelHide(),
            $("#link-viewer-close").hide(),
            linkWrap.hide(),
            linkModifyWrap.hide();
        },
        adaptToolbarWithFormulaPanel: function(e) {
            return $("#web_header_bottom").css("width", $(document.documentElement).width() - this._width),
            e || window.ToolBarAdaption && ToolBarAdaption.collapseTool(),
            this
        },
        restoreToolbarAfterPanelHide: function() {
            return $("#web_header_bottom").css("width", "100%"),
            window.ToolBarAdaption && (ToolBarAdaption.collapseTool(),
            ToolBarAdaption.hide()),
            this
        },
        updatePosition: function(e) {
            if (this._show) {
                var t = $(".guidebar").height()
                  , o = $("#foldIcon").hasClass("hidefold")
                  , n = $("#web_header_top").height()
                  , i = t + (o ? 0 : n);
                $("#link-viewer-container").css({
                    top: i
                });
                $("#link-viewer-close").css({
                    top: i + 12
                }),
                $("#link-viewer-table").css({
                    height: ($(window).height() - i - 200)+'px'
                }),
                this.adaptToolbarWithFormulaPanel(e),
                this
            }
        },
        isShow: function() {
            return this._show
        },
        getWidth: function() {
            return this._width
        },
        add_link_data: function(data){
            // 获取模板内容
            var tr =  document.createElement('tr');
            $(tr).append(window.linkLineTpl);
            $(tr).find(".fieldName").html(data.fieldName);
            $(tr).find(".fieldCode").html(data.fieldCode);
            $(tr).find(".cell").html(data.cell).addClass(data.cell);
            $(tr).find(".xuhao").html($(this._dom).find(".switch-rules tbody:eq(0) tr").length + 1);
            $(this._dom).find(".switch-rules tbody:eq(0)").append(tr);

        },
        _linksload : function(){
            var config = immTableSheet.jExcel.config;
            if(!config){
                return;
            }
            var fileType = config.fileType || 'excel';
            $('[name=lineNum]').val(config.lineNum || '');
            $('[name=lineCount]').val(config.lineCount || '');
            $('[name=fileType][value='+fileType+']').prop('checked', true);
            // 初始化绑定信息
            $(this._dom).find(".switch-rules tbody:eq(0)").html("");
            for (var i = 0; i < immTableSheet.jExcel.data.length; i++) {
                for (var j = 0; j < immTableSheet.jExcel.data[i].length; j++) {
                    if(immTableSheet.jExcel.cellDataArray[i][j].link){
                        var link = immTableSheet.jExcel.cellDataArray[i][j].link;
                        var cellHead = $.fn.jexcel('getLetter', j, i);
                        this.add_link_data({
                            cell: cellHead,
                            fieldCode: link[0],
                            fieldName: link[1]
                        });
                    }
                }
            }
            // 点击选中单元格
            $('.switch-rules').on('click', '.cell', function(){
                var cell = $('#'+jExcelID).jexcel('getCell', $(this).text());
                $('#'+jExcelID).jexcel('updateSelection', cell, cell);
                // $(cell).trigger("click");
            });
        }
    }
    $(document).ready(function() {
        window.linkViewer = {
            show: i.show.bind(i),
            hide: i.hide.bind(i),
            updatePosition: i.updatePosition.bind(i),
            getWidth: i.getWidth.bind(i),
            isShow: i.isShow.bind(i),
            add_link_data: i.add_link_data.bind(i)
        }
        // linkViewer.show();
    })

    // 配置信息修改代码
    $("body").on("change", "[name=fileType]", function(){
        immTableSheet.jExcel.config.fileType = $(this).val();
    });
    $("body").on("click", "#setLineInfo", function(){
        var selection = $("#"+jExcelID).jexcel('getSelection');
        if(!selection){
            padutils.showToolToast('请选择单元格', {
                zIndex: 99999
            }, {
                toastType: "typeWarnning",
                toolToastCloseTime: 3e3
            })
            return;
        }
        var o = selection[0];
        var d = selection[1];
        var px = 0;
        var ux = $("#"+jExcelID).find('tbody tr:first td').not('.jexcel_label').length-1;
        var py = $(o).prop('id').split('-')[1];
        var uy = $(d).prop('id').split('-')[1];
        o = $("#"+jExcelID).find('tbody td[id='+px+'-'+py+']');
        d = $("#"+jExcelID).find('tbody td[id='+ux+'-'+uy+']');
        $("#"+jExcelID).jexcel('updateSelection', o, d);
        var selection = $("#"+jExcelID).jexcel('getSelection');
        or = $(selection[0]).prop('id').split('-');
        de = $(selection[1]).prop('id').split('-');
        $.fn.jexcel.defaults[jExcelID].config.lineNum = parseInt(or[1]);
        $.fn.jexcel.defaults[jExcelID].config.lineCount = parseInt(de[1]) - parseInt(or[1]) + 1;
        $('#link-viewer-container [name=lineNum]').val($.fn.jexcel.defaults[jExcelID].config.lineNum);
        $('#link-viewer-container [name=lineCount]').val($.fn.jexcel.defaults[jExcelID].config.lineCount);
        $('#link-viewer-container [name=fileType][value=excel]').prop('checked', true);
    });
}();

toolbarController = null;
((toolbarController = new ToolBarSheet).bindHotEvents(),
ToolBarAdaption.init())
padeditbar.init();
padutils.tooltip("[data-tooltip]");

// Ctrl+S和保存按钮进行数据保存
window.onceCtrlS = function (e) {
    if (37 === e.keyCode || 38 === e.keyCode || 39 === e.keyCode || 40 === e.keyCode)
        return !0;
    var i = (e.ctrlKey || e.metaKey) && !e.altKey;
    if (70 === e.keyCode && i || 82 === e.keyCode && i || 116 === e.keyCode || 112 === e.keyCode && i || 122 === e.keyCode || 73 === e.keyCode && e.altKey && (e.ctrlKey || e.metaKey) || 123 === e.keyCode)
        return !0;
    if (83 === e.keyCode && i || e.target.className.indexOf("save") >= 0) {
        $(immTableSheet.jExcel.div).jexcel("saveData");
        return e.preventDefault(), !1
    }
}
$("body").bind("keydown", window.onceCtrlS);
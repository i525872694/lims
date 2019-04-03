"use strict"; 
!function(t) {
    function e(t) {
        return t ? 'id="' + t + '"': ""
    }
    function o(t) {
        return t ? 'data-tooltip="' + t + '"': ""
    }
    function n(t) {
        var n = t.id,
        a = t.tooltip,
        s = t.disabled,
        i = t.stateClassName,
        l = t.className,
        r = t.identity;
        return "<div " + e(n) + " " + o(a) + " " + (r ? 'identity="' + r + '"': "") + " " + (s ? "disabled": "") + ' class="hp-ui-button ' + (l || "") + " " + (i || "") + '">'
    }
    t.toolbarHtml = t.toolbarHtml || {},
    t.toolbarHtml.helper = {
        N_: function(t) {
            return t
        },
        getIdString: e,
        getTooltipString: o,
        getOuterDivStart: n,
        getToolbarButton: function(t) {
            var e = t.id,
            o = t.tooltip,
            a = t.disabled,
            s = t.stateClassName,
            i = t.iconClassName,
            c = t.className || "";
            return "\n" + n({
                id: e,
                tooltip: o,
                disabled: a,
                stateClassName: s,
                className: "toolbar-button-wrapper toolbar-inline-block "+ c
            }) + '\n    <div class="toolbar-button-outer-container toolbar-inline-block" style="user-select: none;">\n        <div class="toolbar-button-inner-container toolbar-inline-block" style="user-select: none;">\n            <div class="docx-icon toolbar-inline-block" style="user-select: none;">\n                <div class="docx-icon-img-container ' + (i || "") + '" style="user-select: none;"></div>\n            </div>\n        </div>\n    </div>\n</div>'
        },
        getToolbarMenuButton: function(t) {
            var e = t.id,
            o = t.tooltip,
            a = t.identity,
            s = t.disabled,
            i = t.stateClassName,
            l = t.iconClassName;
            return "\n" + n({
                id: e,
                tooltip: o,
                identity: a,
                disabled: s,
                stateClassName: i,
                className: "toolbar-menu-button-wrapper"
            }) + '\n    <div class="toolbar-menu-button-outer-container toolbar-inline-block" style="user-select: none;">\n        <div class="toolbar-menu-button-inner-container toolbar-inline-block" style="user-select: none;">\n            <div class="toolbar-menu-button-icon toolbar-inline-block" style="user-select: none;">\n                <div class="docx-icon toolbar-inline-block" style="user-select: none;">\n                    <div class="docx-icon-container docx-icon-img-container ' + (l || "") + '"  style="user-select: none;"></div>\n                </div>\n            </div>\n            <div class="toolbar-menu-button-dropdown toolbar-inline-block" style="user-select: none;"></div>\n        </div>\n    </div>\n</div>'
        },
        getToolbarContentMenuButton: function(t) {
            var e = t.id,
            o = t.tooltip,
            a = t.identity,
            s = t.disabled,
            i = t.stateClassName,
            l = t.iconContent,
            r = t.iconClassName;
            return "\n" + n({
                id: e,
                tooltip: o,
                identity: a,
                disabled: s,
                stateClassName: i,
                className: "toolbar-menu-button-wrapper"
            }) + '\n    <div class="toolbar-menu-button-outer-container toolbar-inline-block" style="user-select: none;">\n        <div class="toolbar-menu-button-inner-container toolbar-inline-block" style="user-select: none;">\n            <div class="toolbar-menu-button-icon toolbar-menu-button-content docx-icon-container toolbar-inline-block ' + (r || "") + '" style="user-select: none;">\n                ' + (l || "") + '\n            </div>\n            <div class="toolbar-menu-button-dropdown toolbar-inline-block" style="user-select: none;"></div>\n        </div>\n    </div>\n</div>'
        },
        getToolbarDropdownMenuButton: function(t) {
            var a = t.id,
            s = t.identity,
            i = t.disabled,
            l = t.buttonId,
            r = t.tooltip,
            d = t.menuContent,
            c = (t.dropdowTps, t.dropdownId),
            p = t.stateClassName,
            m = t.iconClassName,
            u = t.dropdownTips;
            return "\n" + n({
                id: a,
                identity: s,
                disabled: i,
                stateClassName: p,
                className: "toolbar-menu-button-wrapper toolbar-dropdown-menu-button"
            }) + '\n    <div class="toolbar-menu-button-outer-container toolbar-inline-block" style="user-select: none;">\n        <div class="toolbar-menu-button-inner-container toolbar-inline-block " style="user-select: none;">\n            <div class="toolbar-menu-button-icon toolbar-inline-block toolbar-menu-button-text-outer" style="user-select: none;" ' + e(l) + " " + o(r) + '>\n                 <div class="docx-icon toolbar-inline-block" style="user-select: none;" >\n                    <div class="docx-icon-container docx-icon-img-container ' + (m || "") + '" style="user-select: none;"  ></div>\n                </div><span class="toolbar-mini-menu-button-content-insert">' + (d || "") + '</span>\n            </div><div class="toolbar-menu-button-dropdown-outer" ' + e(c) + " " + o(u) + '><div class="toolbar-menu-button-dropdown toolbar-inline-block" style="user-select: none;" ></div>\n            </div>\n        </div>\n    </div>\n</div>'
        },
        getDropdown: function() {
            return '\n<script id="template-dropdown-wrapper" type="text/html">\n    <div id="dropdown-wrapper" class="dropdown-wrapper common-web-popup-boxshadow"></div>\n<\/script>\n\n<script id="template-dropdown-image-content-row" type="text/html">\n    <li class="li-dropdown-ic li-dropdown">\n        <div class="dropdown-ic-img-wrapper dropdown-img-wrapper">\n            <div class="dropdown-ic-img dropdown-img"></div>\n        </div>\n        <div class="dropdown-ic-content-wrapper dropdown-content-wrapper">\n            <div class="dropdown-ic-content dropdown-content"></div>\n        </div>\n    </li>\n<\/script>\n\n<script id="template-dropdown-image-row" type="text/html">\n    <li class="li-dropdown-i li-dropdown">\n        <div class="dropdown-i-img-wrapper dropdown-img-wrapper">\n            <div class="dropdown-i-img dropdown-img"></div>\n        </div>\n    </li>\n<\/script>\n\n<script id="template-dropdown-content-row" type="text/html">\n    <li class="li-dropdown-c li-dropdown">\n        <div class="dropdown-c-content-wrapper dropdown-content-wrapper">\n            <div class="dropdown-c-content dropdown-content"></div>\n        </div>\n    </li>\n<\/script>\n\n<script id="template-dropdown-celltype-row" type="text/html">\n    <li class="li-dropdown-celltype li-dropdown">\n        <div class="dropdown-celltype-wrapper">\n            <div class="dropdown-celltype-title"></div>\n            <div class="dropdown-celltype-detail"></div>\n        </div>\n    </li>\n<\/script>\n\n<script id="template-dropdown-separator-row" type="text/html">\n    <div class="dropdown-separator-wrapper">\n        <div class="dropdown-separator"></div>\n    </div>\n<\/script>';
        }
    };
} (window),
function(t) {
    function e(t) {
        return t ? 'id="' + t + '"' : ""
    }
    function n(t) {
        return t ? 'data-tooltip="' + t + '"' : ""
    }
    function a(t) {
        var a = t.id
          , o = t.tooltip
          , i = t.disabled
          , s = t.stateClassName
          , l = t.className
          , r = t.identity;
        return "<div " + e(a) + " " + n(o) + " " + (r ? 'identity="' + r + '"' : "") + " " + (i ? "disabled" : "") + ' class="hp-ui-button ' + (l || "") + " " + (s || "") + '">'
    }
    t.toolbarHtml = t.toolbarHtml || {},
    t.toolbarHtml.helper = {
        N_: function(t) {
            return t
        },
        getIdString: e,
        getTooltipString: n,
        getOuterDivStart: a,
        getToolbarButton: function(t) {
            var e = t.id
              , n = t.tooltip
              , o = t.disabled
              , i = t.stateClassName
              , s = t.iconClassName;
            return "\n" + a({
                id: e,
                tooltip: n,
                disabled: o,
                stateClassName: i,
                className: "toolbar-button-wrapper toolbar-inline-block"
            }) + '\n    <div class="toolbar-button-outer-container toolbar-inline-block" style="user-select: none;">\n        <div class="toolbar-button-inner-container toolbar-inline-block" style="user-select: none;">\n            <div class="docx-icon toolbar-inline-block" style="user-select: none;">\n                <div class="docx-icon-img-container ' + (s || "") + '" style="user-select: none;"></div>\n            </div>\n        </div>\n    </div>\n</div>'
        },
        getToolbarMenuButton: function(t) {
            var e = t.id
              , n = t.tooltip
              , o = t.identity
              , i = t.disabled
              , s = t.stateClassName
              , l = t.iconClassName;
            return "\n" + a({
                id: e,
                tooltip: n,
                identity: o,
                disabled: i,
                stateClassName: s,
                className: "toolbar-menu-button-wrapper"
            }) + '\n    <div class="toolbar-menu-button-outer-container toolbar-inline-block" style="user-select: none;">\n        <div class="toolbar-menu-button-inner-container toolbar-inline-block" style="user-select: none;">\n            <div class="toolbar-menu-button-icon toolbar-inline-block" style="user-select: none;">\n                <div class="docx-icon toolbar-inline-block" style="user-select: none;">\n                    <div class="docx-icon-container docx-icon-img-container ' + (l || "") + '"  style="user-select: none;"></div>\n                </div>\n            </div>\n            <div class="toolbar-menu-button-dropdown toolbar-inline-block" style="user-select: none;"></div>\n        </div>\n    </div>\n</div>'
        },
        getToolbarContentMenuButton: function(t) {
            var e = t.id
              , n = t.tooltip
              , o = t.identity
              , i = t.disabled
              , s = t.stateClassName
              , l = t.iconContent
              , r = t.iconClassName;
            return "\n" + a({
                id: e,
                tooltip: n,
                identity: o,
                disabled: i,
                stateClassName: s,
                className: "toolbar-menu-button-wrapper"
            }) + '\n    <div class="toolbar-menu-button-outer-container toolbar-inline-block" style="user-select: none;">\n        <div class="toolbar-menu-button-inner-container toolbar-inline-block" style="user-select: none;">\n            <div class="toolbar-menu-button-icon toolbar-menu-button-content docx-icon-container toolbar-inline-block ' + (r || "") + '" style="user-select: none;">\n                ' + (l || "") + '\n            </div>\n            <div class="toolbar-menu-button-dropdown toolbar-inline-block" style="user-select: none;"></div>\n        </div>\n    </div>\n</div>'
        },
        getToolbarDropdownMenuButton: function(t) {
            var o = t.id
              , i = t.identity
              , s = t.disabled
              , l = t.buttonId
              , r = t.tooltip
              , c = t.menuContent
              , d = (t.dropdowTps,
            t.dropdownId)
              , p = t.stateClassName
              , m = t.iconClassName
              , u = t.dropdownTips;
            return "\n" + a({
                id: o,
                identity: i,
                disabled: s,
                stateClassName: p,
                className: "toolbar-menu-button-wrapper toolbar-dropdown-menu-button"
            }) + '\n    <div class="toolbar-menu-button-outer-container toolbar-inline-block" style="user-select: none;">\n        <div class="toolbar-menu-button-inner-container toolbar-inline-block " style="user-select: none;">\n            <div class="toolbar-menu-button-icon toolbar-inline-block toolbar-menu-button-text-outer" style="user-select: none;" ' + e(l) + " " + n(r) + '>\n                 <div class="docx-icon toolbar-inline-block" style="user-select: none;" >\n                    <div class="docx-icon-container docx-icon-img-container ' + (m || "") + '" style="user-select: none;"  ></div>\n                </div><span class="toolbar-mini-menu-button-content-insert">' + (c || "") + '</span>\n            </div><div class="toolbar-menu-button-dropdown-outer" ' + e(d) + " " + n(u) + '><div class="toolbar-menu-button-dropdown toolbar-inline-block" style="user-select: none;" ></div>\n            </div>\n        </div>\n    </div>\n</div>'
        },
        getDropdown: function() {
            return '\n<script id="template-dropdown-wrapper" type="text/html">\n    <div id="dropdown-wrapper" class="dropdown-wrapper common-web-popup-boxshadow"></div>\n<\/script>\n\n<script id="template-dropdown-image-content-row" type="text/html">\n    <li class="li-dropdown-ic li-dropdown">\n        <div class="dropdown-ic-img-wrapper dropdown-img-wrapper">\n            <div class="dropdown-ic-img dropdown-img"></div>\n        </div>\n        <div class="dropdown-ic-content-wrapper dropdown-content-wrapper">\n            <div class="dropdown-ic-content dropdown-content"></div>\n        </div>\n    </li>\n<\/script>\n\n<script id="template-dropdown-image-row" type="text/html">\n    <li class="li-dropdown-i li-dropdown">\n        <div class="dropdown-i-img-wrapper dropdown-img-wrapper">\n            <div class="dropdown-i-img dropdown-img"></div>\n        </div>\n    </li>\n<\/script>\n\n<script id="template-dropdown-content-row" type="text/html">\n    <li class="li-dropdown-c li-dropdown">\n        <div class="dropdown-c-content-wrapper dropdown-content-wrapper">\n            <div class="dropdown-c-content dropdown-content"></div>\n        </div>\n    </li>\n<\/script>\n\n<script id="template-dropdown-content-separator-row" type="text/html">\n    <li class="li-dropdown-c li-dropdown li-dropdown-separator">\n        <div class="dropdown-separator-wrapper">\n            <div class="dropdown-separator"></div>\n        </div>\n        <div class="dropdown-c-content-wrapper dropdown-content-wrapper">\n            <div class="dropdown-c-content dropdown-content"></div>\n        </div>\n    </li>\n<\/script>\n\n<script id="template-dropdown-celltype-row" type="text/html">\n    <li class="li-dropdown-celltype li-dropdown">\n        <div class="dropdown-celltype-wrapper">\n            <div class="dropdown-celltype-title"></div>\n            <div class="dropdown-celltype-detail"></div>\n        </div>\n    </li>\n<\/script>\n\n<script id="template-dropdown-separator-row" type="text/html">\n    <div class="dropdown-separator-wrapper">\n        <div class="dropdown-separator"></div>\n    </div>\n<\/script>'
        }
    }
}(window),
function(t) {
    if (!t.toolbarHtml || !t.toolbarHtml.helper) throw new Error("绘制 toolbar 前必须先初始化 toobalHtml 和 相应 helper， 请检查是否有进行相应初始化。");
    var o = t.toolbarHtml.helper,
    n = o.N_,
    a = o.getToolbarButton,
    s = o.getToolbarContentMenuButton,
    i = o.getToolbarMenuButton,
    l = o.getToolbarDropdownMenuButton;
    t.toolbarHtml.getToolbarInnerHtml = function() {
        return '\n<span id="toolbar" class="toolbar-linespace-clear">\n<span class="hp-ui-button-group" data-type="sheet-undoredo">\n    '+ a({
            id: "sheet-undobutton",
            stateClassName: "toolbar-button-wrapper-disabled",
            iconClassName: "docx-icon-common toolbar-icon-undo",
            tooltip: n("撤销(Ctrl+Z)")
        }) + "\n    " + a({
            id: "sheet-redobutton",
            stateClassName: "toolbar-button-wrapper-disabled",
            iconClassName: "docx-icon-common toolbar-icon-redo",
            tooltip: n("重做(Ctrl+Y)")
        }) + "\n    " + a({
            id: "sheet-savebutton",
            stateClassName: "toolbar-button-wrapper-disabled",
            iconClassName: "docx-icon-common toolbar-icon-save",
            tooltip: n("保存 (Ctrl+S)")
        }) + "\n    " + a({
            id: "sheet-format-painter",
            stateClassName: "toolbar-button-wrapper-disabled hide",
            iconClassName: "docx-icon-common toolbar-icon-formatpainter",
            tooltip: n("格式刷，双击无限刷")
        }) + "\n    " + a({
            id: "sheet-clear-format",
            stateClassName: "toolbar-button-wrapper-disabled",
            iconClassName: "docx-icon-common toolbar-icon-clearformat",
            tooltip: n("清除格式")
        }) + '\n    </span><span class="hp-ui-button-group vertical-separator">\n    </span><span id="toolbar-sheet-font--group" class="hp-ui-button-group" data-type="sheet-format-shotcuts">\n    <span id="toolbar-sheet-format-group" class="toolbar-inline-block">\n        ' + s({
            id: "sheet-format",
            identity: "sheet-format-default",
            iconContent: "常规",
            stateClassName: "toolbar-button-wrapper-disabled",
            iconClassName: "toolbar-menu-button-content-format",
            tooltip: n("格式")
        }) + "\n    </span>\n    " + a({
            id: "sheet-formatcurrencychsbutton",
            stateClassName: "toolbar-button-wrapper-disabled hide top-5px",
            iconClassName: "docx-icon-common toolbar-icon-currency-chs",
            tooltip: n("人民币格式")
        }) + "\n\n    " + a({
            id: "sheet-formatpercentagebutton",
            stateClassName: "toolbar-button-wrapper-disabled hide top-5px",
            iconClassName: "docx-icon-common toolbar-icon-percentage",
            tooltip: n("百分比格式")
        }) + "\n\n    " + a({
            id: "sheet-decimaldecreasebutton",
            stateClassName: "toolbar-button-wrapper-disabled top-5px",
            iconClassName: "docx-icon-common toolbar-icon-decimal-decrease",
            tooltip: n("减少小数位数")
        }) + "\n\n    " + a({
            id: "sheet-decimalincreasebutton",
            stateClassName: "toolbar-button-wrapper-disabled top-5px",
            iconClassName: "docx-icon-common toolbar-icon-decimal-increase",
            tooltip: n("增加小数位数")
        }) + '\n</span><span class="hp-ui-button-group vertical-separator vertical-separator-sepcific">\n</span><span id="toolbar-sheet-font--group" class="hp-ui-button-group" data-type="sheet-align">\n    <span id="toolbar-sheet-font-family-group" class="toolbar-inline-block">\n        ' + s({
            id: "sheet-font-family",
            identity: "sheet-font-family-Microsoft YaHei",
            iconContent: "微软雅黑",
            stateClassName: "toolbar-button-wrapper-disabled hide",
            iconClassName: "toolbar-menu-button-content-font-family",
            tooltip: n("字体")
        }) + '\n    </span>\n    <span id="toolbar-sheet-font-size-group" class="toolbar-inline-block">\n        ' + s({
            id: "sheet-font-size",
            identity: "font-size-10",
            iconContent: "10",
            stateClassName: "toolbar-button-wrapper-disabled",
            iconClassName: "toolbar-menu-button-content-font-size",
            tooltip: n("字号")
        }) + '\n     </span>\n</span><span class="hp-ui-button-group vertical-separator">\n</span><span id="toolbar-sheet-group" class="hp-ui-button-group" data-type="sheet-format">\n    ' + a({
            id: "sheet-boldbutton",
            stateClassName: "toolbar-button-wrapper-disabled",
            iconClassName: "docx-icon-common toolbar-icon-bold",
            tooltip: n("加粗")
        }) + "\n    " + a({
            id: "sheet-italicsbutton",
            stateClassName: "toolbar-button-wrapper-disabled hide",
            iconClassName: "docx-icon-common toolbar-icon-italic",
            tooltip: n("倾斜")
        }) + "\n    " + a({
            id: "sheet-underlinebutton",
            stateClassName: "toolbar-button-wrapper-disabled hide",
            iconClassName: "docx-icon-common toolbar-icon-underline",
            tooltip: n("下划线")
        }) + "\n    " + a({
            id: "sheet-strikebutton",
            stateClassName: "toolbar-button-wrapper-disabled hide",
            iconClassName: "docx-icon-common toolbar-icon-strikethrough",
            tooltip: n("删除线 (Ctrl+D)")
        }) + '\n    <span id="toolbar-sheet-font-color-group" class="toolbar-inline-block toolbar-font-color toolbar-button-wrapper-disabled">\n        <input id="sheet-fontcolor-button" type="text" name="sheet-fontcolor-button" value="#FFFFFF" style="display: none"/>\n    </span>\n</span><span class="hp-ui-button-group vertical-separator">\n</span><span id="toolbar-sheet-align--group" class="hp-ui-button-group" data-type="sheet-align">\n    <span id="toolbar-sheet-paint-brush-color-group"  class="toolbar-inline-block toolbar-font-color toolbar-button-wrapper-disabled">\n        <input id="sheet-paint-brush-color-button" type="text" name="sheet-paint-brush-color-button" value="#FFFFFF" style="display: none"/>\n    </span>\n    <span id="toolbar-sheet-border-group" class="toolbar-inline-block">\n        ' + i({
            id: "sheet-border",
            identity: "sheet-left-align",
            stateClassName: "toolbar-button-wrapper-disabled",
            iconClassName: "docx-icon-common toolbar-icon-sheet-border-all",
            tooltip: n("边框")
        }) + '\n    </span>\n    <span id="toolbar-sheet-mergecell-group" class="toolbar-inline-block">\n        ' + a({
            id: "sheet-merge-cell",
            stateClassName: "toolbar-button-wrapper-disabled top-5px",
            iconClassName: "docx-icon-common toolbar-icon-merge-cell",
            tooltip: n("合并单元格")
        }) + '\n    </span>\n</span>\n<span class="hp-ui-button-group vertical-separator">\n</span><span id="toolbar-sheet-opr--group" class="hp-ui-button-group" data-type="sheet-align">\n    <span id="toolbar-sheet-horizontal-align-group" class="toolbar-inline-block">\n        ' + i({
            id: "sheet-horizontal-align",
            identity: "sheet-left-align",
            stateClassName: "toolbar-button-wrapper-disabled",
            iconClassName: "docx-icon-common toolbar-icon-sheet-left-align",
            tooltip: n("对齐")
        }) + '\n    </span>\n    <span id="toolbar-sheet-vertical-align-group" class="toolbar-inline-block">\n        ' + i({
            id: "sheet-vertical-align",
            identity: "sheet-top-align",
            stateClassName: "toolbar-button-wrapper-disabled",
            iconClassName: "docx-icon-common toolbar-icon-sheet-middle-align",
            tooltip: n("垂直对齐")
        }) + '\n    </span>\n    <span id="toolbar-sheet-textwrapbutton-group" class="toolbar-inline-block">\n        ' + a({
            id: "sheet-textwrapbutton",
            stateClassName: "toolbar-button-wrapper-disabled top-5px",
            iconClassName: "docx-icon-common toolbar-icon-textwrap",
            tooltip: n("自动换行")
        }) + '\n    </span>\n</span><span class="hp-ui-button-group vertical-separator">\n</span><span id="toolbar-sheet-cell--group" class="hp-ui-button-group" data-type="sheet-align">\n    <span id="toolbar-sheet-calculate-group" class="toolbar-inline-block">\n        ' + l({
            id: "sheet-calculate",
            buttonId: "sheet-calculate-button",
            dropdownId: "sheet-calculate-more",
            menuContent: "自动求和",
            identity: "sheet-sum-calculate",
            stateClassName: "toolbar-button-wrapper-disabled hide",
            iconClassName: "docx-icon-common toolbar-icon-formula",
            tooltip: n("自动计算框选区域的和"),
            dropdownTips: n("插入函数")
        }) + '\n    </span>\n    <span id="toolbar-sheet-insert-group" class="toolbar-inline-block">\n        ' + a({
            id: "sheet-insert-link",
            stateClassName: "toolbar-button-wrapper-disabled",
            iconClassName: "docx-icon-common toolbar-icon-insert-link sheet-insertling-style",
            tooltip: n("数据关联")
        }) + '\n    </span>\n</span><span class="hp-ui-button-group vertical-separator hide"></span>\n<span class="hp-ui-button-group" data-type="insert">\n   <span id="toolbar-search-group" class="toolbar-inline-block">\n ' + a({
            id: "sheet-search",
            stateClassName: "toolbar-button-wrapper-disabled hide",
            iconClassName: "docx-icon-common toolbar-search sheet-insertling-style",
            tooltip: n("查找替换")
        }) + '\n    </span>\n</span>\n<span class="hp-ui-button-group vertical-separator before-more-separator" style="display:none;"></span><div id="toobarMoreButton" class="hp-ui-button-group" style="display:none;">\n    <div class="hp-ui-button toolbar-menu-button-wrapper">\n        <div class="toolbar-menu-button-outer-container toolbar-inline-block" style="user-select: none;">\n            <div class="toolbar-menu-button-inner-container toolbar-inline-block" style="user-select: none;">\n                <div class="toolbar-menu-button-more toolbar-inline-block" style="user-select: none;">更多</div>\n                <div class="toolbar-menu-button-dropdown toolbar-inline-block" style="user-select: none;"></div>\n            </div>\n        </div>\n    </div>\n</div>\n</span>\n'
    }
} (window),
function(t) {
    var o = t.toolbarHtml;
    if (!o) throw new Error("必须要先初始化 toobalHTML");
    var n, a,
    i = t.$;
    function l() {
        var t = i("#my").height(),
        o = i("#web_header_top").height(),
        n = i("#web_header_bottom").height(),
        a = i("#inputboxwithpc").height();
        i("#fold-gap").css({
            left: "15px"
        }),
        i("header,#padpage").css({
            transform: "translate3d(0px,-" + o + "px,0px)"
        }),
        i("#foldIcon").removeClass("icon-unfold").addClass("icon-fold");
        i("#moreToolsView").css({
            display: "none"
        }),
        i("#foldIcon").attr("data-tooltip", "退出全屏模式（Ctrl+F1）"),
        i("#my").length && i("#my").height(t + o),
        window.padutils.tooltip("#foldIcon"),
        window.padeditor && (window.padeditor.isBannerShow = !1)
    }
    function r() {
        var t = i("#my").height(),
        o = i("#web_header_top").height(),
        n = i("#web_header_bottom").height(),
        a = i("#inputboxwithpc").height();
        i("#fold-gap").css({
            left: "51px"
        }),
        i("header,#padpage,#inputboxwithpc").css({
            transform: "translate3d(0px,0px,0px)"
        }),
        i("#foldIcon").removeClass("icon-fold").addClass("icon-unfold"),
        i("#moreToolsView").css({
            display: "none"
        }),
        i("#foldIcon").attr("data-tooltip", "全屏模式（Ctrl+F1）"),
        i("#my").length && i("#my").height(t - o),
        window.padutils.tooltip("#foldIcon")
    }
    i("#web_header_bottom").append((n = i('<div id="foldBtn" class="hide">').css({
        position: "absolute",
        width: "100px",
        height: "28px",
        right: "18px",
        top: "6px",
        background: "transparent",
        opacity: 1
    }), a = i('<div id="foldIcon" data-tooltip="全屏模式（Ctrl+F1）" class="icon-unfold">').css({
        width: "29px",
        height: "28px",
        position: "absolute",
        right: "0px",
        top: "0"
    }), i('<div id="fold-gap" class="hp-ui-button-group vertical-separator">').css({
        position: "absolute",
        left: "51px",
        top: "2px",
        "margin-top": "0px"
    }).appendTo(n), a.appendTo(n), i("#web_header_bottom").css({
        position: "relative"
    }), i("#padpage,#inputboxwithpc").css({
        transition: "transform 0.2s"
    }), i("html").css({
        "background-color": "#f5f6f7"
    }), n)),
    i("#foldIcon").click(function() {
        i(this).hasClass("hidefold") ? r() : l(),
        i(this).toggleClass("hidefold");
    }),
    i("#web_header_bottom").append(o.getToolbarInnerHtml()),
    i("#web_header_bottom").append(o.helper.getDropdown()),
    window.onhelp = function() {
        return ! 1
    },
    document.addEventListener("keydown", function(t) {
        var e = (t = t || window.event).type,
        o = (t.charCode, t.keyCode);
        if ("keydown" == e && 112 === o ) return setTimeout(function() {
            i("#foldIcon").hasClass("hidefold") ? r() : l(),
            i("#foldIcon").toggleClass("hidefold")
        },
        1),
        t && t.preventDefault && t.preventDefault(),
        window.event && (window.event.returnValue = !1), !1;
    });
} (window); !
function() {
    var t = []; 
    window._formulaCategory = [{"name":"日期与时间","key":"date_time","values":["DATE","DATEDIF","DAY","MONTH","NOW","TODAY","WEEKDAY","YEAR"]},{"name":"数学与三角函数","key":"math","values":["ABS","INT","MOD","RAND","ROUND","ROUNDUP","SQRT","SUBTOTAL","SUM","SUMIF","SUMIFS","SUMPRODUCT"]},{"name":"统计","key":"count","values":["AVERAGE","COUNT","MAX","MIN","COUNTA","COUNTIF","COUNTIFS"]},{"name":"查找与引用","key":"find","values":["LOOKUP","INDEX","INDIRECT","MATCH","ROW","VLOOKUP"]},{"name":"文本","key":"txt","values":["FIND","LEFT","LEN","MID","RIGHT","TEXT"]},{"name":"逻辑","key":"logic","values":["AND","IF","IFERROR","OR"]},{"name":"信息","key":"information","values":["ISERROR","ISNA","ISNUMBER"]},{"name":"兼容性","key":"adapt","values":["RANK"]},{"name":"Web","key":"web","values":["STOCK"]}];
    window._formulaConfig = {
        sum: {
            name: "求参数的和",
            abbr: "求和",
            brief: "求参数的和",
            title: "SUM(*)",
            titleParams: [{
                name: "数值1",
                subItemIndex: 1
            }, {
                name: "[数值2,...]",
                subItemIndex: 1
            }],
            subItem: [{
                title: "示例",
                detail: "SUM(4,18)"
            }, {
                title: "函数说明",
                detail: "可以将单个值、单元格引用或是区域相加，或者将三者的组合相加。"
            }, {
                title: "数值1",
                detail: "必需。要相加的第一个数字。"
            }, {
                title: "[数值2]",
                detail: "可选。要相加的第二个数字。可按照此方式最多指定255个数字。"
            }],
            repeatableParameter: "{1}*"
        }
    };
    (window._formulaCategory || []).forEach(function(e) {
        t.push('<li data-formula-type="' + e.key + '" class="formula-item">' + e.name + "<i></i></li>")
    });
    var e = '<div id="formula-editor-container">\n        <div class="formula-editor-title-container">\n            <div class="title">函数</div>\n        </div>\n        <div class="formula-editor">\n            <div class="formula-editor-search-container">\n                <input type="text" id="formula-editor-search-input" placeholder="请输入函数搜索" />\n                <span class="formula-editor-search-close"></span>\n            </div>\n            <div class="formula-editor-tab-container">\n                <div class="formula-editor-tab">\n                    <div class="formula-editor-select formula-editor-table" style="display: none;">\n                        <div class="formula-part left">\n                            <div class="tab-select">\n                                <ul>\n                                    <li class="all formula-item">全部<i></i></li>\n                                    <li class="recently formula-item">最近使用<i></i></li>\n                                </ul>\n                            </div>\n                            <div class="line"></div>\n                            <div class="tab-type">\n                                <ul>' + t.join("") + '</ul>\n                            </div>\n                        </div>\n                        <div class="formula-part right">\n                            <ul class="formula-editor-list"></ul>\n                            <div class="line"></div>\n                        </div>\n                    </div>\n                    <div class="formula-editor-search formula-editor-table" style="display: none;">\n                        <ul class="formula-editor-list"></ul>\n                    </div>\n                </div>\n            </div>\n            <div class="formula-editor-button">\n                <button id="formula-editor-inject-button" class="pad-ui-btn pad-ui-btn-normal pad-ui-btn-blue">插入函数</button>\n            </div>\n            <div id="formula-editor-line" class="formula-editor-line"></div>\n            <div class="formula-editor-detail-container"></div>\n        </div>\n    </div>\n    <div id="formula-editor-close"></div>';
    window.formulaEditorHTML = e
} ();
!function(){
    window.linkLineTpl = '<td><span class="xuhao"></span></td><td><a href="javascript:void(0);" class="cell"></a></td><td><span class="fieldName"></span></td><td style="text-align:left;white-space: pre-wrap;word-break:break-all;"><span class="fieldCode"></span></td>'
    var e = '<div id="link-viewer-container">\n    <div class="formula-editor-title-container">\n        <div class="title">表单配置信息</div>\n    </div>\n    <div class="formula-editor">\n                    <div>\n                <div class="from-group">\n                    <label>表单类型:</label>\n                    <label>\n                        <input type="radio" name="fileType" value="word"> Word\n                    </label>\n                    <label>\n                        <input type="radio" name="fileType" value="excel" checked=""> Excel\n                    </label>\n                </div>\n                <div class="from-group">\n                    <label>数据起始行:\n                        <input type="text" readonly size="4" name="lineNum" value="">\n                    </label>\n                    <label>数据行行数:\n                        <input type="text" readonly size="4" name="lineCount" value="">\n                    </label>\n                </div>\n                <button class="btn btn-primary btn-xs" type="button" id="setLineInfo">设置行数据信息</button>\n            </div>\n            <table style="margin-top:20px;" class="switch-rules-head table table-bordered table-condensed width-limit-xl center">\n                <colgroup><col style="width: 45px;" /><col style="width: 60px;" /><col style="width: 77px;" /><col style="width: 77px;" /></colgroup><thead>\n                    <tr>\n                        <th nowrap data-field="xuhao">序号</th>\n                        <th nowrap data-field="cell">单元格</th>\n                        <th nowrap data-field="fieldName">字段名称</th>\n                        <th nowrap data-field="fieldCode">字段标识</th>\n                    </tr>\n                </thead>\n</table><div id="link-viewer-table" style="min-height:300px;overflow-y:auto;margin-top:p;padding-top:0;position:relative;top:-21px"><table class="switch-rules table table-bordered table-condensed width-limit-xl center"><colgroup><col style="width: 45px;" /><col style="width: 60px;" /><col style="width: 77px;" /><col style="width: 77px;" /></colgroup><tbody>\n                    <!--  -->\n                </tbody>\n            </table>\n        </div>\n    </div>\n</div>\n<div id="link-viewer-close"></div>';
    window.linkViewerHTML = e;
}();
/**
 * Layui图标选择器
 * @author wujiawei0926@yeah.net
 *
 */

layui.define(['laypage', 'form'], function (exports) {
    "use strict";

    var IconPicker =function () {
        this.v = '0.1.beta';
    }, _MOD = 'iconPicker',
        _this = this,
        $ = layui.jquery,
        laypage = layui.laypage,
        form = layui.form,
        BODY = 'body',
        TIPS = '请选择图标';

    /**
     * 渲染组件
     */
    IconPicker.prototype.render = function(options){
        var opts = options,
            // DOM选择器
            elem = opts.elem,
            // 数据类型：fontClass/unicode
            type = opts.type == null ? 'fontClass' : opts.type,
            // 是否分页：true/false
            page = opts.page,
            // 每页显示数量
            limit = opts.limit == null ? 12 : opts.limit,
            // 是否开启搜索：true/false
            search = opts.search == null ? true : opts.search,
            // 每个图标格子的宽度：'43px'或'20%'
            cellWidth = opts.cellWidth,
            // 点击回调
            click = opts.click,
            // 渲染成功后的回调
            success = opts.success,
            // json数据
            data = {},
            // 唯一标识
            tmp = new Date().getTime(),
            // 是否使用的class数据
            isFontClass = opts.type === 'fontClass',
            // 初始化时input的值
            ORIGINAL_ELEM_VALUE = $(elem).val(),
            TITLE = 'layui-select-title',
            TITLE_ID = 'layui-select-title-' + tmp,
            ICON_BODY = 'layui-iconpicker-' + tmp,
            PICKER_BODY = 'layui-iconpicker-body-' + tmp,
            PAGE_ID = 'layui-iconpicker-page-' + tmp,
            LIST_BOX = 'layui-iconpicker-list-box',
            selected = 'layui-form-selected',
            unselect = 'layui-unselect';

        var a = {
            init: function () {
                data = common.getData[type]();

                a.hideElem().createSelect().createBody().toggleSelect();
                a.preventEvent().inputListen();
                common.loadCss();
                
                if (success) {
                    success(this.successHandle());
                }

                return a;
            },
            successHandle: function(){
                var d = {
                    options: opts,
                    data: data,
                    id: tmp,
                    elem: $('#' + ICON_BODY)
                };
                return d;
            },
            /**
             * 隐藏elem
             */
            hideElem: function () {
                $(elem).hide();
                return a;
            },
            /**
             * 绘制select下拉选择框
             */
            createSelect: function () {
                var oriIcon = '<i class="layui-icon">';
                
                // 默认图标
                if(ORIGINAL_ELEM_VALUE === '') {
                    if(isFontClass) {
                        ORIGINAL_ELEM_VALUE = 'layui-icon-circle-dot';
                    } else {
                        switch(opts.type) {
                            case 'fontAwesome':
                                ORIGINAL_ELEM_VALUE = 'fa fa-circle-o';
                                break;
                            case 'Glyphicons':
                                ORIGINAL_ELEM_VALUE = 'glyphicon glyphicon-asterisk';
                                break;
                            default:
                                ORIGINAL_ELEM_VALUE = '&#xe617;';
                                break;
                        }
                    }
                }

                if (isFontClass) {
                    oriIcon = '<i class="layui-icon '+ ORIGINAL_ELEM_VALUE +'">';
                } else {
                    switch(opts.type) {
                        case 'fontAwesome':
                            oriIcon = '<i class="'+ ORIGINAL_ELEM_VALUE +'">';
                            break;
                        case 'Glyphicons':
                            oriIcon = '<i class="'+ ORIGINAL_ELEM_VALUE +'">';
                            break;
                        default:
                            oriIcon += ORIGINAL_ELEM_VALUE; 
                            break;
                    }
                }
                oriIcon += '</i>';

                var selectHtml = '<div class="layui-iconpicker layui-unselect layui-form-select" id="'+ ICON_BODY +'">' +
                    '<div class="'+ TITLE +'" id="'+ TITLE_ID +'">' +
                        '<div class="layui-iconpicker-item">'+
                            '<span class="layui-iconpicker-icon layui-unselect">' +
                                oriIcon +
                            '</span>'+
                            '<i class="layui-edge"></i>' +
                        '</div>'+
                    '</div>' +
                    '<div class="layui-anim layui-anim-upbit" style="">' +
                        '123' +
                    '</div>';
                $(elem).after(selectHtml);
                return a;
            },
            /**
             * 展开/折叠下拉框
             */
            toggleSelect: function () {
                var item = '#' + TITLE_ID + ' .layui-iconpicker-item,#' + TITLE_ID + ' .layui-iconpicker-item .layui-edge';
                a.event('click', item, function (e) {
                    var $icon = $('#' + ICON_BODY);
                    if ($icon.hasClass(selected)) {
                        $icon.removeClass(selected).addClass(unselect);
                    } else {
                        // 隐藏其他picker
                        $('.layui-form-select').removeClass(selected);
                        // 显示当前picker
                        $icon.addClass(selected).removeClass(unselect);
                    }
                    e.stopPropagation();
                });
                return a;
            },
            /**
             * 绘制主体部分
             */
            createBody: function () {
                // 获取数据
                var searchHtml = '';

                if (search) {
                    searchHtml = '<div class="layui-iconpicker-search">' +
                        '<input class="layui-input">' +
                        '<i class="layui-icon">&#xe615;</i>' +
                        '</div>';
                }

                // 组合dom
                var bodyHtml = '<div class="layui-iconpicker-body" id="'+ PICKER_BODY +'">' +
                    searchHtml +
                        '<div class="'+ LIST_BOX +'"></div> '+
                     '</div>';
                $('#' + ICON_BODY).find('.layui-anim').eq(0).html(bodyHtml);
                a.search().createList().check().page();

                return a;
            },
            /**
             * 绘制图标列表
             * @param text 模糊查询关键字
             * @returns {string}
             */
            createList: function (text) {
                var d = data,
                    l = d.length,
                    pageHtml = '',
                    listHtml = $('<div class="layui-iconpicker-list">')//'<div class="layui-iconpicker-list">';

                // 计算分页数据
                var _limit = limit, // 每页显示数量
                    _pages = l % _limit === 0 ? l / _limit : parseInt(l / _limit + 1), // 总计多少页
                    _id = PAGE_ID;

                // 图标列表
                var icons = [];

                for (var i = 0; i < l; i++) {
                    var obj = d[i];

                    // 判断是否模糊查询
                    if (text && obj.indexOf(text) === -1) {
                        continue;
                    }

                    // 是否自定义格子宽度
                    var style = '';
                    if (cellWidth !== null) {
                        style += ' style="width:' + cellWidth + '"';
                    }

                    // 每个图标dom
                    var icon = '<div class="layui-iconpicker-icon-item" title="'+ obj +'" '+ style +'>';
                    if (isFontClass){
                        icon += '<i class="layui-icon '+ obj +'"></i>';
                    } else {
                        switch(opts.type) {
                            case 'fontAwesome':
                                icon += '<i class="'+ obj +'"></i>';
                                break;
                            case 'Glyphicons':
                                icon += '<i class="'+ obj +'"></i>';
                                break;
                            default:
                                icon += '<i class="layui-icon">'+ obj.replace('amp;', '') +'</i>';
                                break;
                        }
                    }
                    icon += '</div>';

                    icons.push(icon);
                }

                // 查询出图标后再分页
                l = icons.length;
                _pages = l % _limit === 0 ? l / _limit : parseInt(l / _limit + 1);
                for (var i = 0; i < _pages; i++) {
                    // 按limit分块
                    var lm = $('<div class="layui-iconpicker-icon-limit" id="layui-iconpicker-icon-limit-' + tmp + (i+1) +'">');

                    for (var j = i * _limit; j < (i+1) * _limit && j < l; j++) {
                        lm.append(icons[j]);
                    }

                    listHtml.append(lm);
                }

                // 无数据
                if (l === 0) {
                    listHtml.append('<p class="layui-iconpicker-tips">无数据</p>');
                }

                // 判断是否分页
                if (page){
                    $('#' + PICKER_BODY).addClass('layui-iconpicker-body-page');
                    pageHtml = '<div class="layui-iconpicker-page" id="'+ PAGE_ID +'">' +
                        '<div class="layui-iconpicker-page-count">' +
                        '<span id="'+ PAGE_ID +'-current">1</span>/' +
                        '<span id="'+ PAGE_ID +'-pages">'+ _pages +'</span>' +
                        ' (<span id="'+ PAGE_ID +'-length">'+ l +'</span>)' +
                        '</div>' +
                        '<div class="layui-iconpicker-page-operate">' +
                        '<i class="layui-icon" id="'+ PAGE_ID +'-prev" data-index="0" prev>&#xe603;</i> ' +
                        '<i class="layui-icon" id="'+ PAGE_ID +'-next" data-index="2" next>&#xe602;</i> ' +
                        '</div>' +
                        '</div>';
                }


                $('#' + ICON_BODY).find('.layui-anim').find('.' + LIST_BOX).html('').append(listHtml).append(pageHtml);
                return a;
            },
            // 阻止Layui的一些默认事件
            preventEvent: function() {
                var item = '#' + ICON_BODY + ' .layui-anim';
                a.event('click', item, function (e) {
                    e.stopPropagation();
                });
                return a;
            },
            // 分页
            page: function () {
                var icon = '#' + PAGE_ID + ' .layui-iconpicker-page-operate .layui-icon';

                $(icon).unbind('click');
                a.event('click', icon, function (e) {
                   var elem = e.currentTarget,
                       total = parseInt($('#' +PAGE_ID + '-pages').html()),
                       isPrev = $(elem).attr('prev') !== undefined,
                       // 按钮上标的页码
                       index = parseInt($(elem).attr('data-index')),
                       $cur = $('#' +PAGE_ID + '-current'),
                       // 点击时正在显示的页码
                       current = parseInt($cur.html());

                    // 分页数据
                    if (isPrev && current > 1) {
                        current=current-1;
                        $(icon + '[prev]').attr('data-index', current);
                    } else if (!isPrev && current < total){
                        current=current+1;
                        $(icon + '[next]').attr('data-index', current);
                    }
                    $cur.html(current);

                    // 图标数据
                    $('#'+ ICON_BODY + ' .layui-iconpicker-icon-limit').hide();
                    $('#layui-iconpicker-icon-limit-' + tmp + current).show();
                    e.stopPropagation();
                });
                return a;
            },
            /**
             * 搜索
             */
            search: function () {
                var item = '#' + PICKER_BODY + ' .layui-iconpicker-search .layui-input';
                a.event('input propertychange', item, function (e) {
                    var elem = e.target,
                        t = $(elem).val();
                    a.createList(t);
                });
                return a;
            },
            /**
             * 点击选中图标
             */
            check: function () {
                var item = '#' + PICKER_BODY + ' .layui-iconpicker-icon-item';
                a.event('click', item, function (e) {
                    var icon = '';
                    
                    switch(opts.type) {
                        case 'fontAwesome':
                            var el = $(e.currentTarget).find('.fa')
                            break;
                        case 'Glyphicons':
                            var el = $(e.currentTarget).find('.glyphicon')
                            break;
                        default:
                            var el = $(e.currentTarget).find('.layui-icon')
                            break;
                    }
                        
                    if (isFontClass) {
                        var clsArr = el.attr('class').split(/[\s\n]/),
//                            cls = clsArr[1],
//                            icon = cls
                            icon = el.attr('class');
                        $('#' + TITLE_ID).find('.layui-iconpicker-item .layui-icon').html('').attr('class', clsArr.join(' '));
                    } else {
                        switch(opts.type) {
                            case 'fontAwesome':
                                var clsArr = el.attr('class').split(/[\s\n]/),
                                    icon = el.attr('class');
                                $('#' + TITLE_ID).find('.layui-iconpicker-item .fa').html('').attr('class', clsArr.join(' '));
                                break;
                            case 'Glyphicons':
                                var clsArr = el.attr('class').split(/[\s\n]/),
                                    icon = el.attr('class');
                                $('#' + TITLE_ID).find('.layui-iconpicker-item .glyphicon').html('').attr('class', clsArr.join(' '));
                                break;
                            default:
                                var cls = el.html(),
                                    icon = cls;
                                $('#' + TITLE_ID).find('.layui-iconpicker-item .layui-icon').html(icon);
                                break;
                        }
                    }

                    $('#' + ICON_BODY).removeClass(selected).addClass(unselect);
                    $(elem).val(icon).attr('value', icon);
                    // 回调
                    if (click) {
                        click({
                            icon: icon
                        });
                    }

                });
                return a;
            },
            // 监听原始input数值改变
            inputListen: function(){
                var el = $(elem);
                a.event('change', elem, function(){
                    var value = el.val();
                })
                // el.change(function(){
                    
                // });
                return a;
            },
            event: function (evt, el, fn) {
                $(BODY).on(evt, el, fn);
            }
        };

        var common = {
            /**
             * 加载样式表
             */
            loadCss: function () {
                var css = '.layui-iconpicker {max-width: 280px;}.layui-iconpicker .layui-anim{display:none;position:absolute;left:0;top:42px;padding:5px 0;z-index:899;min-width:100%;border:1px solid #d2d2d2;max-height:300px;overflow-y:auto;background-color:#fff;border-radius:2px;box-shadow:0 2px 4px rgba(0,0,0,.12);box-sizing:border-box;}.layui-iconpicker-item{border:1px solid #e6e6e6;width:90px;height:38px;border-radius:4px;cursor:pointer;position:relative;}.layui-iconpicker-icon{border-right:1px solid #e6e6e6;-webkit-box-sizing:border-box;-moz-box-sizing:border-box;box-sizing:border-box;display:block;width:60px;height:100%;float:left;text-align:center;background:#fff;transition:all .3s;}.layui-iconpicker-icon i{line-height:38px;font-size:18px;}.layui-iconpicker-item > .layui-edge{left:70px;}.layui-iconpicker-item:hover{border-color:#D2D2D2!important;}.layui-iconpicker-item:hover .layui-iconpicker-icon{border-color:#D2D2D2!important;}.layui-iconpicker.layui-form-selected .layui-anim{display:block;}.layui-iconpicker-body{padding:6px;}.layui-iconpicker .layui-iconpicker-list{background-color:#fff;border:1px solid #ccc;border-radius:4px;}.layui-iconpicker .layui-iconpicker-icon-item{display:inline-block;width:21.1%;line-height:36px;text-align:center;cursor:pointer;vertical-align:top;height:36px;margin:4px;border:1px solid #ddd;border-radius:2px;transition:300ms;}.layui-iconpicker .layui-iconpicker-icon-item i.layui-icon{font-size:17px;}.layui-iconpicker .layui-iconpicker-icon-item:hover{background-color:#eee;border-color:#ccc;-webkit-box-shadow:0 0 2px #aaa,0 0 2px #fff inset;-moz-box-shadow:0 0 2px #aaa,0 0 2px #fff inset;box-shadow:0 0 2px #aaa,0 0 2px #fff inset;text-shadow:0 0 1px #fff;}.layui-iconpicker-search{position:relative;margin:0 0 6px 0;border:1px solid #e6e6e6;border-radius:2px;transition:300ms;}.layui-iconpicker-search:hover{border-color:#D2D2D2!important;}.layui-iconpicker-search .layui-input{cursor:text;display:inline-block;width:86%;border:none;padding-right:0;margin-top:1px;}.layui-iconpicker-search .layui-icon{position:absolute;top:11px;right:4%;}.layui-iconpicker-tips{text-align:center;padding:8px 0;cursor:not-allowed;}.layui-iconpicker-page{margin-top:6px;margin-bottom:-6px;font-size:12px;padding:0 2px;}.layui-iconpicker-page-count{display:inline-block;}.layui-iconpicker-page-operate{display:inline-block;float:right;cursor:default;}.layui-iconpicker-page-operate .layui-icon{font-size:12px;cursor:pointer;}.layui-iconpicker-body-page .layui-iconpicker-icon-limit{display:none;}.layui-iconpicker-body-page .layui-iconpicker-icon-limit:first-child{display:block;}';
                var $style = $('head').find('style[iconpicker]');
                if ($style.length === 0) {
                    $('head').append('<style rel="stylesheet" iconpicker>'+css+'</style>');
                }
            },
            /**
             * 获取数据
             */
            getData: {
                fontClass: function () {
                    var arr = ["layui-icon-rate-half","layui-icon-rate","layui-icon-rate-solid","layui-icon-cellphone","layui-icon-vercode","layui-icon-login-wechat","layui-icon-login-qq","layui-icon-login-weibo","layui-icon-password","layui-icon-username","layui-icon-refresh-3","layui-icon-auz","layui-icon-spread-left","layui-icon-shrink-right","layui-icon-snowflake","layui-icon-tips","layui-icon-note","layui-icon-home","layui-icon-senior","layui-icon-refresh","layui-icon-refresh-1","layui-icon-flag","layui-icon-theme","layui-icon-notice","layui-icon-website","layui-icon-console","layui-icon-face-surprised","layui-icon-set","layui-icon-template-1","layui-icon-app","layui-icon-template","layui-icon-praise","layui-icon-tread","layui-icon-male","layui-icon-female","layui-icon-camera","layui-icon-camera-fill","layui-icon-more","layui-icon-more-vertical","layui-icon-rmb","layui-icon-dollar","layui-icon-diamond","layui-icon-fire","layui-icon-return","layui-icon-location","layui-icon-read","layui-icon-survey","layui-icon-face-smile","layui-icon-face-cry","layui-icon-cart-simple","layui-icon-cart","layui-icon-next","layui-icon-prev","layui-icon-upload-drag","layui-icon-upload","layui-icon-download-circle","layui-icon-component","layui-icon-file-b","layui-icon-user","layui-icon-find-fill","layui-icon-loading","layui-icon-loading-1","layui-icon-add-1","layui-icon-play","layui-icon-pause","layui-icon-headset","layui-icon-video","layui-icon-voice","layui-icon-speaker","layui-icon-fonts-del","layui-icon-fonts-code","layui-icon-fonts-html","layui-icon-fonts-strong","layui-icon-unlink","layui-icon-picture","layui-icon-link","layui-icon-face-smile-b","layui-icon-align-left","layui-icon-align-right","layui-icon-align-center","layui-icon-fonts-u","layui-icon-fonts-i","layui-icon-tabs","layui-icon-radio","layui-icon-circle","layui-icon-edit","layui-icon-share","layui-icon-delete","layui-icon-form","layui-icon-cellphone-fine","layui-icon-dialogue","layui-icon-fonts-clear","layui-icon-layer","layui-icon-date","layui-icon-water","layui-icon-code-circle","layui-icon-carousel","layui-icon-prev-circle","layui-icon-layouts","layui-icon-util","layui-icon-templeate-1","layui-icon-upload-circle","layui-icon-tree","layui-icon-table","layui-icon-chart","layui-icon-chart-screen","layui-icon-engine","layui-icon-triangle-d","layui-icon-triangle-r","layui-icon-file","layui-icon-set-sm","layui-icon-add-circle","layui-icon-404","layui-icon-about","layui-icon-up","layui-icon-down","layui-icon-left","layui-icon-right","layui-icon-circle-dot","layui-icon-search","layui-icon-set-fill","layui-icon-group","layui-icon-friends","layui-icon-reply-fill","layui-icon-menu-fill","layui-icon-log","layui-icon-picture-fine","layui-icon-face-smile-fine","layui-icon-list","layui-icon-release","layui-icon-ok","layui-icon-help","layui-icon-chat","layui-icon-top","layui-icon-star","layui-icon-star-fill","layui-icon-close-fill","layui-icon-close","layui-icon-ok-circle","layui-icon-add-circle-fine"];
                    return arr;
                },
                unicode: function () {
                    return ["&amp;#xe6c9;","&amp;#xe67b;","&amp;#xe67a;","&amp;#xe678;","&amp;#xe679;","&amp;#xe677;","&amp;#xe676;","&amp;#xe675;","&amp;#xe673;","&amp;#xe66f;","&amp;#xe9aa;","&amp;#xe672;","&amp;#xe66b;","&amp;#xe668;","&amp;#xe6b1;","&amp;#xe702;","&amp;#xe66e;","&amp;#xe68e;","&amp;#xe674;","&amp;#xe669;","&amp;#xe666;","&amp;#xe66c;","&amp;#xe66a;","&amp;#xe667;","&amp;#xe7ae;","&amp;#xe665;","&amp;#xe664;","&amp;#xe716;","&amp;#xe656;","&amp;#xe653;","&amp;#xe663;","&amp;#xe6c6;","&amp;#xe6c5;","&amp;#xe662;","&amp;#xe661;","&amp;#xe660;","&amp;#xe65d;","&amp;#xe65f;","&amp;#xe671;","&amp;#xe65e;","&amp;#xe659;","&amp;#xe735;","&amp;#xe756;","&amp;#xe65c;","&amp;#xe715;","&amp;#xe705;","&amp;#xe6b2;","&amp;#xe6af;","&amp;#xe69c;","&amp;#xe698;","&amp;#xe657;","&amp;#xe65b;","&amp;#xe65a;","&amp;#xe681;","&amp;#xe67c;","&amp;#xe601;","&amp;#xe857;","&amp;#xe655;","&amp;#xe770;","&amp;#xe670;","&amp;#xe63d;","&amp;#xe63e;","&amp;#xe654;","&amp;#xe652;","&amp;#xe651;","&amp;#xe6fc;","&amp;#xe6ed;","&amp;#xe688;","&amp;#xe645;","&amp;#xe64f;","&amp;#xe64e;","&amp;#xe64b;","&amp;#xe62b;","&amp;#xe64d;","&amp;#xe64a;","&amp;#xe64c;","&amp;#xe650;","&amp;#xe649;","&amp;#xe648;","&amp;#xe647;","&amp;#xe646;","&amp;#xe644;","&amp;#xe62a;","&amp;#xe643;","&amp;#xe63f;","&amp;#xe642;","&amp;#xe641;","&amp;#xe640;","&amp;#xe63c;","&amp;#xe63b;","&amp;#xe63a;","&amp;#xe639;","&amp;#xe638;","&amp;#xe637;","&amp;#xe636;","&amp;#xe635;","&amp;#xe634;","&amp;#xe633;","&amp;#xe632;","&amp;#xe631;","&amp;#xe630;","&amp;#xe62f;","&amp;#xe62e;","&amp;#xe62d;","&amp;#xe62c;","&amp;#xe629;","&amp;#xe628;","&amp;#xe625;","&amp;#xe623;","&amp;#xe621;","&amp;#xe620;","&amp;#xe61f;","&amp;#xe61c;","&amp;#xe60b;","&amp;#xe619;","&amp;#xe61a;","&amp;#xe603;","&amp;#xe602;","&amp;#xe617;","&amp;#xe615;","&amp;#xe614;","&amp;#xe613;","&amp;#xe612;","&amp;#xe611;","&amp;#xe60f;","&amp;#xe60e;","&amp;#xe60d;","&amp;#xe60c;","&amp;#xe60a;","&amp;#xe609;","&amp;#xe605;","&amp;#xe607;","&amp;#xe606;","&amp;#xe604;","&amp;#xe600;","&amp;#xe658;","&amp;#x1007;","&amp;#x1006;","&amp;#x1005;","&amp;#xe608;"];
                },
                fontAwesome: function () {
                    return ["fa fa-500px","fa fa-amazon","fa fa-balance-scale","fa fa-battery-empty","fa fa-battery-full","fa fa-battery-half","fa fa-battery-quarter","fa fa-black-tie","fa fa-calendar-plus-o","fa fa-cc-diners-club","fa fa-cc-jcb","fa fa-chrome","fa fa-clone","fa fa-commenting","fa fa-commenting-o","fa fa-contao","fa fa-expeditedssl","fa fa-firefox","fa fa-fonticons","fa fa-genderless","fa fa-get-pocket","fa fa-gg","fa fa-gg-circle","fa fa-hand-lizard-o","fa fa-hand-paper-o","fa fa-hand-peace-o","fa fa-hand-pointer-o","fa fa-hand-rock-o","fa fa-hand-scissors-o","fa fa-hand-spock-o","fa fa-hourglass","fa fa-hourglass-end","fa fa-hourglass-half","fa fa-hourglass-o","fa fa-hourglass-start","fa fa-houzz","fa fa-i-cursor","fa fa-industry","fa fa-map","fa fa-map-o","fa fa-map-pin","fa fa-map-signs","fa fa-mouse-pointer","fa fa-object-group","fa fa-object-ungroup","fa fa-odnoklassniki","fa fa-opencart","fa fa-opera","fa fa-optin-monster","fa fa-registered","fa fa-safari","fa fa-sticky-note","fa fa-sticky-note-o","fa fa-television","fa fa-trademark","fa fa-tripadvisor","fa fa-vimeo","fa fa-wikipedia-w","fa fa-y-combinator","fa fa-adjust","fa fa-anchor","fa fa-archive","fa fa-area-chart","fa fa-arrows","fa fa-arrows-h","fa fa-arrows-v","fa fa-asterisk","fa fa-at","fa fa-ban","fa fa-bar-chart","fa fa-barcode","fa fa-bars","fa fa-bed","fa fa-beer","fa fa-bell","fa fa-bell-o","fa fa-bell-slash","fa fa-bell-slash-o","fa fa-bicycle","fa fa-binoculars","fa fa-birthday-cake","fa fa-bolt","fa fa-bomb","fa fa-book","fa fa-bookmark","fa fa-bookmark-o","fa fa-briefcase","fa fa-bug","fa fa-building","fa fa-building-o","fa fa-bullhorn","fa fa-bullseye","fa fa-bus","fa fa-calculator","fa fa-calendar","fa fa-calendar-o","fa fa-camera","fa fa-camera-retro","fa fa-car","fa fa-cart-arrow-down","fa fa-cart-plus","fa fa-cc","fa fa-certificate","fa fa-check","fa fa-check-circle","fa fa-check-circle-o","fa fa-check-square","fa fa-check-square-o","fa fa-child","fa fa-circle","fa fa-circle-o","fa fa-circle-o-notch","fa fa-circle-thin","fa fa-clock-o","fa fa-cloud","fa fa-cloud-download","fa fa-cloud-upload","fa fa-code","fa fa-code-fork","fa fa-coffee","fa fa-cog","fa fa-cogs","fa fa-comment","fa fa-comment-o","fa fa-comments","fa fa-comments-o","fa fa-compass","fa fa-copyright","fa fa-credit-card","fa fa-crop","fa fa-crosshairs","fa fa-cube","fa fa-cubes","fa fa-cutlery","fa fa-database","fa fa-desktop","fa fa-diamond","fa fa-dot-circle-o","fa fa-download","fa fa-ellipsis-h","fa fa-ellipsis-v","fa fa-envelope","fa fa-envelope-o","fa fa-envelope-square","fa fa-eraser","fa fa-exchange","fa fa-exclamation","fa fa-external-link","fa fa-eye","fa fa-eye-slash","fa fa-eyedropper","fa fa-fax","fa fa-female","fa fa-fighter-jet","fa fa-file-archive-o","fa fa-file-audio-o","fa fa-file-code-o","fa fa-file-excel-o","fa fa-file-image-o","fa fa-file-pdf-o","fa fa-file-video-o","fa fa-file-word-o","fa fa-film","fa fa-filter","fa fa-fire","fa fa-flag","fa fa-flag-checkered","fa fa-flag-o","fa fa-flask","fa fa-folder","fa fa-folder-o","fa fa-folder-open","fa fa-folder-open-o","fa fa-frown-o","fa fa-futbol-o","fa fa-gamepad","fa fa-gavel","fa fa-gift","fa fa-glass","fa fa-globe","fa fa-graduation-cap","fa fa-hdd-o","fa fa-headphones","fa fa-heart","fa fa-heart-o","fa fa-heartbeat","fa fa-history","fa fa-home","fa fa-inbox","fa fa-info","fa fa-info-circle","fa fa-key","fa fa-keyboard-o","fa fa-language","fa fa-laptop","fa fa-leaf","fa fa-lemon-o","fa fa-level-down","fa fa-level-up","fa fa-life-ring","fa fa-lightbulb-o","fa fa-line-chart","fa fa-location-arrow","fa fa-lock","fa fa-magic","fa fa-magnet","fa fa-male","fa fa-map-marker","fa fa-meh-o","fa fa-microphone","fa fa-minus","fa fa-minus-circle","fa fa-minus-square","fa fa-minus-square-o","fa fa-mobile","fa fa-money","fa fa-moon-o","fa fa-motorcycle","fa fa-music","fa fa-newspaper-o","fa fa-paint-brush","fa fa-paper-plane","fa fa-paper-plane-o","fa fa-paw","fa fa-pencil","fa fa-pencil-square","fa fa-pencil-square-o","fa fa-phone","fa fa-phone-square","fa fa-picture-o","fa fa-pie-chart","fa fa-plane","fa fa-plug","fa fa-plus","fa fa-plus-circle","fa fa-plus-square","fa fa-plus-square-o","fa fa-power-off","fa fa-print","fa fa-puzzle-piece","fa fa-qrcode","fa fa-question","fa fa-question-circle","fa fa-quote-left","fa fa-quote-right","fa fa-random","fa fa-recycle","fa fa-refresh","fa fa-reply","fa fa-reply-all","fa fa-retweet","fa fa-road","fa fa-rocket","fa fa-rss","fa fa-rss-square","fa fa-search","fa fa-search-minus","fa fa-search-plus","fa fa-server","fa fa-share","fa fa-share-alt","fa fa-share-square","fa fa-share-square-o","fa fa-shield","fa fa-ship","fa fa-shopping-cart","fa fa-sign-in","fa fa-sign-out","fa fa-signal","fa fa-sitemap","fa fa-sliders","fa fa-smile-o","fa fa-sort","fa fa-sort-alpha-asc","fa fa-sort-alpha-desc","fa fa-sort-amount-asc","fa fa-sort-asc","fa fa-sort-desc","fa fa-space-shuttle","fa fa-spinner","fa fa-spoon","fa fa-square","fa fa-square-o","fa fa-star","fa fa-star-half","fa fa-star-half-o","fa fa-star-o","fa fa-street-view","fa fa-suitcase","fa fa-sun-o","fa fa-tablet","fa fa-tachometer","fa fa-tag","fa fa-tags","fa fa-tasks","fa fa-taxi","fa fa-terminal","fa fa-thumb-tack","fa fa-thumbs-down","fa fa-thumbs-o-down","fa fa-thumbs-o-up","fa fa-thumbs-up","fa fa-ticket","fa fa-times","fa fa-times-circle","fa fa-times-circle-o","fa fa-tint","fa fa-toggle-off","fa fa-toggle-on","fa fa-trash","fa fa-trash-o","fa fa-tree","fa fa-trophy","fa fa-truck","fa fa-tty","fa fa-umbrella","fa fa-university","fa fa-unlock","fa fa-unlock-alt","fa fa-upload","fa fa-user","fa fa-user-plus","fa fa-user-secret","fa fa-user-times","fa fa-users","fa fa-video-camera","fa fa-volume-down","fa fa-volume-off","fa fa-volume-up","fa fa-wheelchair","fa fa-wifi","fa fa-wrench","fa fa-hand-o-down","fa fa-hand-o-left","fa fa-hand-o-right","fa fa-hand-o-up","fa fa-ambulance","fa fa-subway","fa fa-train","fa fa-mars","fa fa-mars-double","fa fa-mars-stroke","fa fa-mars-stroke-h","fa fa-mars-stroke-v","fa fa-mercury","fa fa-neuter","fa fa-transgender","fa fa-transgender-alt","fa fa-venus","fa fa-venus-double","fa fa-venus-mars","fa fa-file","fa fa-file-o","fa fa-file-text","fa fa-file-text-o","fa fa-cc-amex","fa fa-cc-discover","fa fa-cc-mastercard","fa fa-cc-paypal","fa fa-cc-stripe","fa fa-cc-visa","fa fa-google-wallet","fa fa-paypal","fa fa-btc","fa fa-eur","fa fa-gbp","fa fa-ils","fa fa-inr","fa fa-jpy","fa fa-krw","fa fa-rub","fa fa-try","fa fa-usd","fa fa-align-center","fa fa-align-justify","fa fa-align-left","fa fa-align-right","fa fa-bold","fa fa-chain-broken","fa fa-clipboard","fa fa-columns","fa fa-files-o","fa fa-floppy-o","fa fa-font","fa fa-header","fa fa-indent","fa fa-italic","fa fa-link","fa fa-list","fa fa-list-alt","fa fa-list-ol","fa fa-list-ul","fa fa-outdent","fa fa-paperclip","fa fa-paragraph","fa fa-repeat","fa fa-scissors","fa fa-strikethrough","fa fa-subscript","fa fa-superscript","fa fa-table","fa fa-text-height","fa fa-text-width","fa fa-th","fa fa-th-large","fa fa-th-list","fa fa-underline","fa fa-undo","fa fa-angle-double-up","fa fa-angle-down","fa fa-angle-left","fa fa-angle-right","fa fa-angle-up","fa fa-arrow-circle-up","fa fa-arrow-down","fa fa-arrow-left","fa fa-arrow-right","fa fa-arrow-up","fa fa-arrows-alt","fa fa-caret-down","fa fa-caret-left","fa fa-caret-right","fa fa-caret-up","fa fa-chevron-down","fa fa-chevron-left","fa fa-chevron-right","fa fa-chevron-up","fa fa-long-arrow-down","fa fa-long-arrow-left","fa fa-long-arrow-up","fa fa-backward","fa fa-compress","fa fa-eject","fa fa-expand","fa fa-fast-backward","fa fa-fast-forward","fa fa-forward","fa fa-pause","fa fa-play","fa fa-play-circle","fa fa-play-circle-o","fa fa-step-backward","fa fa-step-forward","fa fa-stop","fa fa-youtube-play","fa fa-adn","fa fa-android","fa fa-angellist","fa fa-apple","fa fa-behance","fa fa-behance-square","fa fa-bitbucket","fa fa-buysellads","fa fa-codepen","fa fa-connectdevelop","fa fa-css3","fa fa-dashcube","fa fa-delicious","fa fa-deviantart","fa fa-digg","fa fa-dribbble","fa fa-dropbox","fa fa-drupal","fa fa-empire","fa fa-facebook","fa fa-facebook-square","fa fa-flickr","fa fa-forumbee","fa fa-foursquare","fa fa-git","fa fa-git-square","fa fa-github","fa fa-github-alt","fa fa-github-square","fa fa-google","fa fa-google-plus","fa fa-gratipay","fa fa-hacker-news","fa fa-html5","fa fa-instagram","fa fa-ioxhost","fa fa-joomla","fa fa-jsfiddle","fa fa-lastfm","fa fa-lastfm-square","fa fa-leanpub","fa fa-linkedin","fa fa-linkedin-square","fa fa-linux","fa fa-maxcdn","fa fa-meanpath","fa fa-medium","fa fa-openid","fa fa-pagelines","fa fa-pied-piper","fa fa-pied-piper-alt","fa fa-pinterest","fa fa-pinterest-p","fa fa-qq","fa fa-rebel","fa fa-reddit","fa fa-reddit-square","fa fa-renren","fa fa-sellsy","fa fa-shirtsinbulk","fa fa-simplybuilt","fa fa-skyatlas","fa fa-skype","fa fa-slack","fa fa-slideshare","fa fa-soundcloud","fa fa-spotify","fa fa-stack-exchange","fa fa-stack-overflow","fa fa-steam","fa fa-steam-square","fa fa-stumbleupon","fa fa-tencent-weibo","fa fa-trello","fa fa-tumblr","fa fa-tumblr-square","fa fa-twitch","fa fa-twitter","fa fa-twitter-square","fa fa-viacoin","fa fa-vimeo-square","fa fa-vine","fa fa-vk","fa fa-weibo","fa fa-weixin","fa fa-whatsapp","fa fa-windows","fa fa-wordpress","fa fa-xing","fa fa-xing-square","fa fa-yahoo","fa fa-yelp","fa fa-youtube","fa fa-youtube-square","fa fa-h-square","fa fa-hospital-o","fa fa-medkit","fa fa-stethoscope","fa fa-user-md"];
                },
                Glyphicons: function () {
                    return ["glyphicon glyphicon-asterisk","glyphicon glyphicon-plus","glyphicon glyphicon-euro","glyphicon glyphicon-eur","glyphicon glyphicon-minus","glyphicon glyphicon-cloud","glyphicon glyphicon-envelope","glyphicon glyphicon-pencil","glyphicon glyphicon-glass","glyphicon glyphicon-music","glyphicon glyphicon-search","glyphicon glyphicon-heart","glyphicon glyphicon-star","glyphicon glyphicon-star-empty","glyphicon glyphicon-user","glyphicon glyphicon-film","glyphicon glyphicon-th-large","glyphicon glyphicon-th","glyphicon glyphicon-th-list","glyphicon glyphicon-ok","glyphicon glyphicon-remove","glyphicon glyphicon-zoom-in","glyphicon glyphicon-zoom-out","glyphicon glyphicon-off","glyphicon glyphicon-signal","glyphicon glyphicon-cog","glyphicon glyphicon-trash","glyphicon glyphicon-home","glyphicon glyphicon-file","glyphicon glyphicon-time","glyphicon glyphicon-road","glyphicon glyphicon-download-alt","glyphicon glyphicon-download","glyphicon glyphicon-upload","glyphicon glyphicon-inbox","glyphicon glyphicon-play-circle","glyphicon glyphicon-repeat","glyphicon glyphicon-refresh","glyphicon glyphicon-list-alt","glyphicon glyphicon-lock","glyphicon glyphicon-flag","glyphicon glyphicon-headphones","glyphicon glyphicon-volume-off","glyphicon glyphicon-volume-down","glyphicon glyphicon-volume-up","glyphicon glyphicon-qrcode","glyphicon glyphicon-barcode","glyphicon glyphicon-tag","glyphicon glyphicon-tags","glyphicon glyphicon-book","glyphicon glyphicon-bookmark","glyphicon glyphicon-print","glyphicon glyphicon-camera","glyphicon glyphicon-font","glyphicon glyphicon-bold","glyphicon glyphicon-italic","glyphicon glyphicon-text-height","glyphicon glyphicon-text-width","glyphicon glyphicon-align-left","glyphicon glyphicon-align-center","glyphicon glyphicon-align-right","glyphicon glyphicon-align-justify","glyphicon glyphicon-list","glyphicon glyphicon-indent-left","glyphicon glyphicon-indent-right","glyphicon glyphicon-facetime-video","glyphicon glyphicon-picture","glyphicon glyphicon-map-marker","glyphicon glyphicon-adjust","glyphicon glyphicon-tint","glyphicon glyphicon-edit","glyphicon glyphicon-share","glyphicon glyphicon-check","glyphicon glyphicon-move","glyphicon glyphicon-step-backward","glyphicon glyphicon-fast-backward","glyphicon glyphicon-backward","glyphicon glyphicon-play","glyphicon glyphicon-pause","glyphicon glyphicon-stop","glyphicon glyphicon-forward","glyphicon glyphicon-fast-forward","glyphicon glyphicon-step-forward","glyphicon glyphicon-eject","glyphicon glyphicon-chevron-left","glyphicon glyphicon-chevron-right","glyphicon glyphicon-plus-sign","glyphicon glyphicon-minus-sign","glyphicon glyphicon-remove-sign","glyphicon glyphicon-ok-sign","glyphicon glyphicon-question-sign","glyphicon glyphicon-info-sign","glyphicon glyphicon-screenshot","glyphicon glyphicon-remove-circle","glyphicon glyphicon-ok-circle","glyphicon glyphicon-ban-circle","glyphicon glyphicon-arrow-left","glyphicon glyphicon-arrow-right","glyphicon glyphicon-arrow-up","glyphicon glyphicon-arrow-down","glyphicon glyphicon-share-alt","glyphicon glyphicon-resize-full","glyphicon glyphicon-resize-small","glyphicon glyphicon-exclamation-sign","glyphicon glyphicon-gift","glyphicon glyphicon-leaf","glyphicon glyphicon-fire","glyphicon glyphicon-eye-open","glyphicon glyphicon-eye-close","glyphicon glyphicon-warning-sign","glyphicon glyphicon-plane","glyphicon glyphicon-calendar","glyphicon glyphicon-random","glyphicon glyphicon-comment","glyphicon glyphicon-magnet","glyphicon glyphicon-chevron-up","glyphicon glyphicon-chevron-down","glyphicon glyphicon-retweet","glyphicon glyphicon-shopping-cart","glyphicon glyphicon-folder-close","glyphicon glyphicon-folder-open","glyphicon glyphicon-resize-vertical","glyphicon glyphicon-resize-horizontal","glyphicon glyphicon-hdd","glyphicon glyphicon-bullhorn","glyphicon glyphicon-bell","glyphicon glyphicon-certificate","glyphicon glyphicon-thumbs-up","glyphicon glyphicon-thumbs-down","glyphicon glyphicon-hand-right","glyphicon glyphicon-hand-left","glyphicon glyphicon-hand-up","glyphicon glyphicon-hand-down","glyphicon glyphicon-circle-arrow-right","glyphicon glyphicon-circle-arrow-left","glyphicon glyphicon-circle-arrow-up","glyphicon glyphicon-circle-arrow-down","glyphicon glyphicon-globe","glyphicon glyphicon-wrench","glyphicon glyphicon-tasks","glyphicon glyphicon-filter","glyphicon glyphicon-briefcase","glyphicon glyphicon-fullscreen","glyphicon glyphicon-dashboard","glyphicon glyphicon-paperclip","glyphicon glyphicon-heart-empty","glyphicon glyphicon-link","glyphicon glyphicon-phone","glyphicon glyphicon-pushpin","glyphicon glyphicon-usd","glyphicon glyphicon-gbp","glyphicon glyphicon-sort","glyphicon glyphicon-sort-by-alphabet","glyphicon glyphicon-sort-by-alphabet-alt","glyphicon glyphicon-sort-by-order","glyphicon glyphicon-sort-by-order-alt","glyphicon glyphicon-sort-by-attributes","glyphicon glyphicon-sort-by-attributes-alt","glyphicon glyphicon-unchecked","glyphicon glyphicon-expand","glyphicon glyphicon-collapse-down","glyphicon glyphicon-collapse-up","glyphicon glyphicon-log-in","glyphicon glyphicon-flash","glyphicon glyphicon-log-out","glyphicon glyphicon-new-window","glyphicon glyphicon-record","glyphicon glyphicon-save","glyphicon glyphicon-open","glyphicon glyphicon-saved","glyphicon glyphicon-import","glyphicon glyphicon-export","glyphicon glyphicon-send","glyphicon glyphicon-floppy-disk","glyphicon glyphicon-floppy-saved","glyphicon glyphicon-floppy-remove","glyphicon glyphicon-floppy-save","glyphicon glyphicon-floppy-open","glyphicon glyphicon-credit-card","glyphicon glyphicon-transfer","glyphicon glyphicon-cutlery","glyphicon glyphicon-header","glyphicon glyphicon-compressed","glyphicon glyphicon-earphone","glyphicon glyphicon-phone-alt","glyphicon glyphicon-tower","glyphicon glyphicon-stats","glyphicon glyphicon-sd-video","glyphicon glyphicon-hd-video","glyphicon glyphicon-subtitles","glyphicon glyphicon-sound-stereo","glyphicon glyphicon-sound-dolby","glyphicon glyphicon-sound-5-1","glyphicon glyphicon-sound-6-1","glyphicon glyphicon-sound-7-1","glyphicon glyphicon-copyright-mark","glyphicon glyphicon-registration-mark","glyphicon glyphicon-cloud-download","glyphicon glyphicon-cloud-upload","glyphicon glyphicon-tree-conifer","glyphicon glyphicon-tree-deciduous","glyphicon glyphicon-cd","glyphicon glyphicon-save-file","glyphicon glyphicon-open-file","glyphicon glyphicon-level-up","glyphicon glyphicon-copy","glyphicon glyphicon-paste","glyphicon glyphicon-alert","glyphicon glyphicon-equalizer","glyphicon glyphicon-king","glyphicon glyphicon-queen","glyphicon glyphicon-pawn","glyphicon glyphicon-bishop","glyphicon glyphicon-knight","glyphicon glyphicon-baby-formula","glyphicon glyphicon-tent","glyphicon glyphicon-blackboard","glyphicon glyphicon-bed","glyphicon glyphicon-apple","glyphicon glyphicon-erase","glyphicon glyphicon-hourglass","glyphicon glyphicon-lamp","glyphicon glyphicon-duplicate","glyphicon glyphicon-piggy-bank","glyphicon glyphicon-scissors","glyphicon glyphicon-bitcoin","glyphicon glyphicon-btc","glyphicon glyphicon-xbt","glyphicon glyphicon-yen","glyphicon glyphicon-jpy","glyphicon glyphicon-ruble","glyphicon glyphicon-rub","glyphicon glyphicon-scale","glyphicon glyphicon-ice-lolly","glyphicon glyphicon-ice-lolly-tasted","glyphicon glyphicon-education","glyphicon glyphicon-option-horizontal","glyphicon glyphicon-option-vertical","glyphicon glyphicon-menu-hamburger","glyphicon glyphicon-modal-window","glyphicon glyphicon-oil","glyphicon glyphicon-grain","glyphicon glyphicon-sunglasses","glyphicon glyphicon-text-size","glyphicon glyphicon-text-color","glyphicon glyphicon-text-background","glyphicon glyphicon-object-align-top","glyphicon glyphicon-object-align-bottom","glyphicon glyphicon-object-align-horizontal","glyphicon glyphicon-object-align-left","glyphicon glyphicon-object-align-vertical","glyphicon glyphicon-object-align-right","glyphicon glyphicon-triangle-right","glyphicon glyphicon-triangle-left","glyphicon glyphicon-triangle-bottom","glyphicon glyphicon-triangle-top","glyphicon glyphicon-console","glyphicon glyphicon-superscript","glyphicon glyphicon-subscript","glyphicon glyphicon-menu-left","glyphicon glyphicon-menu-right","glyphicon glyphicon-menu-down","glyphicon glyphicon-menu-up"];
                }
            }
        };

        a.init();
        return new IconPicker();
    };

    /**
     * 选中图标
     * @param filter lay-filter
     * @param iconName 图标名称，自动识别fontClass/unicode
     */
    IconPicker.prototype.checkIcon = function (filter, iconName){
        var name = iconName;
        if($('*[lay-filter='+ filter +']').next().find('.layui-iconpicker-item .fa')) {
            var p = $('*[lay-filter='+ filter +']').next().find('.layui-iconpicker-item .fa');
            p.html('').attr('class', name);
        } else if($('*[lay-filter='+ filter +']').next().find('.layui-iconpicker-item .glyphicon')) {
            var p = $('*[lay-filter='+ filter +']').next().find('.layui-iconpicker-item .glyphicon');
            p.html('').attr('class', name);
        } else {
            var p = $('*[lay-filter='+ filter +']').next().find('.layui-iconpicker-item .layui-icon');
            if (name.indexOf('#xe') > 0){
                p.html(name);
            } else {
                p.html('').attr('class', name);
            }
        }
    };

    var iconPicker = new IconPicker();
    exports(_MOD, iconPicker);
});
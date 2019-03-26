/**
  公用JS主入口
  以依赖layui
**/    
layui.extend({
    treetable: '{/}' + window.cuiCore.tpl.static + '/dev/layui/layui_extends/treetable-lay/treetable',
}).define(['layer', 'element', 'form', 'table', 'code', 'treetable'], function(exports){
    var $ = layui.$
        ,layer = layui.layer
        ,element = layui.element
        ,form = layui.form
        ,table = layui.table
        ,treetable = layui.treetable;

    // 监控清除缓存按钮
    form.on('submit(cui_clear)', function(data){
        var url = $(this).data('url');
        
        ajax({
            url: url,
            type: 'GET'
        });
        return false;
    });
    // 监控退出按钮
    form.on('submit(cui_logout)', function(data){
        var url = $(this).data('url');
        
        ajax({
            url: url,
            type: 'GET'
        });
        return false;
    });
    // 监控左侧导航搜索按钮
    form.on('submit(cui_search)', menu_search);
    // 搜索绑定回车事件
    $('#cui_search_from').on('keyup', function(event) {
        if(event.keyCode == 13){
            menu_search();
        }
        return false;
    });
    // 监控菜单搜索输入框
    $('#search_title').on("input propertychange", function(event) {
        var title = $(this).val();
        if(title) {
            $(".sidebar-menu.tree .cui_search_filtr").find('a').css('color', '');
            $(".sidebar-menu.tree").find('.cui_search_filtr').removeClass(['cui_search_filtr', 'menu-open']).find('.treeview-menu').hide();
        }
    });
    
    // 数字输入框
    $('.cui_number').on('keyup', function(event) {
        $(this).val($(this).val().replace(/[^\d]/g, '').replace(/(\d{4})(?=\d)/g, "$1 "));
    });
    // 加
    $('.cui_plus').on('click', function(event) {
        var max = $('.cui_number').data('max');
        max = max ? max : 99999;
        var number = Number($('.cui_number').val());
        if(number < max) {
            number += 1;
            $('.cui_number').val(number);
        }
    });
    // 减
    $('.cui_sub').on('click', function(event) {
        var number = Number($('.cui_number').val());
        if(number > 0) {
            number -= 1;
            $('.cui_number').val(number);
        }
    });
    
    // 监控表单返回按钮
    form.on('submit(cui_close)', function(data){
        var way = $(this).data('way');
        var elem = $(this).data('elem') ? $(this).data('elem') : 'cui_table';
        if(way === 'popup') {
            var index = parent.layer.getFrameIndex(window.name);
            parent.layer.close(index);
            if(elem === 'cui_table_tree') {
                parent.treeConfig.data = '';
                parent.treetable.render(parent.treeConfig);
            } else {
                parent.layui.table.reload(elem, {page:{curr:1}});
            }
        }
        return false;
    });
    // 监控表单提交按钮
    form.on('submit(cui_form_write)', function(data){
        var way = $(this).data('way');
        var form = $(this).attr('target-form');
        var elem = $(this).data('elem') ? $(this).data('elem') : 'cui_table';
        if(form) {
            var url = $('.' + form).attr('action');
        } else {
            var url = $(this).data('url');
        }
        
        ajax({
            url: url,
            data: data.field
        }, way, elem);
        return false;
    });
    
    // 监控表单预览按钮
    form.on('submit(cui_preview)', function(data){
        var way = $(this).data('way');
        var url = $(this).data('url');
        var form = $(this).attr('target-form');

        ajax({
            url: url,
            data: data.field
        }, way);
        return false;
    });
    
    var cui_table = {
        // 实例化数据表格
        cui_table_render: function(param) {
            if (!param.url && param.cols) {
                layer.msg('url和渲染数据不存在!', {icon: 5});
                return false;
            }
            param.elem = param.elem ? param.elem : 'cui_table';
            param.toolbar = param.toolbar ? param.toolbar : '#toolbar';
            if(param.elem === 'cui_table_tree') {
                var tableTreeConfig = {
                    treeColIndex: 1,          // treetable新增参数
                    treeSpid: 0,             // treetable新增参数
                    treeIdName: 'id',       // treetable新增参数
                    treePidName: 'pid',     // treetable新增参数
                    treeDefaultClose: true,   // treetable新增参数
                    treeLinkage: true,        // treetable新增参数
                    elem: '#' + param.elem,
                    url: param.url,
                    toolbar: param.toolbar,
                    defaultToolbar: ['filter'],
                    cols: [param.cols],
                    done: function (res, curr, count) {

                    }
                };
                parent.treeConfig = tableTreeConfig;
                treetable.render(tableTreeConfig);
                parent.treetable = treetable;
            } else {
                var tableConfig = {
                    elem: '#' + param.elem
                    , id: param.elem
                    , method: 'post'
                    , url: param.url //数据接口
                    , page: true //开启分页
                    , toolbar: param.toolbar
                    , defaultToolbar: ['filter'] //'filter', 'print', 'exports'
                    , response: {
                        statusName: 'code' //数据状态的字段名称，默认：code
                        , statusCode: 1 //成功的状态码，默认：0
                        , msgName: 'msg' //状态信息的字段名称，默认：msg
                        , countName: 'count' //数据总数的字段名称，默认：count
                        , dataName: 'data' //数据列表的字段名称，默认：data
                    }
                    , cols: [param.cols]
                    , done: function (res, curr, count) {
                        page = curr;

                    }
                };

                table.render(tableConfig);
            }
            //头部工具条监听
            table.on('toolbar(' + param.elem + ')', function(obj){
                // 弹窗
                var way = $(this).data('way');
                // url地址
                var url = $(this).data('url');
                // 标题
                var title = $(this).data('title');
                switch(obj.event){
                    case 'create': //添加按钮
                        if(way === 'popup') {
                            layer.open({
                                type: 2,
                                title: title,
                                shadeClose: true,
                                shade: false,
                                maxmin: true, //开启最大化最小化按钮
                                area: ['893px', '600px'],
                                content: url
                            });
                        } else {
                            window.location.href = url;
                        }
                        break;
                    case 'table_search': //搜索按钮
                        var keywords = $(this).parents('.cuicmf-input-group').find('.keywords').val();
                        if(param.elem === 'cui_table_tree') {
                            var searchCount = 0;
                            $('#cui_table_tree').next('.treeTable').find('.layui-table-body tbody tr td').each(function () {
                                $(this).css('background-color', 'transparent');
                                var text = $(this).text();
                                if (keywords != '' && text.indexOf(keywords) >= 0) {
                                    $(this).css('background-color', 'rgba(250,230,160,0.5)');
                                    if (searchCount == 0) {
                                        treetable.expandAll('#cui_table_tree');
                                        $('html,body').stop(true);
                                        $('html,body').animate({scrollTop: $(this).offset().top - 150}, 500);
                                    }
                                    searchCount++;
                                }
                            });
                            if (keywords == '') {
                                layer.msg("请输入搜索内容", {icon: 5});
                            } else if (searchCount == 0) {
                                layer.msg("没有匹配结果", {icon: 5});
                            }
                        } else {
                            //执行重载
                            table.reload(param.elem, {
                                page: {
                                    curr: 1 //重新从第1页开始
                                }
                                , where: {
                                    key: {
                                        keywords: keywords
                                    }
                                }
                            });
                        }
                        break;
                    case 'expand':
                        treetable.expandAll('#' + param.elem);
                        break;
                    case 'fold':
                        treetable.foldAll('#' + param.elem);
                        break;
                    case 'refresh':
                        tableTreeConfig.data = '';
                        treetable.render(tableTreeConfig);
                        break;
                }
            });
            
            //监听行工具条
            table.on('tool(' + param.elem + ')', function (obj) {
                // 获取点击行的数据
                var data = obj.data;
                // 获取规则
                var way = $(this).data('way');
                // url地址
                var url = $(this).data('url');
                // 标题
                var title = $(this).data('title');
                // 提示语
                var prompt = $(this).data('prompt') ? $(this).data('prompt') : '确认要执行该操作吗？';
                // 判断点击按钮操作类型
                // 插件安装模块强行转换成删除模块分支操作
                obj.event = obj.event === 'install' ? 'delete' : obj.event;
                switch(obj.event){
                    case 'delete':
                        if(param.elem === 'cui_table_uninstalled' || param.elem === 'cui_table_installed') {
                            url = localUrl(url + '?name=' + data.name);
                        } else {
                            url = localUrl(url + '?id=' + data.id);
                        }
                        
                        layer.confirm(prompt, {
                            btn: ['确定','取消']
                        }, function(index){
                            layer.close(index);
                            ajax({
                                url: url,
                                type: 'GET'
                            }, way, param.elem);
                        }, function(index){
                            layer.close(index);
                        });
                        break;
                    default:
                        url = localUrl(url + '?id=' + data.id);
                        if(way === 'popup') {
                            layer.open({
                                type: 2,
                                title: title,
                                shadeClose: true,
                                shade: false,
                                maxmin: true, //开启最大化最小化按钮
                                area: ['893px', '600px'],
                                content: url
                            });
                        } else {
                            window.location.href = url;
                        }
                        break;
                }
            });
        },
        cui_form_verify: function(obj) {
            form.verify(obj);
        },
        cui_form_val: function(elem, obj) {
            form.val(elem, obj);
        }
        
    };

    /**
     * 搜索菜单
     * @param {type} data
     * @returns {Boolean}
     */
    function menu_search(data) {
        var title = $('#search_title').val();
        if(title) {
            $(".sidebar-menu.tree .cui_search_filtr").find('a').css('color', '');
            $(".sidebar-menu.tree").find('.cui_search_filtr').removeClass(['cui_search_filtr', 'menu-open']).find('.treeview-menu').hide();

            $(".sidebar-menu.tree li:contains('" + title + "')").each(function(i) {
                if($(this).hasClass('treeview')) {
                    $(this).addClass(['cui_search_filtr', 'menu-open']);
                    $(this).children("a").css('color', '#ffea00');
                    $(this).children('.treeview-menu').show();
                } else {
                    $(this).addClass(['cui_search_filtr']);
                    $(this).children('a').css('color', '#ffea00');
                }
            });
        }
        return false;
    }
    
    /**
     * Ajax处理
     * map json 必要的参数
     * way string 操作选项
     * elem table容器
     */
    function ajax(map, way, elem) {
        map.type = map.type ? map.type : 'POST';
        map.dataType = map.dataType ? map.dataType : 'json';
        map.timeout = map.timeout ? map.timeout : 500;
        elem = elem ? elem : 'cui_table';
        // 加载层ID
        var loadIndex = '';
        $.ajax({
            url: map.url,
            type: map.type,
            dataType: map.dataType,
            data: map.data,
            timeout: map.timeout,
            async: false,
            beforeSend: function(xhr) {
                loadIndex = layer.load(0, {shade: false});
//                switch(way) {
//                    default:
//                        loadIndex = layer.load(0, {shade: false});
//                        break;
//                }
            },
            complete: function(xhr,status) {
                layer.close(loadIndex);
//                switch(way) {
//                    default:
//                        layer.close(loadIndex);
//                        break;
//                }
            },
            success: function(result) {
                if(result.code === 1) {
                    switch(way) {
                        case 'confirm':
                            layer.msg(result.msg, {icon: 6}, function(){
                                if(elem === 'cui_table_tree') {
                                    parent.treeConfig.data = '';
                                    treetable.render(parent.treeConfig);
//                                    cui_table.cui_table_render(parent.config_);
                                } else {
                                    layui.table.reload(elem, {page:{curr:1}});
                                }
                            });
                            break;
                        case 'popup':
                            var index = parent.layer.getFrameIndex(window.name);
                            layer.msg(result.msg, {icon: 6}, function(){
                                parent.layer.close(index);
                                if(elem === 'cui_table_tree') {
                                    parent.treeConfig.data = '';
                                    parent.treetable.render(parent.treeConfig);
                                } else {
                                    parent.layui.table.reload(elem, {page:{curr:1}});
                                }
                            });
                            break;
                        case 'preview':
                            // html 标签转实体
                            var encode = function(html) {
                                return html.replace(/&(?!#?[a-zA-Z0-9]+;)/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/'/g, '&#39;').replace(/"/g, '&quot;');
                            }
                            /**
                            * 首字母大写
                            * @param str
                            * @returns {*}
                            */
                            var ucfirst = function (str){
                                if(!!str) return str[0].toUpperCase() + str.substr(1);
                                else return str;
                            }
                            var name = map.data.name;
                            
                            layer.open({
                                type: 1,
                                maxmin: true,
                                area: ['640px', '420px'], //宽高
                                title: '预览插件主文件',
                                content: '<pre class="layui-code" lay-skin="notepad">' + encode(result.msg) + '</pre>',
                                success: function(layero, index){
                                    layui.code({
                                        title: 'addons\\' + name + '\\' + ucfirst(name) + '.php',
                                        about: false
                                    });
                                }
                            });
                            break;
                        default:
                            // url地址存在进行跳转,默认进行页面刷新
                            layer.msg(result.msg, {icon: 6}, function(){
                                if(result.url) {
                                    window.location.href = result.url;
                                } else {
                                    window.location.reload()
                                }
                            });
                            break;
                    }
                } else {
                    var msg = result.msg.split(':');
                    if(msg.length > 1) {
                        if($('#' + msg[0]).data('verify')) {
                            $('#' + msg[0]).find('.layui-input.multiple').addClass('danger');
                        }
                        $("html,body").finish().animate({"scrollTop":$('#' + msg[0]).offset().top},400);
                        if(msg[0] == 'verify') {
                            $('#' + msg[0]).val('').focus();
                        } else {
                            $('#' + msg[0]).focus();
                        }
                        layer.msg(msg[1], {icon: 5}, function(){
                            if(msg[0] == 'verify'){
                                $('#verify_img').click();
                            }
                            if($('#' + msg[0]).data('verify')) {
                                $('#' + msg[0]).find('.layui-input.multiple').removeClass('danger');
                            }
                        });
                    } else {
                        layer.msg(msg[0], {icon: 5});
                    }
//
                }
            }
        });
    }
    
    // URL地址格式化
    function localUrl(url) {
        if(!url) return url;
        var localUrl = '';
        url = url.split('?');
        var url_q = url[0].split('.');
        var len = url[1].search('&');
        if(len > -1) {
            var url_h = url[1].split('&');
            localUrl = url_q[0];
            for(var i = 0; i < url_h.length; i++) {
                var h = url_h[i].split('=');
                localUrl = localUrl + '/' + h[0] + '/' + h[1];
            }
            localUrl = localUrl + '.' + url_q[1];
        } else {
            var url_h = url[1].split('=');
            localUrl = url_q[0] + '/' + url_h[0] + '/' + url_h[1] + '.' + url_q[1];
        }
        return localUrl;
    }
    
    exports('common', cui_table);
});    
//layui.use(['form', 'table', 'layer', 'element', 'helper'], function () {
//    var form = layui.form
//        , table = layui.table
//        , layer = layui.layer
//        , element = layui.element
//        , helper = layui.helper;
//        
//    // 表单验证
//    function verify(content, type) {
//        if(!content) return false;
//        var rules = type.slipt('=');
//        switch(rules[0]) {
//            case 'str-length': //计算英文/汉字/数字的长度
//                if(rules[1]) {
//                    rules[1] > content.replace(/[^\x00-\xff]/g, '01').length
//                    return ;
//                }
//                break;
//        }
//    }
//    
//    // AJax处理
//    function ajax(map, way) {
//        map['type'] = map['type'] ? map['type'] : 'POST';
//        map['dataType'] = map['dataType'] ? map['dataType'] : 'json';
//        map['timeout'] = map['timeout'] ? map['timeout'] : 500;
//        var loadIndex = '';
//        $.ajax({
//            url: map['url'],
//            type: map['type'],
//            dataType: map['dataType'],
//            data: map['data'],
//            timeout: map['timeout'],
//            async: false,
//            beforeSend: function(xhr) {
//                switch(way) {
//                    default:
//                        loadIndex = layer.load(0, {shade: false});
//                        break;
//                }
//            },
//            complete: function(xhr,status) {
//                switch(way) {
//                    default:
//                        layer.close(loadIndex);
//                        break;
//                }
//            },
//            success: function(result) {
//                if(result.code === 1) {
//                    switch(way) {
//                        case 'confirm':
//                            layer.msg(result.msg, {icon: 6}, function(){
//                                parent.layui.table.reload('cuiTable',{page:{curr:1}});
//                            });
//                            break;
//                        case 'confirm-tree-table':
//                            layer.msg(result.msg, {icon: 6}, function(){
//                                parent.config_.data = '';
//                                parent.layui.treetable.render(parent.config_);
//                            });
//                            break;
//                        case 'confirm_cui_table_uninstalled':
//                            layer.msg(result.msg, {icon: 6}, function(){
//                                parent.layui.table.reload('cui_table_uninstalled',{page:{curr:1}});
//                            });
//                            break;
//                        case 'confirm_cui_table_installed':
//                            layer.msg(result.msg, {icon: 6}, function(){
//                                parent.layui.table.reload('cui_table_installed',{page:{curr:1}});
//                            });
//                            break;
//                        case 'popup':
//                            var index = parent.layer.getFrameIndex(window.name);
//                            layer.msg(result.msg, {icon: 6}, function(){
//                                parent.layer.close(index);
//                                parent.layui.table.reload('cuiTable',{page:{curr:1}});
//                            });
//                            break;
//                        case 'popup-tree-table':
//                            var index = parent.layer.getFrameIndex(window.name);
//                            layer.msg(result.msg, {icon: 6}, function(){
//                                parent.layer.close(index);
//                                parent.config_.data = '';
//                                parent.layui.treetable.render(parent.config_);
//                            });
//                            break;
//                        case 'preview':
//                            // html 标签转实体
//                            var encode = function(html) {
//                                return html.replace(/&(?!#?[a-zA-Z0-9]+;)/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/'/g, '&#39;').replace(/"/g, '&quot;');
//                            }
//                            var name = $(form).find('[name=name]').val();
//                            layer.open({
//                                type: 1,
//                                maxmin: true,
//                                area: ['640px', '420px'], //宽高
//                                title: '预览插件主文件',
//                                content: '<pre class="layui-code" lay-skin="notepad">' + encode(data.msg) + '</pre>',
//                                success: function(layero, index){
//                                    layui.code({
//                                        title: 'addons\\' + name + '\\' + helper.ucfirst(name) + '.php',
//                                        about: false
//                                    });
//                                }
//                            });
//                            break;
//                        default:
//                            layer.msg(result.msg, {icon: 6}, function(){
//                                window.location.href = result.url;
//                            });
//                            break;
//                    }
//                } else {
//                    var msg = result.msg.split(':');
//                    if(msg.length > 1) {
//                        if($('#' + msg[0]).data('verify')) {
//                            $('#' + msg[0]).find('.layui-input.multiple').addClass('danger');
//                        }
//                        $("html,body").finish().animate({"scrollTop":$('#' + msg[0]).offset().top},400);
//                        if(msg[0] == 'verify') {
//                            $('#' + msg[0]).val('').focus();
//                        } else {
//                            $('#' + msg[0]).focus();
//                        }
//                        layer.msg(msg[1], {icon: 5}, function(){
//                            if(msg[0] == 'verify'){
//                                $('#verify_img').click();
//                            }
//                            if($('#' + msg[0]).data('verify')) {
//                                $('#' + msg[0]).find('.layui-input.multiple').removeClass('danger');
//                            }
//                        });
//                    } else {
//                        layer.msg(msg[0], {icon: 5});
//                    }
//
//                }
//            }
//        });
//    }
    
//        
//});
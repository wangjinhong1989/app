define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'jubao/index' + location.search,
                    add_url: 'jubao/add',
                    edit_url: 'jubao/edit',
                    del_url: 'jubao/del',
                    multi_url: 'jubao/multi',
                    table: 'jubao',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'article.title', title: __('Article.title')},
                        {field: 'auth_username', title: __('作者')},
                        {field: 'username', title: __('举报人')},
                        {field: 'type', title: __('Type'), searchList: {"内容抄袭或转载":__('内容抄袭或转载'),"广告或垃圾信息":__('广告或垃圾信息'),"其它":__('其它')}, formatter: Table.api.formatter.normal},
                        {field: 'status', title: __('Status'), searchList: {"有效":__('有效'),"无效":__('无效'),"审核":__('审核')}, formatter: Table.api.formatter.status},
                        {field: 'content', title: __('Content'),formatter: function (value) {
                                return value.substr(0, 50);
                            }},

                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            Controller.api.bindevent();
        },
        edit: function () {
            Controller.api.bindevent();
        },
        api: {
            bindevent: function () {
                Form.api.bindevent($("form[role=form]"));
            }
        }
    };
    return Controller;
});
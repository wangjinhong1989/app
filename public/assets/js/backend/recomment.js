define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'recomment/index' + location.search,
                    add_url: 'recomment/add',
                    edit_url: 'recomment/edit',
                    del_url: 'recomment/del',
                    multi_url: 'recomment/multi',
                    table: 'recomment',
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
                        {field: 'img', title: __('Img')},
                        {field: 'url', title: __('Url'), formatter: Table.api.formatter.url},
                        {field: 'url_type', title: __('Url_type'), searchList: {"内链":__('内链'),"外链":__('外链')}, formatter: Table.api.formatter.normal},
                        {field: 'status', title: __('Status'), searchList: {"显示":__('显示'),"隐藏":__('隐藏')}, formatter: Table.api.formatter.status},
                        {field: 'article.title', title: __('Article.title')},
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
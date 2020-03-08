define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'tanchuang/index' + location.search,
                    add_url: 'tanchuang/add',
                    edit_url: 'tanchuang/edit',
                    del_url: 'tanchuang/del',
                    multi_url: 'tanchuang/multi',
                    table: 'tanchuang',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'paixu',
                sortOrder:"asc",
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: 'ID'},
                        {field: 'title', title: __('广告标题')},
                        {field: 'paixu', title: __('Paixu')},
                        {field: 'image', title: __('Image'), events: Table.api.events.image, formatter: formart_img},
                        {field: 'begin_time_text', title: __('开始时间')},
                        {field: 'end_time_text', title: __('结束时间')},
                        {field: 'url_type', title: __('Url_type'), searchList: {"内链":__('内链'),"外链":__('外链')}, formatter: Table.api.formatter.normal},
                        {field: 'url', title: __('Url'), formatter: Table.api.formatter.url},
                        {field: 'status', title: __('Status'), searchList: {"显示":__('显示'),"隐藏":__('隐藏')}, formatter: Table.api.formatter.status},
                        {field: 'article.title', title: __('文章标题')},
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
define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'discover/index' + location.search,
                    add_url: 'discover/add',
                    edit_url: 'discover/edit',
                    del_url: 'discover/del',
                    multi_url: 'discover/multi',
                    table: 'discover',
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
                        {field: 'id', title: "ID"},
                        {field: 'title', title: __('广告名称')},
                        {field: 'paixu', title: __('Paixu')},
                        {field: 'image', title: __('Image'), events: Table.api.events.image, formatter: Table.api.formatter.image},

                        {field: 'status_text', title: __('Status'), searchList: {"显示":__('显示'),"隐藏":__('隐藏')}, formatter: Table.api.formatter.status},

                        {field: 'url_type', title: __('Url_type'), searchList: {"外链":__('外链'),"内链":__('内链')}, formatter: Table.api.formatter.normal},
                        {field: 'url', title: __('Url'), formatter: Table.api.formatter.url},
                        {field: 'begin_time', title: __('Begin_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'end_time', title: __('End_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'article.title', title: __('文章标题')},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);
        },
        add: function () {
            $("#c-article_id").data("params", function (obj) {
                return {custom: {articletype_id: ["NOT IN",2]}}
            });
            Controller.api.bindevent();
        },
        edit: function () {
            $("#c-article_id").data("params", function (obj) {
                return {custom: {articletype_id: ["NOT IN",2]}}
            });
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
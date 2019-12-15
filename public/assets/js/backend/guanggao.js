define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'guanggao/index' + location.search,
                    add_url: 'guanggao/add',
                    edit_url: 'guanggao/edit',
                    del_url: 'guanggao/del',
                    multi_url: 'guanggao/multi',
                    table: 'guanggao',
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
                        {field: 'position', title: __('Position')},
                        {field: 'name', title: __('Name')},
                        {field: 'status', title: __('Status'), searchList: {"显示":__('显示'),"隐藏":__('隐藏')}, formatter: Table.api.formatter.status},
                        {field: 'url', title: __('Url'), formatter: Table.api.formatter.url},
                        {field: 'img', title: __('Img')},
                        {field: 'time', title: __('Time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'last_time', title: __('Last_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'start_time', title: __('Start_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
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
define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'config_user/index' + location.search,
                    add_url: 'config_user/add',
                    edit_url: 'config_user/edit',
                    del_url: 'config_user/del',
                    multi_url: 'config_user/multi',
                    table: 'config_user',
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
                        {field: 'modify_username', title: __('Modify_username')},
                        {field: 'kaiguan', title: __('Kaiguan'), searchList: {"开启":__('开启'),"关闭":__('关闭')}, formatter: Table.api.formatter.normal},
                        {field: 'geren', title: __('Geren')},
                        {field: 'gerencishu', title: __('Gerencishu')},
                        {field: 'qiyecishu', title: __('Qiyecishu')},
                        {field: 'meiticishu', title: __('Meiticishu')},
                        {field: 'create_time', title: __('Create_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
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
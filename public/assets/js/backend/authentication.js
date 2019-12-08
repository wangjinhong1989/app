define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'authentication/index' + location.search,
                    add_url: 'authentication/add',
                    edit_url: 'authentication/edit',
                    del_url: 'authentication/del',
                    multi_url: 'authentication/multi',
                    table: 'authentication',
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
                        {field: 'user_id', title: __('User_id')},
                        {field: 'authentication_type', title: __('Authentication_type'), searchList: {"个人认证":__('个人认证'),"企业认证":__('企业认证'),"媒体认证":__('媒体认证')}, formatter: Table.api.formatter.normal},
                        {field: 'status', title: __('Status'), searchList: {"有效":__('有效'),"无效":__('无效'),"审核":__('审核')}, formatter: Table.api.formatter.status},
                        {field: 'files', title: __('Files')},
                        {field: 'certificates_id', title: __('Certificates_id')},
                        {field: 'number', title: __('Number')},
                        {field: 'parent_id', title: __('Parent_id')},
                        {field: 'time', title: __('Time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'certificates.name', title: __('Certificates.name')},
                        {field: 'user.username', title: __('User.username')},
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
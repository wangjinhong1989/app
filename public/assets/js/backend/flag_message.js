define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'flag_message/index' + location.search,
                    add_url: 'flag_message/add',
                    edit_url: 'flag_message/edit',
                    del_url: 'flag_message/del',
                    multi_url: 'flag_message/multi',
                    table: 'flag_message',
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
                        {field: 'reply_flag', title: __('Reply_flag'), formatter: Table.api.formatter.flag},
                        {field: 'comment_flag', title: __('Comment_flag'), formatter: Table.api.formatter.flag},
                        {field: 'follow_flag', title: __('Follow_flag'), formatter: Table.api.formatter.flag},
                        {field: 'system_flag', title: __('System_flag'), formatter: Table.api.formatter.flag},
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
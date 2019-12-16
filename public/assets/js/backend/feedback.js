define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'feedback/index' + location.search,
                    add_url: 'feedback/add',
                    edit_url: 'feedback/edit',
                    del_url: 'feedback/del',
                    multi_url: 'feedback/multi',
                    table: 'feedback',
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
                        {field: 'feedback_type', title: __('Feedback_type'), searchList: {"功能反馈":__('功能反馈'),"体验反馈":__('体验反馈'),"其它":__('其它')}, formatter: Table.api.formatter.normal},
                        {field: 'content', title: __('Content')},
                        {field: 'files', title: __('Files')},
                        {field: 'time', title: __('Time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
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
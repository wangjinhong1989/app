define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'authentication_media/index' + location.search,
                    add_url: 'authentication_media/add',
                    edit_url: 'authentication_media/edit',
                    del_url: 'authentication_media/del',
                    multi_url: 'authentication_media/multi',
                    table: 'authentication_media',
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
                        {field: 'status', title: __('Status'), searchList: {"审核中":__('审核中'),"审核通过":__('审核通过'),"审核不通过":__('审核不通过')}, formatter: Table.api.formatter.status},
                        {field: 'name', title: __('Name')},
                        {field: 'certificates_number', title: __('Certificates_number')},
                        {field: 'files', title: __('Files'),formatter:formart_img},
                        {field: 'note', title: __('Note')},
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
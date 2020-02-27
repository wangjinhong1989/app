define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'zhengjian_qiye/index' + location.search,
                    add_url: 'zhengjian_qiye/add',
                    edit_url: 'zhengjian_qiye/edit',
                    del_url: 'zhengjian_qiye/del',
                    multi_url: 'zhengjian_qiye/multi',
                    table: 'zhengjian_qiye',
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
                        {field: 'certificates_type', title: __('Certificates_type'), searchList: {"企业营业执照":__('企业营业执照'),"企业组织机构代码":__('企业组织机构代码'),"三证合一":__('三证合一')}, formatter: Table.api.formatter.normal},
                        {field: 'name', title: __('Name')},
                        {field: 'number', title: __('Number')},
                        {field: 'image', title: __('Image'), events: Table.api.events.image, formatter: Table.api.formatter.image},
                        {field: 'faren_name', title: __('Faren_name')},
                        {field: 'faren_number', title: __('Faren_number')},
                        {field: 'images', title: __('Images'), events: Table.api.events.image, formatter: Table.api.formatter.images},
                        {field: 'status', title: __('Status'), searchList: {"审核中":__('审核中'),"通过":__('通过'),"不通过":__('不通过')}, formatter: Table.api.formatter.status},
                        {field: 'create_time', title: __('Create_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
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
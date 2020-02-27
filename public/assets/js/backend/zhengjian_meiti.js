define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'zhengjian_meiti/index' + location.search,
                    add_url: 'zhengjian_meiti/add',
                    edit_url: 'zhengjian_meiti/edit',
                    del_url: 'zhengjian_meiti/del',
                    multi_url: 'zhengjian_meiti/multi',
                    table: 'zhengjian_meiti',
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
                        {field: 'user.username', title: __('User.username')},
                        {field: 'certificates_type', title: __('Certificates_type'), searchList: {"企业营业执照":__('企业营业执照'),"企业组织机构代码":__('企业组织机构代码'),"三证合一":__('三证合一')}, formatter: Table.api.formatter.normal},
                        {field: 'name', title: __('Name')},
                        {field: 'number', title: __('Number')},
                        {field: 'image', title: __('Image'), events: Table.api.events.image, formatter: Table.api.formatter.image},
                        {field: 'faren_name', title: __('Faren_name')},
                        {field: 'faren_number', title: __('Faren_number')},
                        {field: 'images', title: __('证件照片'), formatter:formart_img},
                        {field: 'status', title: __('Status'), searchList: {"审核中":__('审核中'),"通过":__('通过'),"不通过":__('不通过')}, formatter: Table.api.formatter.status},
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
define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'authentication_personal/index' + location.search,
                    add_url: 'authentication_personal/add',
                    edit_url: 'authentication_personal/edit',
                    del_url: 'authentication_personal/del',
                    multi_url: 'authentication_personal/multi',
                    table: 'authentication_personal',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'id',
                queryParams:queryParams,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('ID')},
                        {field: 'user.username', title: __('User.username')},
                        {field: 'type', title: __('Type'), searchList: {"个人认证":__('个人认证'),"企业认证":__('企业认证'),"媒体认证":__('媒体认证')}, formatter: Table.api.formatter.normal},
                        {field: 'certificates_type', title: __('Certificates_type')},
                        {field: 'status', title: __('Status'), searchList: {"审核中":__('审核中'),"审核通过":__('审核通过'),"审核不通过":__('审核不通过')}, formatter: Table.api.formatter.status},

                        {field: 'name', title: __('Name')},
                        {field: 'certificates_number', title: __('Certificates_number')},
                        {field: 'files', title: __('Files'),formatter:formart_img},
                        {field: 'note', title: __('Note')},
                        {field: 'time', title: __('Time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},

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

    function queryParams(pageReqeust) {


        pageReqeust.filter=JSON.parse( pageReqeust.filter );
        pageReqeust.op=JSON.parse( pageReqeust.op );
        pageReqeust.filter["type"]="个人认证";
        pageReqeust.op["type"]="=";
        pageReqeust.op=JSON.stringify( pageReqeust.op );
        pageReqeust.filter=JSON.stringify( pageReqeust.filter );
        return pageReqeust;
    }

    return Controller;
});
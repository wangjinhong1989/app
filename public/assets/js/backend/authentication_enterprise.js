define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'authentication_enterprise/index' + location.search,
                    add_url: 'authentication_enterprise/add',
                    edit_url: 'authentication_enterprise/edit',
                    del_url: 'authentication_enterprise/del',
                    multi_url: 'authentication_enterprise/multi',
                    table: 'authentication_enterprise',
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
                        {field: 'certificates_type', title: __('Certificates_type')},
                        {field: 'status', title: __('Status'), searchList: {"审核中":__('审核中'),"审核通过":__('审核通过'),"审核不通过":__('审核不通过')}, formatter: Table.api.formatter.status},
                        {field: 'personal_info.status', title: __('运营者审核状态'),formatter:fmt},
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

    function fmt(value,row){

        var str="";
        if(row["personal_info"]["status"]!=undefined)

         return   $str="<a target='_blank' href=\"{:$auth->check('authentication_personal/edit?id="+row["personal_info"]["id"]+"')\" title=\"{:__('Edit')}\" ><i class=\"fa fa-pencil\"></i> "+row["personal_info"]["status"]+"</a>"

        else
            return "-";
    }
    return Controller;
});
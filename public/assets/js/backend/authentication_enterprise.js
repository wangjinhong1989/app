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
                        {field: 'id', title: __('ID')},
                        {field: 'user.username', title: __('User.username')},
                        {field: 'certificates_type', title: __('Certificates_type')},
                        {field: 'status', title: __('Status'), searchList: {"审核中":__('审核中'),"审核通过":__('审核通过'),"审核不通过":__('审核不通过')}, formatter: Table.api.formatter.status},
                        {field: 'name', title: __('Name')},
                        {field: 'certificates_number', title: __('Certificates_number')},
                        {field: 'files', title: __('Files'),formatter:formart_img},
                        {field: 'note', title: __('Note')},
                        {field: 'time', title: __('Time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},


                        {field: 'personal_info.status', title: __('运营者审核状态'),formatter:fmt},
                        {field: 'personal_info.files', title: __('运营者上传文件'),  formatter: formart_img},


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

         return   $str="<a href='authentication_personal/edit?ids="+row["personal_info"]["id"]+"' class='btn btn-xs btn-primary btn-dialog' data-url='authentication_personal/edit?id="+row["personal_info"]["id"]+"'>"+row["personal_info"]["status"]+"</a>";

        else
            return "-";
    }
    return Controller;
});
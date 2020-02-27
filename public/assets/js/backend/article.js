define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'article/index' + location.search,
                    add_url: 'article/add',
                    edit_url: 'article/edit',
                    del_url: 'article/del',
                    multi_url: 'article/multi',
                    table: 'article',
                }
            });

            $(".btn-add").data("area",["900px","95%"]);
            $(".btn-edit").data("area",["900px","95%"]);
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
                        {field: 'id', title: __('Id')},
                        {field: 'title', title: __('Title')},
                        {field: 'status', title: __('Status'), searchList: {"显示":__('显示'),"隐藏":__('隐藏')}, formatter: Table.api.formatter.status},
                        {field: 'top', title: __('置顶'), searchList: {"取消置顶":__('无'),"置顶":__('置顶'),"广告":"广告","推广":"推广"}, formatter: Table.api.formatter.status},
                        // {field: 'come_from', title: __('Come_from')},
                        // {field: 'label_ids', title: __('Label_ids')},
                        // {field: 'url', title: __('Url'), formatter: Table.api.formatter.url},
                        // {field: 'img', title: __('Img'),},
                        {field: 'read_count', title: __('Read_count'),operate:false},

                        {field: 'articletype.name', title: __('Articletype.name'),operate:false},
                        {field: 'label.name', title: __('Label.name'),operate:false},
                        {field: 'user.username', title: __('User.username')},
                        {field: 'create_time_text', title: __('发布时间')},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            table.on("post-body.bs.table",function () {
                $(".btn-editone").data("area",["900px","95%"]);
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
        pageReqeust.filter["articletype.name"]="快讯,关注";
        pageReqeust.op["articletype.name"]="NOT IN";
        pageReqeust.op=JSON.stringify( pageReqeust.op );
        pageReqeust.filter=JSON.stringify( pageReqeust.filter );
        return pageReqeust;
    }
    return Controller;
});
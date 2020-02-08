define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'article_kuaixun/index' + location.search+"&articletype_id=2",
                    add_url: 'article_kuaixun/add',
                    edit_url: 'article_kuaixun/edit',
                    del_url: 'article_kuaixun/del',
                    multi_url: 'article_kuaixun/multi',
                    table: 'article_kuaixun',
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
                        {field: 'id', title: __('Id')},
                        {field: 'title', title: __('Title')},
                        {field: 'description', title: __('Description')},
                        {field: 'status', title: __('Status'), searchList: {"显示":__('显示'),"隐藏":__('隐藏')}, formatter: Table.api.formatter.status},
                        {field: 'come_from', title: __('Come_from')},
                        {field: 'label_ids', title: __('Label_ids')},
                        {field: 'url', title: __('Url'), formatter: Table.api.formatter.url},
                        {field: 'img', title: __('Img')},
                        {field: 'read_count', title: __('Read_count')},
                        {field: 'show_count', title: __('Show_count')},
                        {field: 'articletype.name', title: __('Articletype.name')},
                        {field: 'label.name', title: __('Label.name')},
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

    function queryParams(pageReqeust) {

        console.log(pageReqeust);
        pageReqeust.filter='{"articletype.name":"快讯"}';
        return pageReqeust;
    }

    return Controller;
});
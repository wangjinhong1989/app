define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'reply/index' + location.search,
                    add_url: 'reply/add',
                    edit_url: 'reply/edit',
                    del_url: 'reply/del',
                    multi_url: 'reply/multi',
                    table: 'reply',
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
                        {field: 'content', title: __('Content')},
                        {field: 'createtime', title: __('Createtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'status', title: __('Status'), searchList: {"0":__('Status 0'),"1":__('Status 1'),"2":__('Status 2')}, formatter: Table.api.formatter.status},
                        {field: 'parent_id', title: __('Parent_id')},
                        {field: 'article_id', title: __('Article_id')},
                        {field: 'user.username', title: __('User.username')},
                        {field: 'article.id', title: __('Article.id')},
                        {field: 'article.title', title: __('Article.title')},
                        {field: 'article.description', title: __('Article.description')},
                        {field: 'article.create_time', title: __('Article.create_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'article.status', title: __('Article.status'), formatter: Table.api.formatter.status},
                        {field: 'article.user_id', title: __('Article.user_id')},
                        {field: 'article.articletype_ids', title: __('Article.articletype_ids')},
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
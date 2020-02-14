define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'banner/index' + location.search,
                    add_url: 'banner/add',
                    edit_url: 'banner/edit',
                    del_url: 'banner/del',
                    multi_url: 'banner/multi',
                    table: 'banner',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'weigh',
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'weigh', title: __('Weigh')},
                        {field: 'title', title: __('名称')},
                        {field: 'bannername_id', title: __('Bannername_id')},
                        {field: 'img', title: __('Img'),formatter:formart_img},
                        {field: 'top', title: __('Top'), searchList: {"置顶":__('置顶'),"取消置顶":__('取消置顶')}, formatter: Table.api.formatter.normal},
                        {field: 'begin_time', title: __('Begin_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'end_time', title: __('End_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'url', title: __('Url'), formatter: Table.api.formatter.url},
                        {field: 'article_id', title: __('Article_id')},
                        {field: 'url_type', title: __('Url_type'), searchList: {"内链":__('内链'),"外链":__('外链')}, formatter: Table.api.formatter.normal},
                        {field: 'status', title: __('Status'), searchList: {"显示":__('显示'),"隐藏":__('隐藏')}, formatter: Table.api.formatter.status},
                        {field: 'bannername.id', title: __('Bannername.id')},
                        {field: 'bannername.name', title: __('Bannername.name')},
                        {field: 'article.id', title: __('Article.id')},
                        {field: 'article.title', title: __('Article.title')},
                        {field: 'article.description', title: __('Article.description')},
                        {field: 'article.create_time', title: __('Article.create_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'article.status', title: __('Article.status'), formatter: Table.api.formatter.status},
                        {field: 'article.user_id', title: __('Article.user_id')},
                        {field: 'article.articletype_id', title: __('Article.articletype_id')},
                        {field: 'article.come_from', title: __('Article.come_from')},
                        {field: 'article.label_ids', title: __('Article.label_ids')},
                        {field: 'article.url', title: __('Article.url'), formatter: Table.api.formatter.url},
                        {field: 'article.img', title: __('Article.img')},
                        {field: 'article.read_count', title: __('Article.read_count')},
                        {field: 'article.show_count', title: __('Article.show_count')},
                        {field: 'article.is_reply', title: __('Article.is_reply')},
                        {field: 'article.is_mine', title: __('Article.is_mine')},
                        {field: 'article.is_recommendation', title: __('Article.is_recommendation')},
                        {field: 'article.top', title: __('Article.top')},
                        {field: 'article.begin_time', title: __('Article.begin_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'article.end_time', title: __('Article.end_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'article.weigh', title: __('Article.weigh')},
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
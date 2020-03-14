define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form,bannerList) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'banner_search/index' + location.search,
                    add_url: 'banner_search/add',
                    edit_url: 'banner_search/edit',
                    del_url: 'banner_search/del',
                    multi_url: 'banner_search/multi',
                    table: 'banner_search',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'weigh',
                sortOrder:"asc",
                queryParams:queryParams,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('ID')},
                        {field: 'title', title: __('广告名称')},
                        {field: 'weigh', title: __('排序')},
                        {field: 'img', title: __('图片'),formatter:formart_img},
                        {field: 'status', title: __('状态'), searchList: {"显示":__('显示'),"隐藏":__('隐藏')}, formatter: Table.api.formatter.status},


                        {field: 'url_type', title: __('链接类型'), searchList: {"内链":__('内链'),"外链":__('外链')}, formatter: Table.api.formatter.normal},
                        {field: 'url', title: __('链接地址'), formatter: Table.api.formatter.url},
                        {field: 'begin_time', title: __('开始时间'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'end_time', title: __('结束时间'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},

                        {field: 'article.title', title: __('文章标题')},

                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: Table.api.formatter.operate}
                    ]
                ]
            });

            // 为表格绑定事件
            Table.api.bindevent(table);


        },
        add: function () {
            $("#c-article_id").data("params", function (obj) {
                return {custom: {articletype_id: ["NOT IN",2]}}
            });
            Controller.api.bindevent();
        },
        edit: function () {
            $("#c-article_id").data("params", function (obj) {
                return {custom: {articletype_id: ["NOT IN",2]}}
            });
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
        pageReqeust.filter["bannername.name"]="搜索页";
        pageReqeust.op["bannername.name"]="=";
        pageReqeust.op=JSON.stringify( pageReqeust.op );
        pageReqeust.filter=JSON.stringify( pageReqeust.filter );
        return pageReqeust;
    }
    return Controller;
});
define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form,bannerList) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'banner_index/index' + location.search,
                    add_url: 'banner_index/add',
                    edit_url: 'banner_index/edit',
                    del_url: 'banner_index/del',
                    multi_url: 'banner_index/multi',
                    table: 'banner',
                }
            });

            var table = $("#table");

            // 初始化表格
            table.bootstrapTable({
                url: $.fn.bootstrapTable.defaults.extend.index_url,
                pk: 'id',
                sortName: 'weigh',
                queryParams:queryParams,
                columns: [
                    [
                        {checkbox: true},
                        {field: 'id', title: __('Id')},
                        {field: 'title', title: __('名称')},
                        {field: 'bannername.name', title: __('位置')},
                        {field: 'img', title: __('图片'),formatter:formart_img},
                        {field: 'top', title: __('置顶'), searchList: {"置顶":__('置顶'),"取消置顶":__('取消置顶')}, formatter: Table.api.formatter.normal},
                        {field: 'begin_time', title: __('开始时间'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'end_time', title: __('结束时间'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'url', title: __('链接地址'), formatter: Table.api.formatter.url},
                        {field: 'url_type', title: __('链接类型'), searchList: {"内链":__('内链'),"外链":__('外链')}, formatter: Table.api.formatter.normal},
                        {field: 'status', title: __('状态'), searchList: {"显示":__('显示'),"隐藏":__('隐藏')}, formatter: Table.api.formatter.status},
                        {field: 'article.title', title: __('文章标题')},
                        {field: 'weigh', title: __('排序')},
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
        pageReqeust.filter["bannername.name"]="首页";
        pageReqeust.op["bannername.name"]="=";
        pageReqeust.op=JSON.stringify( pageReqeust.op );
        pageReqeust.filter=JSON.stringify( pageReqeust.filter );
        return pageReqeust;
    }
    return Controller;
});
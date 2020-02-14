define(['jquery', 'bootstrap', 'backend', 'table', 'form'], function ($, undefined, Backend, Table, Form) {

    var Controller = {
        index: function () {
            // 初始化表格参数配置
            Table.api.init({
                extend: {
                    index_url: 'yingdao/index' + location.search,
                    add_url: 'yingdao/add',
                    edit_url: 'yingdao/edit',
                    del_url: 'yingdao/del',
                    multi_url: 'yingdao/multi',
                    table: 'yingdao',
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
                        {field: 'files', title: __('Files'),formatter:formart_img},
                        {field: 'url', title: __('Url'), formatter: Table.api.formatter.url},
                        {field: 'top', title: __('Top'), searchList: {"置顶":__('置顶'),"取消置顶":__('取消置顶')}, formatter: Table.api.formatter.normal},
                        {field: 'begin_time', title: __('Begin_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'end_time', title: __('End_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
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

    function formart_img(images) {
    //<a href="javascript:"><img class="img-sm img-center" src="/uploads/20200212/e4b9b6a941e446074555d3b0eb5dad72.jpg,/uploads/20200212/e4b9b6a941e446074555d3b0eb5dad72.jpg,/uploads/20200212/e4b9b6a941e446074555d3b0eb5dad72.jpg,/uploads/20200212/e4b9b6a941e446074555d3b0eb5dad72.jpg"></a>

        var imagesArr=images.split(",");
        var str="";
        for (i=0;i<imagesArr.length;i++){
            str=str+'<a href="javascript:"><img class="img-sm img-center" src="'+imagesArr[i]+'"></a>';
        }

        return str;

    }
    return Controller;
});
define(['jquery', 'bootstrap', 'frontend', 'table', 'form'], function ($, undefined, Frontend, Table, Form) {

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
                        {field: 'title', title: __('Title')},
                        {field: 'description', title: __('Description')},
                        {field: 'create_time', title: __('Create_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        {field: 'status', title: __('Status'), searchList: {"0":__('Status 0'),"1":__('Status 1')}, formatter: Table.api.formatter.status},
                        // {field: 'user_id', title: __('User_id')},
                        // {field: 'articletype_ids', title: __('Articletype_ids')},
                        //{field: 'articletype.id', title: __('Articletype.id')},
                        {field: 'articletype.name', title: __('Articletype.name'),formatter: function(index,row){


                            var str="";
                            $(row.articletype).each(function(){
                                str=str+" "+$(this).name
                            });
                            return str;
                        }},
                        // {field: 'articletype.status', title: __('Articletype.status'), formatter: Table.api.formatter.status},
                        // {field: 'articletype.create_time', title: __('Articletype.create_time'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        // {field: 'user.id', title: __('User.id')},
                        // {field: 'user.group_id', title: __('User.group_id')},
                        {field: 'user.username', title: __('User.username')},
                        // {field: 'user.nickname', title: __('User.nickname')},
                        // {field: 'user.password', title: __('User.password')},
                        // {field: 'user.salt', title: __('User.salt')},
                        // {field: 'user.email', title: __('User.email')},
                        // {field: 'user.mobile', title: __('User.mobile')},
                        {field: 'user.avatar', title: __('User.avatar'), events: Table.api.events.image, formatter: Table.api.formatter.image},
                        // {field: 'user.level', title: __('User.level')},
                        // {field: 'user.gender', title: __('User.gender')},
                        // {field: 'user.birthday', title: __('User.birthday'), operate:'RANGE', addclass:'datetimerange'},
                        // {field: 'user.bio', title: __('User.bio')},
                        // {field: 'user.money', title: __('User.money'), operate:'BETWEEN'},
                        // {field: 'user.score', title: __('User.score')},
                        // {field: 'user.successions', title: __('User.successions')},
                        // {field: 'user.maxsuccessions', title: __('User.maxsuccessions')},
                        // {field: 'user.prevtime', title: __('User.prevtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        // {field: 'user.logintime', title: __('User.logintime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        // {field: 'user.loginip', title: __('User.loginip')},
                        // {field: 'user.loginfailure', title: __('User.loginfailure')},
                        // {field: 'user.joinip', title: __('User.joinip')},
                        // {field: 'user.jointime', title: __('User.jointime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        // {field: 'user.createtime', title: __('User.createtime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        // {field: 'user.updatetime', title: __('User.updatetime'), operate:'RANGE', addclass:'datetimerange', formatter: Table.api.formatter.datetime},
                        // {field: 'user.token', title: __('User.token')},
                        // {field: 'user.status', title: __('User.status'), formatter: Table.api.formatter.status},
                        // {field: 'user.verification', title: __('User.verification')},
                        {field: 'operate', title: __('Operate'), table: table, events: Table.api.events.operate, formatter: function(index,row){

                            return '<a href="/index/article/edit?id='+row.id+'">编辑</a>';
                        }}
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

define(['nkeditor-core'], function (Nkeditor) {
    Nkeditor.plugin('multiimage', function (K) {
        var self = this, name = 'multiimage', lang = self.lang(name + '.'),
            allowImages = K.undef(self.allowImages, false);

        var click = function () {

            var html = [
                '<div class="ke-dialog-content-inner">',
                '<div class="ke-dialog-row ke-clearfix">',
                '<div class=""><div class="ke-inline-block ke-upload-button">' +
                '<form class="ke-upload-area ke-form nice-validator n-default" method="post" enctype="multipart/form-data" style="width: 266px;margin:50px auto;">' +
                '<span class="ke-button-common"><input type="button" class="ke-button-common ke-button" value="批量上传图片" style="width:128px;"></span><input type="file" class="ke-upload-file" name="imgFiles" multiple style="width:128px;left:0;right:inherit" tabindex="-1">' +
                '<span class="ke-button-common" style="margin-left:10px;"><input type="button" class="ke-button-common ke-button ke-select-image" style="width:128px;" value="从图片空间选择"></span>' +
                '</form>' +
                '</div></span></div>',
                '</div>',
                '</div>'
            ].join('');
            var dialog = self.createDialog({
                    name: name,
                    width: 450,
                    height: 260,
                    title: self.lang(name),
                    body: html,
                    noBtn: {
                        name: self.lang('no'),
                        click: function (e) {
                            self.hideDialog().focus();
                        }
                    }
                }),
                div = dialog.div;
            $("input[name=imgFiles]", div).change(function () {
                dialog.showLoading();
                var files = $(this).prop('files');
                $.each(files, function (i, file) {
                    self.beforeUpload.call(self, function (data) {
                        self.exec('insertimage', Fast.api.cdnurl(data.data.url));
                    }, file);
                });
                setTimeout(function () {
                    self.hideDialog().focus();
                }, 0);
            });
            $(".ke-select-image", div).click(function () {
                self.loadPlugin('filemanager', function () {
                    self.plugin.filemanagerDialog({
                        dirName: 'image',
                        multiple: true,
                        clickFn: function (urls) {
                            $.each(urls, function(i, url){
                                self.exec('insertimage', url);
                            });
                        }
                    });
                });
                self.hideDialog().focus();
                // parent.Fast.api.open("general/attachment/select?element_id=&multiple=true&mimetype=*", __('Choose'), {
                //     callback: function (data) {
                //         var urlArr = data.url.split(/\,/);
                //         $.each(urlArr, function () {
                //             var url = Fast.api.cdnurl(this);
                //             self.exec('insertimage', url);
                //         });
                //     }
                // });
            });
        };
        self.clickToolbar(name, click);
    });


    KindEditor.plugin('media', function(K) {
        var self = this, name = 'media', lang = self.lang(name + '.'),
            allowMediaUpload = K.undef(self.allowMediaUpload, false),
            allowFileManager = K.undef(self.allowFileManager, false),
            formatUploadUrl = K.undef(self.formatUploadUrl, true),
            extraParams = K.undef(self.extraFileUploadParams, {}),
            filePostName = K.undef(self.filePostName, 'imgFile'),
            uploadJson = K.undef(self.uploadJson, self.basePath + 'php/upload_json.php');

        self.plugin.media = {
            edit : function() {
                var html = [
                    '<div class="ke-dialog-content-inner">',
                    //url
                    '<div class="ke-dialog-row ke-clearfix">',
                    '<label for="keUrl" class="row-left">' + lang.url + '：</label>',
                    '<div class="row-right">',
                    '<input class="ke-input-text" type="text" id="keUrl" name="url" value="" style="width:180px;" /> &nbsp;',
                    '<input type="button" class="ke-upload-button" value="' + lang.upload + '" /> &nbsp;',
                    '<span class="ke-button-common ke-button-outer">',
                    '<input type="button" class="ke-button-common ke-button" name="viewServer" value="' + lang.viewServer + '" />',
                    '</span>',
                    '<div>支持优酷、爱奇艺、土豆、腾讯视频、56等视频网站【<span style="color:green">通用代码</span>】',
                    '</div>',
                    '</div>',
                    '</div>',
                    //width
                    '<div class="ke-dialog-row ke-clearfix">',
                    '<label for="keWidth" class="row-left">' + lang.width + '：</label>',
                    '<div class="row-right">',
                    '<input type="text" id="keWidth" class="ke-input-text ke-input-number" name="width" value="550" maxlength="4" />',
                    '</div>',
                    '</div>',
                    //height
                    '<div class="ke-dialog-row ke-clearfix">',
                    '<label for="keHeight" class="row-left">' + lang.height + '：</label>',
                    '<div class="row-right">',
                    '<input type="text" id="keHeight" class="ke-input-text ke-input-number" name="height" value="400" maxlength="4" />',
                    '</div>',
                    '</div>',
                    //autostart
                    '<div class="ke-dialog-row ke-clearfix">',
                    '<label for="keAutostart" class="row-left">' + lang.autostart + '：</label>',
                    '<div class="row-right">',
                    '<input type="checkbox" id="keAutostart" name="autostart" class="ke-input-checkbox" value="" /> ',
                    '</div>',
                    '</div>',
                    '</div>'
                ].join('');
                var dialog = self.createDialog({
                        name : name,
                        width : 450,
                        height : 260,
                        title : self.lang(name),
                        body : html,
                        yesBtn : {
                            name : self.lang('yes'),
                            click : function(e) {
                                var url = K.trim(urlBox.val()),
                                    width = widthBox.val(),
                                    height = heightBox.val();
                                var match = url.match(/^<iframe\s(.*?)src=('|")(.*?)('|")/);

                                console.log(match,"aaaa")

                                if (!match) {
                                    console.log(match,"bbb")
                                    if (url == 'http://' || K.invalidUrl(url)) {
                                        console.log(match,"ccc")
                                        K.options.errorMsgHandler(self.lang('invalidUrl'), "error");
                                        urlBox[0].focus();
                                        return;
                                    }
                                }

                                //  这个位置哟.

                                var youkuRegExp = /\/\/v\.youku\.com\/v_show\/id_(\w+)=*\.html/;
                                var youkuMatch = url.match(youkuRegExp);
                                var qqRegExp = /\/\/v\.qq\.com.*?vid=(.+)/;
                                var qqMatch = url.match(qqRegExp);
                                var qqRegExp2 = /\/\/v\.qq\.com\/x?\/?(page|cover).*?\/([^\/]+)\.html\??.*/;
                                var qqMatch2 = url.match(qqRegExp2);

                                if (!/^\d*$/.test(width)) {
                                    K.options.errorMsgHandler(self.lang('invalidWidth'), "error");
                                    widthBox[0].focus();
                                    return;
                                }
                                if (!/^\d*$/.test(height)) {
                                    K.options.errorMsgHandler(self.lang('invalidHeight'), "error");
                                    heightBox[0].focus();
                                    return;
                                }
                                console.log(youkuMatch,"111")
                                console.log(qqMatch,"222")
                                console.log(qqMatch2,"xxx")
                                if (!match) {

                                    console.log(youkuMatch,"111")
                                    console.log(qqMatch,"222")
                                    console.log(qqMatch2,"xxx")

                                    if (youkuMatch && youkuMatch[1].length) {
                                        var html='<iframe frameborder="0" height="498" width="510" src="//player.youku.com/embed/'+ youkuMatch[1]+'" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>'
                                    }
                                    else if ((qqMatch && qqMatch[1].length) || (qqMatch2 && qqMatch2[2].length)) {
                                        var vid = ((qqMatch && qqMatch[1].length) ? qqMatch[1] : qqMatch2[2]);

                                        var html='<iframe frameborder="0" height="310" width="510" src="https://v.qq.com/iframe/player.html?vid='+ vid+'&amp;auto=0" webkitallowfullscreen mozallowfullscreen allowfullscreen></iframe>'
                                    }else{
                                        var html = K.mediaImg(self.themesPath + 'common/blank.gif', {
                                            src: url,
                                            type: K.mediaType(url),
                                            width: width,
                                            height: height,
                                            autostart: autostartBox[0].checked ? 'true' : 'false',
                                            loop: 'true'
                                        });
                                    }

                                }
                                else {
                                    var html = '<iframe src="' + match[3] + '" frameborder="0" style="width:' + width + 'px;height:' + height + 'px;"></iframe>';
                                }
                                self.insertHtml(html).hideDialog().focus();

                            }
                        }
                    }),
                    div = dialog.div,
                    urlBox = K('[name="url"]', div),
                    viewServerBtn = K('[name="viewServer"]', div),
                    widthBox = K('[name="width"]', div),
                    heightBox = K('[name="height"]', div),
                    autostartBox = K('[name="autostart"]', div);
                urlBox.val('http://');

                if (allowMediaUpload) {
                    var uploadbutton = K.uploadbutton({
                        button : K('.ke-upload-button', div)[0],
                        fieldName : filePostName,
                        extraParams : extraParams,
                        url : K.addParam(uploadJson, 'fileType=media'),
                        afterUpload : function(data) {
                            dialog.hideLoading();
                            if (data.code == "000") {
                                var url = data.data.url;
                                if (formatUploadUrl) {
                                    url = K.formatUrl(url, 'absolute');
                                }
                                urlBox.val(url);

                                if (self.afterUpload) {
                                    self.afterUpload.call(self, url, data, name);
                                }
                                K.options.errorMsgHandler(self.lang('uploadSuccess'), "ok");
                            } else {
                                K.options.errorMsgHandler(data.message, "error", "error");
                            }
                        },
                        afterError : function(html) {
                            dialog.hideLoading();
                            self.errorDialog(html);
                        }
                    });
                    uploadbutton.fileBox.change(function(e) {
                        dialog.showLoading(self.lang('uploadLoading'));
                        uploadbutton.submit();
                    });
                } else {
                    K('.ke-upload-button', div).hide();
                }

                if (allowMediaUpload && allowFileManager) {
                    viewServerBtn.click(function(e) {
                        self.loadPlugin('filemanager', function() {
                            self.plugin.filemanagerDialog({
                                dirName : 'media',
                                clickFn : function(url) {
                                    K('[name="url"]', div).val(url);
                                    if (self.afterSelectFile) {
                                        self.afterSelectFile.call(self, url);
                                    }
                                }
                            });
                        });
                    });
                } else {
                    K("#keUrl").css("width", "280px");
                    viewServerBtn.hide();
                }

                var img = self.plugin.getSelectedMedia();
                if (img) {
                    var attrs = K.mediaAttrs(img.attr('data-ke-tag'));
                    urlBox.val(attrs.src);
                    widthBox.val(K.removeUnit(img.css('width')) || attrs.width || 0);
                    heightBox.val(K.removeUnit(img.css('height')) || attrs.height || 0);
                    autostartBox[0].checked = (attrs.autostart === 'true');
                }
                urlBox[0].focus();
                urlBox[0].select();
            },
            'delete' : function() {
                self.plugin.getSelectedMedia().remove();
                // [IE] 删除图片后立即点击图片按钮出错
                self.addBookmark();
            }
        };
        self.clickToolbar(name, self.plugin.media.edit);
    });

    return Nkeditor;
});

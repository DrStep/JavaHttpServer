/*
 * Источник: http://pl.wikipedia.org/wiki/MediaWiki:Wikibugs.js
 * Адаптация под русский: [[User:Александр Сигачёв]], [[User:Putnik]], [[User:LEMeZza]]
 */

window.wb$bugsPage = 'Википедия:Сообщения об ошибках';
window.wb$badPages = [
    'Википедия:Сообщения об ошибках',
    'Заглавная страница'
];
window.wb$i18n = {
    ns_file: 'Файл:',
    ns_special: 'Служебная:',
    ns_cat: 'Категория:',
    btn_fix: 'Исправить самостоятельно',
    btn_report: 'Сообщить об ошибке',
    btn_cancel: 'Отмена',
    btn_send: 'Отправить',
    fld_page: 'Название страницы:',
    fld_text: 'Текст сообщения:',
    fld_text_info: 'Пожалуйста, опишите ошибку как можно точнее. При сообщении о фактической ошибке не забудьте указать источник, подтверждающий вашу информацию.',
    fld_captcha: 'Проверочный код:',
    fld_sign: 'Подпись:',
    alert_short: 'Описание ошибки слишком коротко. Пожалуйста, расширьте его.',
    alert_nopage: 'Введите имя страницы.',
    alert_captcha: 'В вашем тексте содержатся внешние ссылки. Пожалуйста, введите код с изображения и отправьте сообщение ещё раз.',
    alert_error: 'При отправке произошла ошибка. Попробуйте ещё раз.',
    msg_sign: '\n\nАвтор сообщения:',
    new_topic: 'новая тема',
    html_ip_warn: '<strong>Внимание.</strong> Ваш IP-адрес будет записан в журнал изменений страницы.',
    html_info: '<div style="float:right;width:200px;padding:4px 10px;margin:2px 0px 0px 10px;font-size:90%;border:2px solid #a6170f;border-radius:3px">\
        <p><strong>Не\u00A0сообщайте</strong> об\u00A0ошибках на\u00A0других сайтах (например, <strong>«В\u00A0Контакте»</strong> или <strong>«Одноклассники»</strong>), они будут проигнорированы.</p>\
        <p>Отсутствие статьи в\u00A0Википедии\u00A0— не\u00A0ошибка, вы можете оставить <a href="' + wb$link('Википедия:К созданию') + '">запрос на её создание</a>.</p></div>\
        <p style="margin-top:0px">Если вы заметили ошибку в\u00A0Википедии, пожалуйста, исправьте её самостоятельно, используемая на\u00A0этом сайте технология <a href="' + wb$link('вики') + '">вики</a> позволяет это сделать. Не\u00A0смущайтесь, одно из\u00A0правил Википедии гласит: «<a href="' + wb$link('Википедия:Правьте смело') + '">Правьте смело</a>»! Если вы не\u00A0можете исправить ошибку самостоятельно, сообщите о\u00A0ней с\u00A0помощью данной формы.</p><p><strong>Если ошибка уже исправлена\u00A0— не\u00A0сообщайте о\u00A0ней.</strong></p><p>Не\u00A0оставляйте свой телефон и/или электронный адрес, ответ на\u00A0сообщение будет дан только на\u00A0странице с\u00A0сообщениями и нигде больше.</p>\
        <ul><li><a href="' + wb$link(wb$bugsPage) + '">Текущий список сообщений об ошибках.</a></li></ul>\
        ',
};

function wb$link(page)
{
    'use strict';
    return window.wgArticlePath.replace(/\$1/, page.replace(/ /g, '_'));
}

function wb$isValidPageName(name)
{
    'use strict';
    if (!name || name.substr(0, name.indexOf(':') + 1) === wb$i18n.ns_special) {
        return false;
    }
    name = name.replace(/_/g, ' ');
    for (var i = 0; i < wb$badPages.length; i++) {
        if (name === wb$badPages[i]) {
            return false;
        }
    }

    return true;
}

function wb$popWikibug()
{
    'use strict';

    // Background
    var nel = $('<div id="wikibugs-globhidden">');
    nel.css({
        'background': '#000',
        'filter': 'alpha(opacity=75)',
        'opacity': '0.75',
        'position': 'absolute',
        'left': '0',
        'top': '0',
        'z-index': '2000',
        'width': document.documentElement.scrollWidth + 'px',
        'height': document.documentElement.scrollHeight + 'px'
    });
    $('body').append(nel);

    // Scroll to top 
    window.scroll(0, 150);

    // Info popup
    var can_edit = false,
        edit_a = $('#ca-edit a');
    if (edit_a.length) {
        can_edit = true;
    }

    nel = $('<div id="wikibugs-info">');
    nel.css({
        'font-size': '13px',
        'background': 'white',
        'padding': '21px 30px',
        'border': '1px solid #2f6fab',
        'border-radius': '3px',
        'position': 'absolute',
        'min-height': '300px',
        'width': '500px',
        'margin-left': '-250px',
        'top': '200px',
        'left': '50%',
        'z-index': '2002'
    });
    var infoHTML = wb$i18n.html_info;
    if (!window.wgUserName) {
        infoHTML += '<p>' + wb$i18n.html_ip_warn + '</p>';
    }
    infoHTML += '<p style="margin-top:15px">\
        <input type="button" class="wikibugs-cancel mw-ui-button" style="float:right;color:#555;border-color:#aaa" value="' + wb$i18n.btn_cancel + '" />\
        ' + (can_edit ? '<input id="wikibugs-edit" type="button" class="mw-ui-button mw-ui-primary" style="margin:1px 5px 5px 0" value="' + wb$i18n.btn_fix + '" />' : '') + '\
        <input id="wikibugs-report" type="button" class="mw-ui-button mw-ui-primary" style="margin:1px 0 5px" value="' + wb$i18n.btn_report + '" />\
        </p>';
    nel.html(infoHTML);
    $('body').append(nel);

    // Go to report form
    $('#wikibugs-report').on('click', function()
    {
        $('#wikibugs-info').hide();
        $('#wikibugs-form').show();
    });

    // Go to edit page
    $('#wikibugs-edit').on('click', function(e)
    {
        e.preventDefault();
        var edit_a = $('#ca-edit a'),
            edit_href = window.wgArticlePath.replace(/\$1/, wb$bugsPage);
        if (edit_a.length) {
            edit_href = edit_a.attr('href');
        }
        window.location.assign(edit_href);
    });

    // Popup with report form
    nel = $('<div id="wikibugs-form">');
    nel.css({
        'display': 'none',
        'background': 'white',
        'padding': '15px 20px',
        'border': '1px solid #2f6fab',
        'border-radius': '3px',
        'position': 'absolute',
        'min-height': '300px',
        'width': '330px',
        'margin-left': '-165px',
        'top': '200px',
        'left': '50%',
        'z-index': '2001'
    });
    nel.html('<form id="wikibugs-form" class="mw-ui-vform" style="width:330px">\
        <div>' + wb$i18n.fld_page + '\
            <input id="wikibugs-page" type="text" class="mw-ui-input" />\
        </div>\
        <div>' + wb$i18n.fld_text + '\
            <textarea id="wikibugs-text" class="mw-ui-input" style="width:100%;height:200px" placeholder="' + wb$i18n.fld_text_info + '"></textarea>\
        </div>\
        <div id="wikibugs-captcha" style="display:none">' + wb$i18n.fld_captcha + '\
            <input id="wikibugs-captcha-id" type="hidden" />\
            <input id="wikibugs-captcha-word" type="text" class="mw-ui-input" />\
            <img src="" width="249" height="63" />\
        </div>\
        <div>' + wb$i18n.fld_sign + '\
            <input id="wikibugs-sign" type="text" class="mw-ui-input" />\
        </div>\
        <input type="button" class="wikibugs-cancel mw-ui-button" style="float:right;color:#555;border-color:#aaa" value="' + wb$i18n.btn_cancel + '" />\
        <input id="wikibugs-submit" type="submit" class="mw-ui-button mw-ui-primary" style="width:220px;margin-top:1px" value="' + wb$i18n.btn_send + '" />\
        </form>');
    $('body').append(nel);

    // Send message
    nel.on('submit', function(e)
    {
        e.preventDefault();

        var content = $('#wikibugs-text').val();
        if (content === '' || content.length < 20 || !content.match(' ')) {
            mw.notify(wb$i18n.alert_short);
            $('#wikibugs-text').focus();
            return;
        }

        var page = $('#wikibugs-page').val()
                .replace(/^https?:\/\/ru\.wikipedia\.org\/wiki\/(.+)$/, '$1')
                .replace(/_/g, ' ');
        page = decodeURIComponent(page);

        var section;

        if (page === window.wgPageName.replace(/_/g, ' ') && wb$isValidPageName(window.wgPageName)) {
            if (window.wgNamespaceNumber === 6) {
                section = '[[:' + wb$i18n.ns_file + window.wgTitle + '|' + window.wgTitle + ']]';
                content = '[[' + wb$i18n.ns_file + window.wgTitle + '|thumb|left|100px]]\n* ' + content + '\n{{clear}}';
            }
            else {
                var re = new RegExp('^('+ wb$i18n.ns_cat + '|'+ wb$i18n.ns_file + '|\\/)');
                section = page.replace(re, ':$1');
                section = '[[' + section + ']]';
            }
        }
        else {
            page = page
                .replace(/\[\[([^\[\]\|]+)\|[^\[\]\|]+\]\]/g, '$1')
                .replace(/[\[\]\|]/g, '')
                .replace(/^\s+/g, '')
                .replace(/\s+$/g, '');

            if (!wb$isValidPageName(page)) {
                mw.notify(wb$i18n.alert_nopage);
                if (wb$isValidPageName(window.wgPageName)) {
                    $('#wikibugs-page').val(window.wgPageName);
                }
                else {
                    $('#wikibugs-page')
                        .val('')
                        .focus();
                }
                return;
            }
            if (page.indexOf(':') > 0) {
                section = '[[:' + page + ']]';
            }
            else {
                section = '[[' + page + ']]';
            }
        }

        content += wb$i18n.msg_sign;
        if (!window.wgUserName) {
            content += ' ' + $('#wikibugs-sign').val().trim();
        }
        content += ' ~~' + '~~';

        $('#wikibugs-submit').prop('disabled', true);

        var data = {
            format: 'json',
            action: 'edit',
            title: wb$bugsPage,
            section: 'new',
            sectiontitle: section,
            summary: '/* ' + page + ' */ ' + wb$i18n.new_topic,
            text: content.trim(),
            token: mw.user.tokens.values.editToken
        };
        var captcha_id = $('#wikibugs-captcha-id').val();
        if (captcha_id) {
            data.captchaid = captcha_id;
            data.captchaword = $('#wikibugs-captcha-word').val().trim();
        }

        $.ajax({
            url: '/w/api.php',
            type: 'POST',
            data: data,
            success: function(xhr)
            {
                // Success
                if (xhr && xhr.edit && xhr.edit.result === 'Success') {
                    var url = window.wgArticlePath
                        .replace(/\$1/, wb$bugsPage)
                        .replace(/ /g, '_');
                    window.location.href = url + '#' + page;
                }
                // Captcha
                else if (xhr && xhr.edit && xhr.edit.captcha && xhr.edit.captcha.type === 'image') {
                    $('#wikibugs-captcha img').attr('src', xhr.edit.captcha.url);
                    $('#wikibugs-captcha-id').val(xhr.edit.captcha.id);
                    $('#wikibugs-captcha-word').val('');
                    $('#wikibugs-captcha').show();
                    $('#wikibugs-submit').prop('disabled', false);
                    mw.notify(wb$i18n.alert_captcha);
                }
                // Error
                else {
                    $('#wikibugs-submit').prop('disabled', false);
                    mw.notify(wb$i18n.alert_error);
                }
            },
            error: function()
            {
                $('#wikibugs-submit').prop('disabled', false);
                mw.notify(wb$i18n.alert_error);
            }
        });
    });

    // Cancel
    $('.wikibugs-cancel').on('click', function(e)
    {
        e.preventDefault();
        $('#wikibugs-info, #wikibugs-form, #wikibugs-globhidden').remove();
    });

    $('#wikibugs-page').val(window.wgPageName.replace(/_/g, ' '));

    // Disable title changes for main namespace
    if (wb$isValidPageName(window.wgPageName) && !window.wgNamespaceNumber) {
        $('#wikibugs-page')
            .prop('disabled', true)
            .css('background', '#eee');
    }

    if (window.wgUserName) {
        $('#wikibugs-sign')
            .val('~~' + '~~')
            .prop('disabled', true)
            .css('background', '#eee');
    }
}

addOnloadHook(function()
{
    'use strict';
    $('#n-bug_in_article a').click(function(e)
    {
        e.preventDefault();
        mw.loader.using('mediawiki.ui', wb$popWikibug);
    });
});
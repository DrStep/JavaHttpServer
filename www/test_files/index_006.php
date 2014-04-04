$(function(){
  var el = addPortletLink('p-lang', wgArticlePath.replace(/\$1/, 'Википедия:Список_Википедий'), 'Полный список', 'interwiki-completelist');
  if (el) {
    el.style.fontWeight = 'bold';
  }
  $('#searchInput').focus();
});

appendCSS('#t-cite, #catlinks, #lastmod, #footer-info-lastmod {display:none}');
appendCSS('.globegris {background:\
url(//upload.wikimedia.org/wikipedia/commons/1/10/Wikipedia-logo-v2-200px-transparent.png)}');
appendCSS('.wbc-editpage {display:none}'); //[[mediazilla:45037]]
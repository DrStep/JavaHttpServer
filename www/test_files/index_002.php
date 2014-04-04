var addRelatedSites = function() {
	if (typeof(wgWikibaseItemId) === 'undefined' || wgAction !== 'view' || wgNamespaceNumber % 2) {
		return;
	}

	var p_lang = $('#p-lang');
	if (!p_lang.length) {
		return;
	}

	$.ajax({
		url: '//www.wikidata.org/w/api.php',
		data: {
			'format': 'json',
			'action': 'wbgetentities',
			'props': 'sitelinks|claims',
			'ids': wgWikibaseItemId
		},
		dataType: 'jsonp',
		success: function (data) {
			if (data.success) {
				for (var i in data.entities) {
					if (i == -1) {
						return;
					}
					var p_rs = p_lang.clone().attr('id', 'p-relatedsites'),
						p_rs_list = p_rs.find('ul').html('');

					var add_link = function(label, site, page) {
						var url = '//';
						if (site == 'wikidata') {
							url += 'www.' + site;
						}
						else if (site == 'commons') {
							url += site + '.wikimedia';
						}
						else {
							url += 'ru.' + site;
						}
						url += '.org/wiki/' + page.replace(/ /g, '_');
						var li = $('<li class="interlanguage-link interwiki-' + site + '">');
						li.append('<a href="' + url + '" title="' + label + ': ' + page + '">' + label + '</a>');
						li.appendTo(p_rs_list);
					};

					// Vector
					if ($('body').hasClass('skin-vector')) {
						p_rs.attr('aria-labelledby', 'p-relatedsites-label');
						p_rs_list.attr('id', 'p-relatedsites-list');
						p_rs.find('h3').attr('id', 'p-relatedsites-label');
						p_rs.find('h3>a')
							.attr({
								'aria-controls': 'p-relatedsites-list',
								'aria-expanded': 'true'
							})
							.text('В других проектах')
							.click(function(e) {
								e.preventDefault();
							});

						var p_lang_label = p_lang.find('h3');
						if (p_lang_label.attr('tabindex')) {
							p_lang_label.attr('tabindex', parseInt(p_lang_label.attr('tabindex'), 10) + 1);
						}
					}
					// Monobook & Modern
					else {
						p_rs.find('h3').text('В других проектах');
					}

					var links = data.entities[i].sitelinks;
					add_link('Викиданные', 'wikidata', wgWikibaseItemId);
					$('#t-wikibase').hide();

					var claims = data.entities[i].claims;
					if (claims && claims.P373 && claims.P373[0] && claims.P373[0].mainsnak.datavalue) {
						add_link('Викисклад', 'commons', 'Category:' + claims.P373[0].mainsnak.datavalue.value);
					}

					for (var proj in links) {
						if (proj == 'commonswiki') {
							if (!p_rs_list.find('.interwiki-commons').length) {
								add_link('Викисклад', 'commons', links[proj].title);
							}
						}
						else if (proj == 'ruwikisource') {
							add_link('Викитека', 'wikisource', links[proj].title);
						}
						else if (proj == 'ruwikivoyage') {
							add_link('Викигид', 'wikivoyage', links[proj].title);
						}
					}

					if (p_rs_list.children().length) {
						p_rs.insertBefore(p_lang);
					}
				}
			}
		}
	});
};

if (window.jQuery) {
	window.jQuery(document).ready(addRelatedSites);
}
else {
	addOnloadHook(addRelatedSites);
}
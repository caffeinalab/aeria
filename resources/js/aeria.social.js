window.Aeria = window.Aeria || {};
Aeria.social = (function(){
	var self = {};
	var $ = jQuery;

	function getAjaxCount(data, callback) {
		if (window.AERIA_SOCIAL && window.AERIA_SOCIAL.URL != null) {
			$.getJSON(window.AERIA_SOCIAL.URL, data, function(response) {
				if (response == null) return;
				callback(response);
			});
		} else {
			Aeria.ajax.action('aeriasocial.get', data, function(response) {
				if (response == null) return;
				callback($.parseJSON(response) || {});
			});
		}
	}

	function setSum($t) {
		if ($t.data('aeriasocial-summed') == true) return;
		$t.attr('data-aeriasocial-summed', true);

		var sum = 0;
		$t.find('[data-aeriasocial-count]').each(function() {
			sum += ($(this).text() << 0);
		});

		var $cont = $t.parents('.aeriasocial-container');
		if ($cont.length > 0) {
			$cont.addClass('enabled');

			var $sum = $cont.find('[data-aeriasocial-count-sum]');
			if ($sum.length > 0) {
				$sum.addClass('enabled').text(sum + ' shares');
			}
		}
	}

	function popup($t) {
		var service = $t.data('aeriasocial-service');

		var $p = $t.parents('[data-aeriasocial-uri]');
		var uri = ($p.length > 0 && $p.data('aeriasocial-uri')) ? encodeURIComponent($p.data('aeriasocial-uri')) : location.href;
		var text = $t.data('aeriasocial-text') ? encodeURIComponent($t.data('aeriasocial-text')) : document.title;

		if (service === 'facebook') {
			popupJS('http://facebook.com/sharer/sharer.php?u=' + uri);
		} else if (service === 'twitter') {
			popupJS('https://twitter.com/intent/tweet?url=' + uri + '&text=' + text + ($t.data('aeriasocial-via') ? '&via=' + encodeURIComponent($t.data('aeriasocial-via')) : '') );
		} else if (service === 'linkedin') {
			popupJS('http://www.linkedin.com/shareArticle?mini=true&url=' + uri);
		} else if (service === 'gplus') {
			popupJS('https://plus.google.com/share?url=' + uri);
		}
	}

	function popupJS(uri){
		var w = 550, h = 420, l = Math.floor((screen.width-w)/2), t = Math.floor((screen.height-h)/2);
		window.open(uri, '_blank', "width=" + w + ",height=" + h + ",top=" + t + ",left=" + l);
	}

	self.load = function() {
		var uri = [];

		$('[data-aeriasocial-needajax=true]').each(function(){
			var $t = $(this);
			if ($t.data('aeriasocial-needajax') == false) return;

			$t.attr('data-aeriasocial-needajax', false);
			$t.attr('data-aeriasocial-summed', false);
			uri.push( $t.data('aeriasocial-uri') );
		});

		if (uri.length === 0) return;

		getAjaxCount({
			uri: uri,
		}, function(response) {
			if ( ! $.isArray(response)) response = [ response ];

			$.each(response, function(key, response_per_uri) {
				var $t = $('[data-aeriasocial-uri="' + response_per_uri.uri + '"]');

				$.each(response_per_uri.services, function(srv, count) {
					$t.find('[data-aeriasocial-service="' + srv + '"] [data-aeriasocial-count]').html(count);
				});

				setSum($t);
			});

		});
	},

	self.bind = function() {
		$('[data-aeriasocial-uri]').each(function() {
			setSum( $(this) );
		});

		if (self.binded === true) return;
		self.binded = true;

		var st = (document.body || document.documentElement).style;
		if (st.transition==null && st.WebkitTransition==null && st.MozTransition==null && st.MsTransition==null && st.OTransition==null) {
			$(document.body).addClass('no-csstransforms');
		}

		if ('ontouchstart' in window) {
			$(document.body).on('touchstart', '.aeriasocial-container', function(){
				var $t = $(this);
				if ($t.hasClass('hover')) {
					return $t.removeClass('hover');
				}

				$('.aeriasocial-container.hover').removeClass('hover');
				$t.addClass('hover');
			});
		}

		$(document).on('click', '.aeriasocial-btn', function(e){
			e.preventDefault();
			e.stopPropagation();
			popup( $(this) );
		});
	};

	$(document).ready(function(){
		self.bind();
		self.load();
	});

	return self;

})();

window.Aeria = window.Aeria || {};
Aeria.ajax = {
	action: function(method,params,callback){
		params = params || {};
		params.ajax = method;
		return jQuery.post(AERIA_AJAX.URL, params, callback);
	},
	load: function(method,target,fragm,params,callback){
		params = params || {}; params.ajax = method;
		return jQuery(target).load(AERIA_AJAX.URL+(fragm!==undefined?' '+fragm:''),params,callback);
	},
	append: function(method,target,fragm,params,callback,wrapper){
		params = params || {}; params.ajax = method;
		return jQuery(target).append(
			jQuery(wrapper||"<span>").load(AERIA_AJAX.URL+(fragm!==undefined?' '+fragm:''),params,callback)
		);
	}
};

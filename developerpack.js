function developerDispatch(data) {
	data.action = 'developerpack_' + data.action;
	return jQuery.ajax({
		url: ajaxurl,
		method: 'POST',
		data
	});
}

developerDispatch({
	action: 'open'
}).then(res => console.log(res));

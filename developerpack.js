function developerDispatch(data) {
	data.action = 'developerpack_' + data.action;
	return jQuery.ajax({
		url: ajaxurl,
		method: 'POST',
		data
	});
}

developerDispatch({
	action: 'test',
	value: 'If you see this message, everything is fine!'
}).then(res => alert(res));

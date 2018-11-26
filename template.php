<script>
	function developerDispatch(data) {
		data.action = 'developerpack_' + data.action;
		return jQuery.ajax({
			url: ajaxurl,
			method: 'POST',
			dataType: 'json',
			data
		});
	}
</script>
<div class="wrap">
	<h1>Developer Pack</h1>
	<div>
		<p>Welcome to Developer Pack. With this plugin you can view system information, download source code with advance options and live editing your website with a powerful code editor.</p>
		<div>
			<button onclick="phpinfo()" class="button button-primary">PHP Info</button>
			<a href="https://codex.wordpress.org/WordPress_Coding_Standards" target="_blank" class="button">Coding standard</a>
			<a href="https://codex.wordpress.org/Plugin_API" target="_blank" class="button">Plugin API</a>
			<a href="https://codex.wordpress.org/Theme_Development" target="_blank" class="button">Theme Development</a>
			<a href="https://codex.wordpress.org/AJAX_in_Plugins" target="_blank" class="button">AJAX</a>
		</div>
		<script>
			function phpinfo() {
				var win = window.open();
				win.document.body.innerHTML = `<?php echo addslashes( $phpinfo ); ?>`;
			}
		</script>
	</div>
	<h2>Download source code</h2>
	<p>Zipped source code</p>
	<div class="notice notice-warning hidden" id="zipped-danger">
		<p>Don't forget to clean all the zipped source code after download or else it will lead to serious security bleach in your system!</p>
	</div>
	<table class="wp-list-table widefat fixed striped comments">
		<thead>
			<tr>
				<th class="manage-column">File</th>
				<th class="manage-column">Size</th>
				<th class="manage-column" width="100">Action</th>
			</tr>
		</thead>
		<tbody id="zipped">
		</tbody>
		<tbody id="no-zipped">
			<tr class="no-items"><td class="colspanchange" colspan="3">No zipped files found</td></tr>
		</tbody>
		<tfoot>
			<tr>
				<th></th>
				<th></th>
				<th scope="col">
					<a href="#" onclick="dearchive()">Clean all</a>
				</th>
			</tr>
		</tfoot>
	</table>
	<div class="notice notice-success is-dismissible hidden" id="zipped-success">
		<p>File deleted successfully</p>
	</div>
	<script type="text/javascript">
		function updateZipped() {
			jQuery('#zipped').html('');
			developerDispatch({
				action: 'zipped'
			}).then(res => {
				window.resx = res;
				if (res.length > 0) {
					jQuery('#zipped-danger').removeClass('hidden');
					jQuery('#no-zipped').addClass('hidden');
					res.forEach(file =>
					jQuery('<tr>').append(
						jQuery('<td>').append(
							jQuery('<a>', {
								href: '<?php echo plugins_url( '', __FILE__ ); ?>/zip/' + file.name,
								text: file.name
							})
						),
						jQuery('<td>' + file.size + '</td>'),
						jQuery('<td>').append(
							jQuery('<a />', {
								href: '#',
								class: 'dearchive-button',
								text: 'Delete',
								click: function() {
									developerDispatch({
										action: 'delete',
										file: file.path
									}).then(res => {
										jQuery('#zipped-success').removeClass('hidden');
										updateZipped();
									});
								}
							})
						)
					).appendTo('#zipped'))
				} else {
					jQuery('#zipped-danger').addClass('hidden');
					jQuery('#no-zipped').removeClass('hidden');
				}
			});
		}
		updateZipped();
		function dearchive() {
			jQuery('.dearchive-button').click();
		}
	</script>
	<p>Analize project</p>
	<button class="button" onclick="analize(this)">Analize</button>
	<p id="analize-result"></p>
	<script type="text/javascript">
		function analize(self) {
			jQuery(self).attr('disabled', 'disabled');
			developerDispatch({
				action: 'analize'
			}).then(res => jQuery('#analize-result').text(JSON.stringify(res)));
			jQuery(self).removeAttr('disabled');
		}
	</script>
	<p>Download source code options</p>
	<textarea name="options" rows="8" id="zip-options" style="width: 100%"></textarea>
		<div> 
			<button class="button" onclick="minimalist()">Minimalist</button>
			<button class="button" onclick="sourcecode()">Source Code</button>
			<button class="button" onclick="full()">Full</button>
			<button class="button button-primary" onclick="createZip(this)">Create Zip</button>
		</div>
	<div class="hidden" id="created-zip-alert">
		<p>Zip file has been created successfully. Download it now: <a id="created-zip"></a></p>
	</div>
	<script type="text/javascript">
		function updateOptions(options) {
			jQuery('#zip-options').val(JSON.stringify(options, true, 4));
		}
		function minimalist() {
			updateOptions({
				action: "zip",
				output: "minimalist.zip",
				rule: "include",
				files: [
					"/<?php echo $theme_dir; ?>",
					"/<?php echo $child_theme_dir; ?>",
					"/wp-config.php"
				]
			});
		}
		function sourcecode() {
			updateOptions({
				action: "zip",
				output: "sourcecode.zip",
				rule: "exclude",
				files: [
					"/wp-admin",
					"/wp-includes",
					"/wp-content/backup-db",
					"/wp-content/backups",
					"/wp-content/blogs.dir",
					"/wp-content/cache",
					"/wp-content/upgrade",
					"/wp-content/uploads",
					"/wp-content/mu-plugins",
					".zip",
					".rar",
					".jpg",
					".png",
					".gif",
					".mp3",
					".mp4"
				]
			});
		}
		function full() {
			updateOptions({
				action: "zip",
				output: "full.zip",
				rule: "exclude",
				files: [
					"/.keep"
				],
				maxsize: 10000000,
				timeout: 300
			});
		}
		minimalist();
		function createZip(self) {
			jQuery(self).attr('disabled', 'disabled');
			let options = JSON.parse(jQuery('#zip-options').val());
			developerDispatch(options).then(res => {
				if (res.status === 200) {
					updateZipped();
					jQuery('#created-zip').attr('href', '<?php echo plugins_url( '', __FILE__ ); ?>/zip/' + res.output);
					jQuery('#created-zip').text(res.output);
					jQuery('#created-zip-alert').removeClass('hidden');
					jQuery('#created-zip-alert').addClass('notice notice-success');
				} else {
					alert(res.message);
				}
				jQuery(self).removeAttr('disabled')
			}).fail(res => {
				alert("Something went wrong. Open console to view error");
				console.log(res);
				jQuery(self).removeAttr('disabled')
			});
		}
	</script>
	<div>
		<h2>Code editing</h2>
		<p>We use Monaco for code editing. It's a free and opensource javascript text editor with advance feature from Microsoft. Click the button below to use Monaco.</p>
		<a href="<?php echo plugins_url( '/monaco.php', __FILE__ ); ?>" target="_blank" class="button button-primary">Monaco</a>
	</div>
</div>

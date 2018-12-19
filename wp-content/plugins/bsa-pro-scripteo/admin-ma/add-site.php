<?php
function getSiteValue($val) {
	if (isset($_GET['site_id'])) {
		return bsa_site($_GET['site_id'], $val);
	} else {
		if ( isset($_POST[$val]) || isset($_SESSION['bsa_site_status']) ) {
			if ( isset($_SESSION['bsa_site_status']) == 'site_added' ) {
				$_SESSION['bsa_clear_form'] = 'site_added';
				unset($_SESSION['bsa_site_status']);
			}
			$status = (isset($_SESSION['bsa_clear_form']) ? $_SESSION['bsa_clear_form'] : '');
			if ( $status == 'site_added' ) {
				return '';
			} else {
				return $_POST[$val];
			}
		} else {
			return '';
		}
	}
}
function selectedSiteOpt($optName, $optValue)
{
	if( isset( $_GET['site_id'] ) && bsa_site($_GET['site_id'], $optName) == $optValue OR isset($_POST[$optName]) && $_POST[$optName] == $optValue ) {
		echo 'selected="selected"';
	}
}
?>
	<h2>
		<?php if ( isset($_GET['site_id']) ): ?>
			<span class="dashicons dashicons-edit"></span> Edit <strong>Site ID <?php echo $_GET['site_id']; ?></strong> added by User ID <strong><?php echo bsa_site($_GET['site_id'], 'user_id'); ?></strong>
			<p><span class="dashicons dashicon-14 dashicons-arrow-left-alt"></span> <a href="<?php echo admin_url(); ?>admin.php?page=bsa-pro-sub-menu-ma-spaces">back to <strong>sites list</strong></a></p>
		<?php else: ?>
			<span class="dashicons dashicons-plus-alt"></span> Add new Site
			<p><span class="dashicons dashicon-14 dashicons-arrow-left-alt"></span> <a href="<?php echo admin_url(); ?>admin.php?page=bsa-pro-sub-menu-ma-spaces">back to <strong>sites list</strong></a></p>
		<?php endif; ?>
	</h2>

<?php if (  isset($_GET['site_id']) && bsa_site($_GET['site_id'], 'id') != NULL && bsa_site($_GET['site_id'], 'status') != 'pending' && bsa_site($_GET['site_id'], 'status') != 'blocked' ||
			!isset($_GET['site_id']) ||
			isset($_GET['site_id']) && bsa_site($_GET['site_id'], 'id') != NULL && bsa_role() == 'admin' ): ?>

	<form action="" method="post" enctype="multipart/form-data" class="bsaNewAd">
		<?php if ( isset($_GET['site_id']) ): ?>
			<input type="hidden" value="updateMaSite" name="bsaProAction">
		<?php else: ?>
			<input type="hidden" value="addMaNewSite" name="bsaProAction">
		<?php endif; ?>
		<table class="bsaAdminTable form-table">
			<tbody class="bsaTbody">
			<tr>
				<th colspan="2">
					<?php if ( isset($_GET['site_id']) ): ?>
						<h3><span class="dashicons dashicons-admin-site"></span> Edit Site</h3>
					<?php else: ?>
						<h3><span class="dashicons dashicons-admin-site"></span> Add new Site</h3>
					<?php endif; ?>
				</th>
			</tr>
			<tr>
				<th scope="row"><label for="bsa_pro_title">Title</label></th>
				<td>
					<input id="bsa_pro_title" name="title" type="text" class="regular-text" maxlength="255" value="<?php echo getSiteValue('title') ?>">
					<p class="description">Shown in Ordering Form.</p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="bsa_pro_url">Site URL</label></th>
				<td>
					<input id="bsa_pro_url" name="url" type="url" class="regular-text" maxlength="255" value="<?php echo getSiteValue('url') ?>" <?php echo ((bsa_role() != 'admin' && isset($_GET['site_id'])) ? 'disabled' : NULL) ?>>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="bsa_pro_thumb">Thumbnail</label></th>
				<td>
					<input type="file" id="bsa_pro_thumb" name="thumb">
					<p class="description"><?php echo get_option('bsa_pro_plugin_trans_form_left_thumb'); ?></p>
					<p class="description"><strong>Note!</strong> If you editing the site and do not want to change the image, skip this field.</p>
				</td>
			</tr>
			<tr>
				<th scope="row"><label for="bsa_pro_status">Status</label></th>
				<td>
					<select id="bsa_pro_status" name="status">
						<option value="active" <?php selectedSiteOpt('status', 'active'); ?>>Active</option>
						<option value="inactive" <?php selectedSiteOpt('status', 'inactive'); ?>>Inactive</option>
						<?php if ( bsa_role() == 'admin' ): ?>
							<option value="blocked" <?php selectedSiteOpt('status', 'blocked'); ?>>Block</option>
						<?php endif; ?>
					</select>
				</td>
			</tr>
			</tbody>
		</table>
		<input class="bsa_inputs_required" name="inputs_required" type="hidden" value="">
		<p class="submit">
			<input type="submit" value="Save Site" class="button button-primary" id="bsa_pro_submit" name="submit">
		</p>
	</form>

<?php else: ?>

	<div class="updated settings-error" id="setting-error-settings_updated">
		<p><strong>Error!</strong> Site not exists!</p>
	</div>

<?php endif; ?>
<script>
	(function($) {
		// - start - open page
		var bsaItemsWrap = $(".wrap");
		setTimeout(function () {
			bsaItemsWrap.fadeIn(400);
		}, 400);
		// - end - open page
	})(jQuery);
</script>
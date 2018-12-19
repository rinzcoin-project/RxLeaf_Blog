<?php
if (isset($_GET['bsa_pro_action'])) {
	$getParam = $_GET['bsa_pro_action'];
} else {
	$getParam = NULL;
}

if (get_option('bsa_pro_plugin_symbol_position') == 'before') {
	$before = get_option('bsa_pro_plugin_currency_symbol');
} else {
	$before = '';
}
if (get_option('bsa_pro_plugin_symbol_position') != 'before') {
	$after = get_option('bsa_pro_plugin_currency_symbol');
} else {
	$after = '';
}

// active order by column
function get_order_ads($sid, $column)
{
	if ( isset($sid) && bsa_space($sid, 'order_ads') == $column ) {
		return 'bsaOrderActive';
	} else {
		return null;
	}
}

if ( isset($_GET['site_id']) && $_GET['site_id'] != NULL && $_GET['site_id'] != '' ) {
	$site_id = $_GET['site_id'];
	if ( bsa_site($_GET['site_id'], 'status') == 'active' && bsa_verify_role($_GET['site_id'], 'site') ) {
		$site_id = $_GET['site_id'];
	} else {
		$site_id = NULL;
	}
} else {
	$site_id = NULL;
}

$model = new BSA_PRO_Model();
$get_spaces = $model->getSpaces('both', NULL, 'user', $site_id);
$first_space = $get_spaces[0]['id'];
if ( isset($_GET['space_id']) && $_GET['space_id'] != NULL && $_GET['space_id'] != '' ) {
	$space_id = $_GET['space_id'];
} elseif ( $get_spaces != NULL ) {
	$space_id = $first_space;
} else {
	$space_id = 0;
}
$getActiveAds = $model->getActiveAds($space_id, bsa_space($space_id, 'max_items'), 'admin');
$getNotPaidAds = $model->getNotPaidAds($space_id);
$getBlockedAds = $model->getBlockedAds($space_id);
$getArchiveAds = $model->getArchiveAds($space_id);
$getSites = $model->getSites(bsa_role());
?>

<div class="bsaSortableNotice" style="display:none">
	Changes has been saved.
</div>

<h2>
	<span class="dashicons dashicons-welcome-widgets-menus"></span> <?php echo (( isset($_GET['site_id']) && bsa_site($_GET['site_id'], 'id') ) ? 'Manage Spaces and Ads' : 'Manage Sites') ?>
	<?php echo (( isset($_GET['site_id']) && bsa_site($_GET['site_id'], 'id') ) ? 'in <strong>'.bsa_site($_GET['site_id'], 'title').' </strong>' : '') ?>
	<?php if ( isset($_GET['site_id']) ): ?>
		<p><span class="dashicons dashicon-14 dashicons-arrow-left-alt"></span> <a href="<?php echo admin_url(); ?>admin.php?page=bsa-pro-sub-menu-ma-spaces">back to <strong>sites list</strong></a></p>
	<?php endif; ?>
</h2>

<?php if ( isset($_GET['space_id']) && bsa_space($_GET['space_id'], 'id') != NULL && $site_id != NULL || !isset($_GET['space_id']) && $site_id != NULL ): ?>

	<?php if ( $get_spaces ): ?>

		<h2 class="nav-tab-wrapper">
			<?php foreach ( $get_spaces as $space ): ?>
				<a href="<?php echo admin_url(); ?>admin.php?page=bsa-pro-sub-menu-ma-spaces&site_id=<?php echo $site_id; ?>&space_id=<?php echo $space['id']; ?>"
				   class="nav-tab <?php if ( $first_space == $space['id'] && $_GET['space_id'] == NULL OR $_GET['space_id'] == $space['id'] ) { echo 'nav-tab-active'; } else { echo ''; } ?>">
					<?php echo $space['name']; ?> <?php echo ($space['status'] == 'active') ? '<small>(<span class="bsaGreen">active</span>)</small>' : '<small>(<span class="bsaRed">inactive</span>)</small>' ?>
				</a>
			<?php endforeach; ?>
			<a href="<?php echo admin_url(); ?>admin.php?page=bsa-pro-sub-menu-ma-new-space" class="nav-tab" style="float: right;margin-top: 0;"><span class="bsaGreen">+</span> Add new Space</a>
		</h2>

		<?php $domain = str_replace('/', '', str_replace('www.', '', str_replace('http://', '', str_replace('https://', '', bsa_site($site_id, 'url'))))); ?>

		<?php if(get_option('bsa_pro_plugin_'.'agency_other_sites') == 'yes'): ?>
			<div class="bsaDisplayWaysWrapper" style="display:none">
				<div class="bsaDisplayWaysWrapperInner">
					<span class="bsaDisplayWaysClose"></span>

					<strong>Display Ads via Shortcode</strong><br>
					<pre style="max-width:500px;white-space:normal;">[bsa_pro_ma_space id="<?php echo $space_id; ?>" type="space"
url="<?php echo get_option('bsa_pro_plugin_agency_api_url'); ?>"
secure="<?php echo hash('sha1', $space_id.$domain.'bsa_pro'); ?>"]</pre>
					<br><br>

					<strong>Display Ads via Template Tag</strong><br>
					<pre style="max-width:500px;white-space:normal;">&lt;?php echo do_shortcode('[bsa_pro_ma_space id="<?php echo $space_id; ?>" type="space"
url="<?php echo get_option('bsa_pro_plugin_agency_api_url'); ?>"
secure="<?php echo hash('sha1', $space_id.$domain.'bsa_pro'); ?>"]'); ?&gt;</pre>
					<br><br>

					<?php if(get_option('bsa_pro_plugin_'.'agency_other_sites') == 'yes'): ?>
						<strong>Display Ads via Iframe for Non-WordPress Sites</strong><br>
						<pre style="max-width:500px;white-space:normal;">&lt;iframe style="border:0" width="100%" height="auto" scrolling="no" id="bsaIframe" onload="bsaIframe()"
src="<?php echo get_option('bsa_pro_plugin_agency_api_url').(strpos(get_option('bsa_pro_plugin_agency_api_url'), '?') !== false ? '&' : '?'); ?>id=<?php echo $space_id; ?>&i=1"&gt;
&lt;/iframe&gt;&lt;script&gt;function bsaIframe() { var iFrameID = document.getElementById('bsaIframe'); if(iFrameID) { iFrameID.height = iFrameID.contentWindow.document.body.scrollHeight + 'px'; } }&lt;/script&gt;</pre><br>
					<?php endif; ?>
				</div>
			</div>
		<?php endif; ?>

		<div class="bsaSpaceFilter wp-filter">
			<ul class="filter-links">
				<li class="bsaSpaceShortCode">
					<?php if( $space_id && bsa_space($space_id, 'status') == 'removed' ) {
						?><span style="display: inline-block;padding: 8px 0;"><strong>Note!</strong> This space was removed and you cannot use it!</span><?php
					} else {
						?>
							Use this Ad Space by shortcode <input class="bsaSpaceShortCodeInner" type="text" value='[bsa_pro_ma_space id="<?php echo $space_id; ?>" type="space" url="<?php echo get_option('bsa_pro_plugin_agency_api_url'); ?>" secure="<?php echo hash('sha1', $space_id.$domain.'bsa_pro'); ?>"]' placeholder="">
							<?php if(get_option('bsa_pro_plugin_'.'agency_other_sites') == 'yes'): ?>
								<a href="#" class="bsaShowDisplayWays"><span class="dashicons dashicons-visibility"></span> show all display ways</a>
							<?php endif; ?>
						<?php
					} ?>
				</li>
				<?php if ( $get_spaces && bsa_space($space_id, 'status') != 'removed'): ?>
					<li class="addNewSpace">
						<a href="<?php echo admin_url(); ?>admin.php?page=bsa-pro-sub-menu-ma-new-space&site_id=<?php echo $site_id; ?>&space_id=<?php echo $space_id; ?>" class="current currentBlue">Edit Space</a>
						<a href="<?php echo admin_url(); ?>admin.php?page=bsa-pro-sub-menu-ma-new-ad" class="current currentGreen">Add new Ad</a>
						<a href="<?php echo admin_url(); ?>admin.php?page=bsa-pro-sub-menu-ma-spaces&site_id=<?php echo $site_id; ?>&space_id=<?php echo $space_id; ?>&remove_action=1" class="current currentRed">Remove Space</a>
					</li>
				<?php endif; ?>
			</ul>
		</div>

		<?php $model->getAdminAction();
		if ($model->validationBlocked()) {
			echo '
		<div class="updated settings-error" id="setting-error-settings_updated">
			<p><div class="bsaLoader"></div><strong>Ad blocked.</strong></p>
		</div>';
		} elseif ($model->validationUnblocked()) {
			echo '
		<div class="updated settings-error" id="setting-error-settings_updated">
			<p><div class="bsaLoader"></div><strong>Ad unblocked.</strong></p>
		</div>';
		} elseif ($model->validationPaid()) {
			echo '
		<div class="updated settings-error" id="setting-error-settings_updated">
			<p><div class="bsaLoader"></div><strong>Ad marked as paid.</strong></p>
		</div>';
		} ?>

		<?php
		if( isset($_GET['remove_action']) && !isset($_GET['remove_confirm']) ) {
			?>
			<div class="updated settings-error" id="setting-error-settings_updated">
				<p><strong>Confirm remove action!</strong> Yes, I <a href="<?php echo admin_url(); ?>admin.php?page=bsa-pro-sub-menu-ma-spaces&site_id=<?php echo $_GET['site_id'].'&space_id='.$_GET['space_id'] ?>&remove_action=1&remove_confirm=1">want</a> to delete Space ID <strong><?php echo $_GET['space_id'] ?></strong>.</p>
			</div>
			<?php
		}
		?>

		<h3>Active Ads (<?php echo count($getActiveAds) ?>)
			<div style="margin:10px 0 20px;">order by
				<a href="<?php echo admin_url(); ?>admin.php?page=bsa-pro-sub-menu-ma-spaces&site_id=<?php echo $_GET['site_id']; ?>&space_id=<?php echo $space_id; ?>&order_ads=id" class="bsaOrderBy <?php echo get_order_ads($space_id, 'id'); ?>"><span class="dashicons dashicons-editor-ol"></span> id</a>
				<a href="<?php echo admin_url(); ?>admin.php?page=bsa-pro-sub-menu-ma-spaces&site_id=<?php echo $_GET['site_id']; ?>&space_id=<?php echo $space_id; ?>&order_ads=ad_limit" class="bsaOrderBy <?php echo get_order_ads($space_id, 'ad_limit'); ?>"><span class="dashicons dashicons-clock"></span> display limit</a>
				<a href="<?php echo admin_url(); ?>admin.php?page=bsa-pro-sub-menu-ma-spaces&site_id=<?php echo $_GET['site_id']; ?>&space_id=<?php echo $space_id; ?>&order_ads=priority" class="bsaOrderBy <?php echo get_order_ads($space_id, 'priority'); ?>"><img src="<?php echo plugins_url('/bsa-pro-scripteo/frontend/img/icon-drag-16.png'); ?>" /> priority</a>
				<a href="<?php echo admin_url(); ?>admin.php?page=bsa-pro-sub-menu-ma-spaces&site_id=<?php echo $_GET['site_id']; ?>&space_id=<?php echo $space_id; ?>&order_ads=cost" class="bsaOrderBy <?php echo get_order_ads($space_id, 'cost'); ?>"><span class="dashicons dashicons-vault"></span> cost</a>
			</div>
		</h3>
		<table class="wp-list-table widefat bsaListTable">
			<thead>
			<tr>
				<th></th>
				<th style="" class="manage-column post-title page-title column-title">Ad Content</th>
				<th style="" class="manage-column">Buyer</th>
				<th style="" class="manage-column">Stats</th>
				<th style="" class="manage-column">Ad Display Limit</th>
				<th style="" class="manage-column">Order Details</th>
			</tr>
			</thead>

			<tbody id="sortListAgency" class="<?php echo ( (get_order_ads($space_id, 'priority')) ? 'bsaSortableOn' : null ); ?>">
			<?php if (count($getActiveAds) > 0) {
				foreach ($getActiveAds as $key => $entry) {

					if ($key % 2) {
						$alternate = '';
					} else {
						$alternate = 'alternate';
					}
					?>

					<tr class="<?php echo $alternate; ?>" id="<?php echo $entry['id']; ?>">
						<td class="bsaAdminImg">
							<img class="bsaAdminThumb" src="<?php echo ( $entry['img'] != '' ) ? bsa_upload_url().$entry['img'] : plugins_url('/bsa-pro-scripteo/frontend/img/example.png'); ?>">
						</td>
						<td class="post-title page-title column-title">
							<?php echo (isset($entry['ad_name']) && $entry['ad_name'] != '') ? '<span class="bsa_ad_name">'.$entry['ad_name'].'</span>' : null; ?>
							<strong><a href="<?php echo $entry['url']; ?>"><?php echo $entry['title']; ?></a></strong>
							<?php echo ( $entry['description'] != '' ) ? $entry['description'] : ''; ?>
							<?php echo ( $entry['html'] != '' ) ? 'HTML' : '' ; ?>
							<div class="row-actions">
								<form action="" method="post">
									<input type="hidden" value="block" name="bsaProAction">
									<input type="hidden" value="<?php echo $entry['id']; ?>" name="orderId">
								<span class="bsaProActionBtn bsaBlockBtn">
									<input type="submit" value="Block" id="submit" name="submit">
								</span>
								</form>
								|
							<span class="bsaPaidBtn">
								<a href="<?php echo admin_url(); ?>admin.php?page=bsa-pro-sub-menu-ma-new-ad&ad_id=<?php echo $entry['id']; ?>">
									Edit
								</a>
							</span>
							</div>
						</td>
						<td>
							<?php echo $entry['buyer_email']; ?>
						</td>
						<td>
							<?php
							$views = bsa_counter($entry['id'], 'view');
							$clicks = bsa_counter($entry['id'], 'click'); ?>
							Views <strong><?php echo ( $views != NULL ) ? $views : 0; ?></strong><br>
							Clicks <strong><?php echo ( $clicks != NULL ) ? $clicks : 0; ?></strong><br>
							<?php if ( $views != NULL && $clicks != NULL ): ?>
								CTR <strong><?php echo number_format(($clicks / $views) * 100, 2, '.', '').'%'; ?></strong><br>
							<?php endif; ?>
							<a target="_blank" href="<?php echo get_option('bsa_pro_plugin_agency_ordering_form_url') . (( strpos(get_option('bsa_pro_plugin_agency_ordering_form_url'), '?') == TRUE ) ? '&' : '?') ?>bsa_pro_stats=1&bsa_pro_email=<?php echo str_replace('@', '%40', $entry['buyer_email']); ?>&bsa_pro_id=<?php echo $entry['id']; ?>">
								full statistics
							</a>
						</td>
						<td>
							<?php
							if ( $entry['ad_model'] == 'cpd' ) {
								$time = time();
								$limit = $entry['ad_limit'];
								$diff = $limit - $time;
								$limit_value = ( $diff < 86400 /* 1 day in sec */ ) ? ( $diff > 0 ) ? 'less than 1 day' : '0 days' : number_format($diff / 24 / 60 / 60).' days';
								$diffTime = date('d M Y (H:m:s)', time() + $diff);
							} else {
								$limit_value = ($entry['ad_model'] == 'cpc') ? $entry['ad_limit'].' clicks' : $entry['ad_limit'].' views';
								$diffTime = '';
							}
							?>
							<strong><?php echo $limit_value; ?></strong><br>
							<?php echo ( $entry['ad_model'] == 'cpd' ) ? $diffTime : ''; ?>
						</td>
						<td>
							Ad ID / Order ID <strong><?php echo $entry['id']; ?></strong><br>
							Billing Model <strong><?php echo strtoupper($entry['ad_model']); ?></strong><br>
							Cost <strong><?php echo $before . bsa_number_format($entry['cost']) . $after; ?></strong><br>
							<?php if ( $entry['paid'] == 1 ): ?>
								<span class="bsaColorGreen">Paid</span>
							<?php elseif ( $entry['paid'] == 2 ): ?>
								<span class="bsaColorGreen">Added via Admin Panel</span>
							<?php else: ?>
								<span class="bsaColorRed">Not paid</span>
							<?php endif; ?>
						</td>
					</tr>

				<?php }
			} else {
				?>

				<tr>
					<td style="text-align: center" colspan="7">
						List empty.
					</td>
				</tr>

			<?php } ?>
			</tbody>
		</table>

		<h3>Not Paid Ads</h3>
		<table class="wp-list-table widefat bsaListTable">
			<thead>
			<tr>
				<th></th>
				<th style="" class="manage-column post-title page-title column-title">Ad Content</th>
				<th style="" class="manage-column">Buyer</th>
				<th style="" class="manage-column">Stats</th>
				<th style="" class="manage-column">Ad Display Limit</th>
				<th style="" class="manage-column">Order Details</th>
			</tr>
			</thead>

			<tbody>
			<?php
			if (count($getNotPaidAds) > 0) {
				foreach ($getNotPaidAds as $key => $entry) {

					if ($key % 2) {
						$alternate = '';
					} else {
						$alternate = 'alternate';
					}
					?>

					<tr class="<?php echo $alternate; ?>">
						<td class="bsaAdminImg">
							<img class="bsaAdminThumb" src="<?php echo ( $entry['img'] != '' ) ? bsa_upload_url().$entry['img'] : plugins_url('/bsa-pro-scripteo/frontend/img/example.png'); ?>">
						</td>
						<td class="post-title page-title column-title">
							<?php echo (isset($entry['ad_name']) && $entry['ad_name'] != '') ? '<span class="bsa_ad_name">'.$entry['ad_name'].'</span>' : null; ?>
							<strong><a href="<?php echo $entry['url']; ?>"><?php echo $entry['title']; ?></a></strong>
							<?php echo ( $entry['description'] != '' ) ? $entry['description'] : ''; ?>
							<?php echo ( $entry['html'] != '' ) ? 'HTML' : '' ; ?>
							<div class="row-actions">
								<form action="" method="post">
									<input type="hidden" value="paid" name="bsaProAction">
									<input type="hidden" value="<?php echo $entry['id']; ?>" name="orderId">
									<span class="bsaProActionBtn bsaPaidBtn"><input type="submit" value="Mark as paid" id="submitPaid" name="submit"></span>
								</form>
								|
								<form action="" method="post">
									<input type="hidden" value="block" name="bsaProAction">
									<input type="hidden" value="<?php echo $entry['id']; ?>" name="orderId">
									<span class="bsaProActionBtn bsaBlockBtn"><input type="submit" value="Block" id="submitBlock" name="submit"></span>
								</form>
								|
							<span class="bsaPaidBtn">
								<a href="<?php echo admin_url(); ?>admin.php?page=bsa-pro-sub-menu-ma-new-ad&ad_id=<?php echo $entry['id']; ?>">
									Edit
								</a>
							</span>
							</div>
						</td>
						<td>
							<?php echo $entry['buyer_email']; ?>
						</td>
						<td>
							<?php
							$views = bsa_counter($entry['id'], 'view');
							$clicks = bsa_counter($entry['id'], 'click'); ?>
							Views <strong><?php echo ( $views != NULL ) ? $views : 0; ?></strong><br>
							Clicks <strong><?php echo ( $clicks != NULL ) ? $clicks : 0; ?></strong><br>
							<?php if ( $views != NULL && $clicks != NULL ): ?>
								CTR <strong><?php echo number_format(($clicks / $views) * 100, 2, '.', '').'%'; ?></strong><br>
							<?php endif; ?>
							<a target="_blank" href="<?php echo get_option('bsa_pro_plugin_agency_ordering_form_url') . (( strpos(get_option('bsa_pro_plugin_agency_ordering_form_url'), '?') == TRUE ) ? '&' : '?') ?>bsa_pro_stats=1&bsa_pro_email=<?php echo str_replace('@', '%40', $entry['buyer_email']); ?>&bsa_pro_id=<?php echo $entry['id']; ?>">
								full statistics
							</a>
						</td>
						<td>
							<?php
							if ( $entry['ad_model'] == 'cpd' ) {
								$time = time();
								$limit = $entry['ad_limit'];
								$diff = $limit - $time;
								$limit_value = ( $diff < 86400 /* 1 day in sec */ ) ? ( $diff > 0 ) ? 'less than 1 day' : '0 days' : number_format($diff / 24 / 60 / 60).' days';
								$diffTime = date('d M Y (H:m:s)', time() + $diff);
							} else {
								$limit_value = ($entry['ad_model'] == 'cpc') ? $entry['ad_limit'].' clicks' : $entry['ad_limit'].' views';
								$diffTime = '';
							}
							?>
							<strong><?php echo $limit_value; ?></strong><br>
							<?php echo ( $entry['ad_model'] == 'cpd' ) ? $diffTime : ''; ?>
						</td>
						<td>
							Ad ID / Order ID <strong><?php echo $entry['id']; ?></strong><br>
							Billing Model <strong><?php echo strtoupper($entry['ad_model']); ?></strong><br>
							Cost <strong><?php echo $before . bsa_number_format($entry['cost']) . $after; ?></strong><br>
							<?php if ( $entry['paid'] == 1 ): ?>
								<span class="bsaColorGreen">Paid</span>
							<?php elseif ( $entry['paid'] == 2 ): ?>
								<span class="bsaColorGreen">Added via Admin Panel</span>
							<?php else: ?>
								<span class="bsaColorRed">Not paid</span>
							<?php endif; ?>
						</td>
					</tr>

				<?php }
			} else {
				?>

				<tr>
					<td style="text-align: center" colspan="7">
						List empty.
					</td>
				</tr>

			<?php } ?>
			</tbody>
		</table>

		<h3>Blocked Ads</h3>
		<table class="wp-list-table widefat bsaListTable">
			<thead>
			<tr>
				<th></th>
				<th style="" class="manage-column post-title page-title column-title">Ad Content</th>
				<th style="" class="manage-column">Buyer</th>
				<th style="" class="manage-column">Stats</th>
				<th style="" class="manage-column">Ad Display Limit</th>
				<th style="" class="manage-column">Order Details</th>
			</tr>
			</thead>

			<tbody>
			<?php
			if (count($getBlockedAds) > 0) {
				foreach ($getBlockedAds as $key => $entry) {

					if ($key % 2) {
						$alternate = '';
					} else {
						$alternate = 'alternate';
					}
					?>

					<tr class="<?php echo $alternate; ?>">
						<td class="bsaAdminImg">
							<img class="bsaAdminThumb" src="<?php echo ( $entry['img'] != '' ) ? bsa_upload_url().$entry['img'] : plugins_url('/bsa-pro-scripteo/frontend/img/example.png'); ?>">
						</td>
						<td class="post-title page-title column-title">
							<?php echo (isset($entry['ad_name']) && $entry['ad_name'] != '') ? '<span class="bsa_ad_name">'.$entry['ad_name'].'</span>' : null; ?>
							<strong><a href="<?php echo $entry['url']; ?>"><?php echo $entry['title']; ?></a></strong>
							<?php echo ( $entry['description'] != '' ) ? $entry['description'] : ''; ?>
							<?php echo ( $entry['html'] != '' ) ? 'HTML' : '' ; ?>
							<div class="row-actions">
								<form action="" method="post">
									<input type="hidden" value="unblock" name="bsaProAction">
									<input type="hidden" value="<?php echo $entry['id']; ?>" name="orderId">
									<span class="bsaProActionBtn bsaPaidBtn"><input type="submit" value="Unblock" id="submitBlock" name="submit"></span>
								</form>
								|
							<span class="bsaPaidBtn">
								<a href="<?php echo admin_url(); ?>admin.php?page=bsa-pro-sub-menu-ma-new-ad&ad_id=<?php echo $entry['id']; ?>">
									Edit
								</a>
							</span>
							</div>
						</td>
						<td>
							<?php echo $entry['buyer_email']; ?>
						</td>
						<td>
							<?php
							$views = bsa_counter($entry['id'], 'view');
							$clicks = bsa_counter($entry['id'], 'click'); ?>
							Views <strong><?php echo ( $views != NULL ) ? $views : 0; ?></strong><br>
							Clicks <strong><?php echo ( $clicks != NULL ) ? $clicks : 0; ?></strong><br>
							<?php if ( $views != NULL && $clicks != NULL ): ?>
								CTR <strong><?php echo number_format(($clicks / $views) * 100, 2, '.', '').'%'; ?></strong><br>
							<?php endif; ?>
							<a target="_blank" href="<?php echo get_option('bsa_pro_plugin_agency_ordering_form_url') . (( strpos(get_option('bsa_pro_plugin_agency_ordering_form_url'), '?') == TRUE ) ? '&' : '?') ?>bsa_pro_stats=1&bsa_pro_email=<?php echo str_replace('@', '%40', $entry['buyer_email']); ?>&bsa_pro_id=<?php echo $entry['id']; ?>">
								full statistics
							</a>
						</td>
						<td>
							<?php
							if ( $entry['ad_model'] == 'cpd' ) {
								$time = time();
								$limit = $entry['ad_limit'];
								$diff = $limit - $time;
								$limit_value = ( $diff < 86400 /* 1 day in sec */ ) ? ( $diff > 0 ) ? 'less than 1 day' : '0 days' : number_format($diff / 24 / 60 / 60).' days';
								$diffTime = date('d M Y (H:m:s)', time() + $diff);
							} else {
								$limit_value = ($entry['ad_model'] == 'cpc') ? $entry['ad_limit'].' clicks' : $entry['ad_limit'].' views';
								$diffTime = '';
							}
							?>
							<strong><?php echo $limit_value; ?></strong><br>
							<?php echo ( $entry['ad_model'] == 'cpd' ) ? $diffTime : ''; ?>
						</td>
						<td>
							Ad ID / Order ID <strong><?php echo $entry['id']; ?></strong><br>
							Billing Model <strong><?php echo strtoupper($entry['ad_model']); ?></strong><br>
							Cost <strong><?php echo $before . bsa_number_format($entry['cost']) . $after; ?></strong><br>
							<?php if ( $entry['paid'] == 1 ): ?>
								<span class="bsaColorGreen">Paid</span>
							<?php elseif ( $entry['paid'] == 2 ): ?>
								<span class="bsaColorGreen">Added via Admin Panel</span>
							<?php else: ?>
								<span class="bsaColorRed">Not paid</span>
							<?php endif; ?>
						</td>
					</tr>

				<?php }
			} else {
				?>

				<tr>
					<td style="text-align: center" colspan="7">
						List empty.
					</td>
				</tr>

			<?php } ?>
			</tbody>
		</table>

		<h3>Archived Ads</h3>
		<table class="wp-list-table widefat bsaListTable">
			<thead>
			<tr>
				<th></th>
				<th style="" class="manage-column post-title page-title column-title">Ad Content</th>
				<th style="" class="manage-column">Buyer</th>
				<th style="" class="manage-column">Stats</th>
				<th style="" class="manage-column">Ad Display Limit</th>
				<th style="" class="manage-column">Order Details</th>
			</tr>
			</thead>

			<tbody>
			<?php
			if (count($getArchiveAds) > 0) {
				foreach ($getArchiveAds as $key => $entry) {

					if ($key % 2) {
						$alternate = '';
					} else {
						$alternate = 'alternate';
					}
					?>

					<tr class="<?php echo $alternate; ?>">
						<td class="bsaAdminImg">
							<img class="bsaAdminThumb" src="<?php echo ( $entry['img'] != '' ) ? bsa_upload_url().$entry['img'] : plugins_url('/bsa-pro-scripteo/frontend/img/example.png'); ?>">
						</td>
						<td class="post-title page-title column-title">
							<?php echo (isset($entry['ad_name']) && $entry['ad_name'] != '') ? '<span class="bsa_ad_name">'.$entry['ad_name'].'</span>' : null; ?>
							<strong><a href="<?php echo $entry['url']; ?>"><?php echo $entry['title']; ?></a></strong>
							<?php echo ( $entry['description'] != '' ) ? $entry['description'] : ''; ?>
							<?php echo ( $entry['html'] != '' ) ? 'HTML' : '' ; ?>
							<div class="row-actions">
							<span class="bsaPaidBtn">
								<a href="<?php echo admin_url(); ?>admin.php?page=bsa-pro-sub-menu-ma-new-ad&ad_id=<?php echo $entry['id']; ?>">
									Edit
								</a>
							</span>
							</div>
						</td>
						<td>
							<?php echo $entry['buyer_email']; ?>
						</td>
						<td>
							<?php
							$views = bsa_counter($entry['id'], 'view');
							$clicks = bsa_counter($entry['id'], 'click'); ?>
							Views <strong><?php echo ( $views != NULL ) ? $views : 0; ?></strong><br>
							Clicks <strong><?php echo ( $clicks != NULL ) ? $clicks : 0; ?></strong><br>
							<?php if ( $views != NULL && $clicks != NULL ): ?>
								CTR <strong><?php echo number_format(($clicks / $views) * 100, 2, '.', '').'%'; ?></strong><br>
							<?php endif; ?>
							<a target="_blank" href="<?php echo get_option('bsa_pro_plugin_agency_ordering_form_url') . (( strpos(get_option('bsa_pro_plugin_agency_ordering_form_url'), '?') == TRUE ) ? '&' : '?') ?>bsa_pro_stats=1&bsa_pro_email=<?php echo str_replace('@', '%40', $entry['buyer_email']); ?>&bsa_pro_id=<?php echo $entry['id']; ?>">
								full statistics
							</a>
						</td>
						<td>
							<?php
							if ( $entry['ad_model'] == 'cpd' ) {
								$time = time();
								$limit = $entry['ad_limit'];
								$diff = $limit - $time;
								$limit_value = ( $diff < 86400 /* 1 day in sec */ ) ? ( $diff > 0 ) ? 'less than 1 day' : '0 days' : number_format($diff / 24 / 60 / 60).' days';
								$diffTime = date('d M Y (H:m:s)', time() + $diff);
							} else {
								$limit_value = ($entry['ad_model'] == 'cpc') ? $entry['ad_limit'].' clicks' : $entry['ad_limit'].' views';
								$diffTime = '';
							}
							?>
							<strong><?php echo $limit_value; ?></strong><br>
							<?php echo ( $entry['ad_model'] == 'cpd' ) ? $diffTime : ''; ?>
						</td>
						<td>
							Ad ID / Order ID <strong><?php echo $entry['id']; ?></strong><br>
							Billing Model <strong><?php echo strtoupper($entry['ad_model']); ?></strong><br>
							Cost <strong><?php echo $before . bsa_number_format($entry['cost']) . $after; ?></strong><br>
							<?php if ( $entry['paid'] == 1 ): ?>
								<span class="bsaColorGreen">Paid</span>
							<?php elseif ( $entry['paid'] == 2 ): ?>
								<span class="bsaColorGreen">Added via Admin Panel</span>
							<?php else: ?>
								<span class="bsaColorRed">Not paid</span>
							<?php endif; ?>
						</td>
					</tr>

				<?php }
			} else {
				?>

				<tr>
					<td style="text-align: center" colspan="7">
						List empty.
					</td>
				</tr>

			<?php } ?>
			</tbody>
		</table>

	<?php elseif ( $space_id == 0 ): ?>

		<div class="updated settings-error" id="setting-error-settings_updated">
			<p><strong>Spaces not exists!</strong> Go <a href="<?php echo admin_url(); ?>admin.php?page=bsa-pro-sub-menu-ma-new-space">here</a> to add first space.</p>
		</div>

	<?php else: ?>

		<div class="updated settings-error" id="setting-error-settings_updated">
			<p><strong>Note!</strong> Space not exists! Go <a href="<?php echo admin_url(); ?>admin.php?page=bsa-pro-sub-menu-ma-spaces">back</a>.</p>
		</div>

	<?php endif; ?>

<?php else: ?>

	<?php if ( isset($_GET['site_id']) && bsa_site($_GET['site_id'], 'id') != NULL || !isset($_GET['site_id']) ): ?>

		<h2 class="nav-tab-wrapper" style="max-width: 300px; float: right;">
			<a href="<?php echo admin_url(); ?>admin.php?page=bsa-pro-sub-menu-ma-new-site" class="nav-tab"><span class="bsaGreen">+</span> Add new Site</a>
		</h2>

		<h3>Sites</h3>
		<table class="wp-list-table widefat bsaListTable">
			<thead>
			<tr>
				<th></th>
				<th style="" class="manage-column post-title page-title column-title">Site Title</th>
				<th style="" class="manage-column">URL</th>
				<th style="" class="manage-column">Stats</th>
			</tr>
			</thead>

			<tbody>
			<?php
			if (count($getSites) > 0) {
				foreach ($getSites as $key => $entry) {

					if ($key % 2) {
						$alternate = '';
					} else {
						$alternate = 'alternate';
					}
					?>

					<tr class="<?php echo $alternate; ?>">
						<td class="bsaAdminImg">
							<img class="bsaAdminThumb" src="<?php echo ( $entry['thumb'] != '' ) ? bsa_upload_url().$entry['thumb'] : plugins_url('/bsa-pro-scripteo/frontend/img/example.png'); ?>">
						</td>
						<td class="post-title page-title column-title">
							<strong><?php echo $entry['title']; ?>
								<?php if ( $entry['status'] == 'active' ): ?>
									(<a href="<?php echo admin_url(); ?>admin.php?page=bsa-pro-sub-menu-ma-spaces&site_id=<?php echo $entry['id']?>">Manage Spaces and Ads</a>)
								<?php endif; ?>
							</strong>
							<?php if ( $entry['status'] != 'blocked' && $entry['status'] != 'pending' OR bsa_role() == 'admin' ): ?>
								<div class="row-actions">
									<span class="bsaPaidBtn">
										<a href="<?php echo admin_url(); ?>admin.php?page=bsa-pro-sub-menu-ma-new-site&site_id=<?php echo $entry['id']; ?>">
											Edit
										</a>
									</span>
								</div>
							<?php endif; ?>
						</td>
						<td>
							<a href="<?php echo $entry['url']; ?>"><?php echo $entry['url']; ?></a>
						</td>
						<td>
							<?php if ( $entry['status'] == 'active' ): ?>
								<span class="bsaColorGreen">Active</span>
							<?php elseif ( $entry['status'] == 'pending' ): ?>
								<span class="bsaColorGrey">Pending</span>
							<?php elseif ( $entry['status'] == 'inactive' ): ?>
								<span class="bsaColorGrey">Inactive</span>
							<?php elseif ( $entry['status'] == 'blocked' ): ?>
								<span class="bsaColorGrey">Blocked</span>
							<?php endif; ?>
						</td>
					</tr>

				<?php }
			} else {
				?>

				<tr>
					<td style="text-align: center" colspan="7">
						List empty.
					</td>
				</tr>

			<?php } ?>
			</tbody>
		</table>

	<?php else: ?>

		<div class="updated settings-error" id="setting-error-settings_updated">
			<p><strong>Note!</strong> Space not exists! Go <a href="<?php echo admin_url(); ?>admin.php?page=bsa-pro-sub-menu-ma-spaces">back</a>.</p>
		</div>

	<?php endif; ?>

<?php endif; ?>

<script>
	(function($) {
		// - start - open page
		var bsaItemsWrap = $('.wrap');
		bsaItemsWrap.hide();

		setTimeout(function(){
			bsaItemsWrap.fadeIn(400);
		}, 400);
		// - end - open page

		<?php if ( get_order_ads($space_id, 'priority') ): ?>
		var sortListAgency = $('#sortListAgency');
		sortListAgency.sortable({
			stop : function(event, ui){
				var getOrderAgency = $(this).sortable('toArray');
				$.post(ajaxurl, {action:'bsa_sortable_callback',bsa_order:getOrderAgency}, function(result) {
					var bsaSortableNotice = $('.bsaSortableNotice');
					bsaSortableNotice.fadeIn();
					setTimeout(function(){
						bsaSortableNotice.fadeOut();
					}, 2000);
				});
			}
		});
		sortListAgency.disableSelection();
		<?php endif; ?>

		var bsaShowDisplayWays = $('.bsaShowDisplayWays');
		var bsaDisplayWaysWrapper = $('.bsaDisplayWaysWrapper');
		bsaShowDisplayWays.click(function(){
			bsaDisplayWaysWrapper.fadeIn();
		});

		var bsaDisplayWaysClose = $('.bsaDisplayWaysClose');
		bsaDisplayWaysClose.click(function(){
			bsaDisplayWaysWrapper.fadeOut();
		});

		<?php if ($model->validationBlocked() or $model->validationUnblocked() or $model->validationPaid()) { ?>
		var bsaValidationAlert = $('#setting-error-settings_updated');
		bsaValidationAlert.fadeIn(100);
		setTimeout(function(){
			bsaValidationAlert.fadeOut(100);
			bsaItemsWrap.fadeOut(100);
		}, 2000);
		setTimeout(function(){
			window.location=document.location.href;
		}, 2000);
		<?php } ?>
	})(jQuery);
</script>
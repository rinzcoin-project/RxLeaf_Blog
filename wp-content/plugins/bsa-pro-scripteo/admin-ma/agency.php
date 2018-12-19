<?php
if (get_option('bsa_pro_plugin_symbol_position') == 'before') {
	$before = '<small>'.get_option('bsa_pro_plugin_currency_symbol').'</small> ';
} else {
	$before = '';
}
if (get_option('bsa_pro_plugin_symbol_position') != 'before') {
	$after = ' <small>'.get_option('bsa_pro_plugin_currency_symbol').'</small>';
} else {
	$after = '';
}
$model = new BSA_PRO_Model();
$countSites = $model->countSites('user');
$countSpaces = $model->countSpaces('user');
$countAds = $model->countAgencyAds('user');
$pendingEarnings = $model->pendingEarnings(bsa_role());
$getUserEarnings = $model->getUserEarnings();
$pendingWithdrawals = $model->pendingWithdrawals(bsa_role());
$getPendingAds = $model->getPendingAds('user');
$getLastAds = $model->getLastAds(5, 'user');
$agencyCommission = ( get_option('bsa_pro_plugin_'.'agency_commission') > 0 ) ? get_option('bsa_pro_plugin_'.'agency_commission') : 30;
?>
<div style="float:right;">
	<strong><a href="http://codecanyon.net/item/ads-pro-multipurpose-wordpress-ad-manager/10275010?ref=scripteo">ADS PRO</a></strong> - Marketing Agency <?php echo get_option('bsa_pro_plugin_agency_api_version') ?>
</div>
<h2>
	<i class="dashicons dashicons-megaphone"></i> ADS PRO - Marketing Agency Dashboard
</h2>
<div class="bsaDashboard">
	<div class="bsaDashboard-stats">
		<div class="bsaDashboard-stat">
			<div class="bsaDashboard-inner greenBg">
				<span class="dashicons dashicons-admin-site"></span>
				<span class="bsaDashboard-title">Sites</span>
				<strong class="bsaDashboard-amount"><?php echo ( $countSites ) ? $countSites : 0 ?></strong>
			</div>
		</div>
		<div class="bsaDashboard-stat">
			<div class="bsaDashboard-inner blueBg">
				<span class="dashicons dashicons-welcome-widgets-menus"></span>
				<span class="bsaDashboard-title">Spaces / Ads</span>
				<strong class="bsaDashboard-amount"><?php echo ( $countSpaces ) ? $countSpaces : 0 ?> / <?php echo ( $countAds ) ? $countAds : 0 ?> </strong>
			</div>
		</div>
		<div class="bsaDashboard-stat">
			<div class="bsaDashboard-inner yellowBg">
				<span class="dashicons dashicons-vault"></span>
				<span class="bsaDashboard-title">Pending Earnings</span>
				<strong class="bsaDashboard-amount"><?php echo ( $pendingEarnings ) ? $before.bsa_number_format(($pendingEarnings - ($pendingEarnings * ($agencyCommission / 100)))).$after : $before.bsa_number_format(0).$after ?></strong>
			</div>
		</div>
		<div class="bsaDashboard-stat">
			<div class="bsaDashboard-inner violetBg">
				<span class="dashicons dashicons-vault"></span>
				<span class="bsaDashboard-title">Possible to Withdraw</span>
				<strong class="bsaDashboard-amount"><?php echo ( $getUserEarnings ) ? $before.bsa_number_format($getUserEarnings).$after : $before.bsa_number_format(0).$after ?></strong>
			</div>
		</div>
		<div class="bsaDashboard-stat">
			<div class="bsaDashboard-inner redBg">
				<span class="dashicons dashicons-backup"></span>
				<span class="bsaDashboard-title">Pending Withdrawals</span>
				<strong class="bsaDashboard-amount"><?php echo ( $pendingWithdrawals ) ? $before.bsa_number_format($pendingWithdrawals).$after : $before.bsa_number_format(0).$after ?></strong>
			</div>
		</div>
	</div>
</div>

<?php  $model->getAdminAction();
if ( $model->validationAccept() ) {
	echo '
	<div class="updated settings-error" id="setting-error-settings_updated">
		<p><div class="bsaLoader"></div><strong>Ad has been accepted.</strong></p>
	</div>';
} elseif ( $model->validationBlocked() ) {
	echo '
		<div class="updated settings-error" id="setting-error-settings_updated">
			<p><div class="bsaLoader"></div><strong>Ad blocked.</strong></p>
		</div>';
} elseif ($model->validationPaid()) {
	echo '
		<div class="updated settings-error" id="setting-error-settings_updated">
			<p><div class="bsaLoader"></div><strong>Ad marked as paid.</strong></p>
		</div>';
} ?>

<h3>Pending Ads</h3>
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
	if (count($getPendingAds) > 0) {
		foreach ($getPendingAds as $key => $entry) {

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
					<strong><a href="<?php echo $entry['url']; ?>"><?php echo $entry['title']; ?></a></strong>
					<?php echo ( $entry['description'] != '' ) ? $entry['description'] : ''; ?>
					<?php echo ( $entry['html'] != '' ) ? 'HTML' : '' ; ?>
					<div class="row-actions">
						<form action="" method="post">
							<input type="hidden" value="accept" name="bsaProAction">
							<input type="hidden" value="<?php echo $entry['id']; ?>" name="orderId">
							<span class="bsaProActionBtn bsaPaidBtn"><input type="submit" value="Accept this Ad" id="submitAccept" name="submit"></span>
						</form>
						|
						<?php if ( $entry['paid'] == 0 || $entry['paid'] == NULL ): ?>
							<form action="" method="post">
								<input type="hidden" value="paid" name="bsaProAction">
								<input type="hidden" value="<?php echo $entry['id']; ?>" name="orderId">
								<span class="bsaProActionBtn bsaPaidBtn"><input type="submit" value="Mark as paid" id="submitPaid" name="submit"></span>
							</form>
							|
						<?php endif; ?>
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
					<a target="_blank" href="<?php echo get_option('bsa_pro_plugin_ordering_form_url') . (( strpos(get_option('bsa_pro_plugin_ordering_form_url'), '?') == TRUE ) ? '&' : '?') ?>bsa_pro_stats=1&bsa_pro_email=<?php echo str_replace('@', '%40', $entry['buyer_email']); ?>&bsa_pro_id=<?php echo $entry['id']; ?>">
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
					Space ID <strong><?php echo $entry['space_id']; ?></strong><br>
					Ad ID / Order ID <strong><?php echo $entry['id']; ?></strong><br>
					Billing Model <strong><?php echo strtoupper($entry['ad_model']); ?></strong><br>
					Cost <strong><?php echo $before . bsa_number_format($entry['cost']) . $after; ?></strong><br>
					<?php if ( $entry['paid'] == 1 ): ?>
						<span class="bsaColorGreen">Paid</span>
					<?php elseif ( $entry['paid'] == 2 ): ?>
						<span class="bsaColorGreen">Added via Admin Panel</span>
					<?php else: ?>
						<span class="bsaColorRed">Not paid</span>
					<?php endif; ?><br>
					<?php if ( $model->getPendingTask($entry['id'], 'ad') ): ?>
						<span class="dashicons dashicons-clock"></span> scheduled status change
					<?php endif; ?>
				</td>
			</tr>
			
		<?php
		}
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

<h3>Last Sold Ads</h3>
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
	if (count($getLastAds) > 0) {
		foreach ($getLastAds as $key => $entry) {

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
					<strong><a href="<?php echo $entry['url']; ?>"><?php echo $entry['title']; ?></a></strong>
					<?php echo ( $entry['description'] != '' ) ? $entry['description'] : ''; ?>
					<?php echo ( $entry['html'] != '' ) ? 'HTML' : '' ; ?>
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
					<a target="_blank" href="<?php echo get_option('bsa_pro_plugin_ordering_form_url') . (( strpos(get_option('bsa_pro_plugin_ordering_form_url'), '?') == TRUE ) ? '&' : '?') ?>bsa_pro_stats=1&bsa_pro_email=<?php echo str_replace('@', '%40', $entry['buyer_email']); ?>&bsa_pro_id=<?php echo $entry['id']; ?>">
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
					Space ID <strong><?php echo $entry['space_id']; ?></strong><br>
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


<script>
	(function($){
		// - start - open page
		var bsaItemsWrap = $('.wrap');
		bsaItemsWrap.hide();

		setTimeout(function(){
			bsaItemsWrap.fadeIn(400);
		}, 400);
		// - end - open page

		<?php if ( $model->validationAccept() or $model->validationBlocked() or $model->validationPaid() ) { ?>
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
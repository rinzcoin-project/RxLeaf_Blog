<?php

// -- START -- Set API version
if ( !isset($_SESSION['bsa_pro_plugin_agency_api_version']) OR $_SESSION['bsa_pro_plugin_agency_api_version'] != '1.8.4' ) {
	update_option('bsa_pro_plugin_agency_api_version', '1.8.4');
	$_SESSION['bsa_pro_plugin_agency_api_version'] = get_option('bsa_pro_plugin_agency_api_version');
}
// -- END -- Set API version

function bsa_pro_sub_menu_agency()
{
	?>
	<div class="wrap" style="display:none">
		<?php require_once 'agency.php'; ?>
	</div>
<?php
}

function bsa_pro_sub_menu_ma_items()
{
	?>
	<div class="wrap" style="display:none">
		<?php require_once 'items.php'; ?>
	</div>
<?php
}

function bsa_pro_sub_menu_ma_new_site()
{
	?>
	<div class="wrap" style="display:none">
		<?php bsaMaNewSite(); ?>
		<?php require_once 'add-site.php'; ?>
	</div>
<?php
}

function bsaMaNewSite()
{
	$plugin_id = 'bsa_pro_plugin_';

	if ($_SERVER["REQUEST_METHOD"] == "POST" && $_POST["bsaProAction"] == 'addMaNewSite' ||
		$_SERVER["REQUEST_METHOD"] == "POST" && $_POST["bsaProAction"] == 'updateMaSite') {

		if ( isset($_GET['site_id']) && bsa_verify_role($_GET['site_id'], 'site') ) {
			unset($_SESSION['bsa_ad_'.$_GET['site_id']]);
		}

		if ( isset($_POST["title"]) && $_POST["title"] != '' && isset($_POST["url"]) && $_POST["url"] != '' && isset($_POST["status"]) && $_POST["status"] != '' ) {

			$model = new BSA_PRO_Model();
			$url = str_replace('/', '', str_replace('www.', '', str_replace('http://', '', str_replace('https://', '', $_POST["url"]))));
			$urlDB = str_replace('/', '', str_replace('www.', '', str_replace('http://', '', str_replace('https://', '', $model->siteExists($_POST["url"])))));

			if ( $url != $urlDB || isset($_GET['site_id']) && bsa_verify_role($_GET['site_id'], 'site') ) {

				// if isset img
				if ( $_FILES['thumb']["name"] ) {
					$allowedExts = array("gif", "jpeg", "jpg", "png", "GIF", "JPEG", "JPG", "PNG",);
					$temp = explode(".", $_FILES["thumb"]["name"]);
					$extension = end($temp);
					$fileName = NULL;

					if ((($_FILES["thumb"]["type"] == "image/gif")
							|| ($_FILES["thumb"]["type"] == "image/jpeg")
							|| ($_FILES["thumb"]["type"] == "image/jpg")
							|| ($_FILES["thumb"]["type"] == "image/pjpeg")
							|| ($_FILES["thumb"]["type"] == "image/x-png")
							|| ($_FILES["thumb"]["type"] == "image/png"))
						&& $_FILES["thumb"]["error"] == 0
						&& in_array($extension, $allowedExts)) {

						$fileName = time().'-'.$_FILES["thumb"]["name"];
						$path     = bsa_upload_url('basedir').$fileName;
						$thumbLoc = $_FILES["thumb"]["tmp_name"];

						list($width, $height) = getimagesize($thumbLoc);
						$maxSize = get_option($plugin_id.'thumb_size');
						$maxWidth = get_option($plugin_id.'thumb_w');
						$maxHeight = get_option($plugin_id.'thumb_h');

						if (($_FILES["thumb"]["size"] > $maxSize * 1024) OR $width > $maxWidth OR $height > $maxHeight) {
							echo '
						<div class="updated settings-error">
							<p><strong>Ad has not been saved.</strong> Images was too high.</p>
						</div>';
							return;
						} else {
							// save img
							move_uploaded_file($thumbLoc, $path);
						}
					} else {
						echo '
					<div class="updated settings-error">
						<p><strong>Ad has not been saved.</strong> Type of image invalid.</p>
					</div>
					';
						return;
					}
				} else {
					$fileName = '';
				}

				if ( get_option($plugin_id.'agency_auto_accept') == 'yes' ) {
					$status = 'active';
				} else {
					$status = 'pending';
				}

				$model = new BSA_PRO_Model();

				if ( $_POST["bsaProAction"] == 'updateMaSite' ) {
					if ( bsa_role() == 'admin' ) {
						$model->updateSite(	$_GET['site_id'], $_POST["title"], $_POST["url"], $fileName, $_POST["status"]);
					} else {
						$model->updateSite($_GET['site_id'], $_POST["title"], bsa_site($_GET['site_id'], 'url'), $fileName, $_POST["status"]);
					}
				} else {
					$model->addNewSite(	NULL, $_POST["title"], $_POST["url"], $fileName, $status);
				}
				$_SESSION['bsa_site_status'] = 'site_added';

				echo '
						<div class="updated settings-error">
							<p><strong>Success!</strong> Site saved.</p>
						</div>';

			} else {

				echo '
				<div class="updated settings-error">
					<p><strong>Site has not been saved.</strong> Site exists in system or invalid url.</p>
				</div>';

			}

		} else {

			echo '
			<div class="updated settings-error">
				<p><strong>Site has not been saved.</strong> The title and URL fields are required!</p>
			</div>';

		}
	}
}

function bsa_pro_sub_menu_ma_new_space()
{
	?>
	<div class="wrap" style="display:none">
		<?php bsaMaAddNewSpace(); ?>
		<?php require_once 'add-space.php'; ?>
	</div>
<?php
}

function bsaMaAddNewSpace()
{

	if ( $_SERVER["REQUEST_METHOD"] == "POST" && $_POST["bsaProAction"] == 'updateMaSpace' ) {

		if ( isset($_POST["site_id"]) && bsa_verify_role($_POST['site_id'], 'site') && isset($_GET['space_id']) && bsa_verify_role($_GET['space_id'], 'space') ) {

			if ( isset($_SESSION['bsa_space_'.$_GET['space_id']]) || isset($_SESSION['bsaProSpace'.$_GET['space_id']]) ) {
				unset($_SESSION['bsa_space_'.$_GET['space_id']]); // Reset cache
				unset($_SESSION['bsaProSpace'.$_GET['space_id']]); // Reset cache
			}

			if ( $_POST["name"] != '' ) {

				$model = new BSA_PRO_Model();
				$cpc_status = $model->billingValidator('cpc');
				$cpm_status = $model->billingValidator('cpm');
				$cpd_status = $model->billingValidator('cpd');

				if ( $cpc_status == 'isset' || $cpc_status == 'incorrect' || $cpm_status == 'isset' || $cpm_status == 'incorrect' || $cpd_status == 'isset' || $cpd_status == 'incorrect' ) {

					if ( $_POST['max_items'] < $model->countAds($_GET['space_id']) || $cpc_status == 'incorrect' || $cpm_status == 'incorrect' || $cpd_status == 'incorrect' ) {

						if ( $_POST['max_items'] < $model->countAds($_GET['space_id']) ) { // if ad limit smaller than number of active ads

							echo '
							<div class="updated settings-error">
								<p><strong>Error!</strong> You have more active ads than ad limit.<br><br>
								<strong>Note!</strong><br>
								Increase number of maximum ads.</p>
							</div>';

						} else {

							echo '
							<div class="updated settings-error">
								<p><strong>Error, incorrect contract values!</strong> Please enter the price and values for some contract, add them one by one (all values must be different).<br><br>
								<strong>Note!</strong><br>
								Enter <strong>0</strong> in the price field if you do not want to use some of billing model.</p>
							</div>';
						}

					} else {

						$model->updateSpace(
							$_GET['space_id'],
							$_POST['name'],
							$_POST['title'],
							$_POST['add_new'],
							($_POST['cpc_price'] > 0 ? $_POST['cpc_price'] : 0),
							($_POST['cpm_price'] > 0 ? $_POST['cpm_price'] : 0),
							($_POST['cpd_price'] > 0 ? $_POST['cpd_price'] : 0),
							($_POST['cpc_price'] > 0 ? ($_POST['cpc_contract_1'] > 0 ? $_POST['cpc_contract_1'] : 0) : 0),
							($_POST['cpc_price'] > 0 ? ($_POST['cpc_contract_2'] > 0 ? $_POST['cpc_contract_2'] : 0) : 0),
							($_POST['cpc_price'] > 0 ? ($_POST['cpc_contract_3'] > 0 ? $_POST['cpc_contract_3'] : 0) : 0),
							($_POST['cpm_price'] > 0 ? ($_POST['cpm_contract_1'] > 0 ? $_POST['cpm_contract_1'] : 0) : 0),
							($_POST['cpm_price'] > 0 ? ($_POST['cpm_contract_2'] > 0 ? $_POST['cpm_contract_2'] : 0) : 0),
							($_POST['cpm_price'] > 0 ? ($_POST['cpm_contract_3'] > 0 ? $_POST['cpm_contract_3'] : 0) : 0),
							($_POST['cpd_price'] > 0 ? ($_POST['cpd_contract_1'] > 0 ? $_POST['cpd_contract_1'] : 0) : 0),
							($_POST['cpd_price'] > 0 ? ($_POST['cpd_contract_2'] > 0 ? $_POST['cpd_contract_2'] : 0) : 0),
							($_POST['cpd_price'] > 0 ? ($_POST['cpd_contract_3'] > 0 ? $_POST['cpd_contract_3'] : 0) : 0),
							($_POST['discount_2'] > 0 && $_POST['discount_2'] <= 100 ? $_POST['discount_2'] : 0),
							($_POST['discount_3'] > 0 && $_POST['discount_3'] <= 100 ? $_POST['discount_3'] : 0),
							$_POST['grid_system'],
							$_POST['template'],
							( isset($_POST['display_type']) && $_POST['display_type'] != '' ? $_POST['display_type'] : 'default' ),
							$_POST['random'],
							$_POST['max_items'],
							$_POST['col_per_row'],
							( isset($_POST['font']) && $_POST['font'] != '' ? $_POST['font'] : null ),
							( isset($_POST['font_url']) && $_POST['font_url'] != '' ? $_POST['font_url'] : null ),
							$_POST['header_bg'],
							$_POST['header_color'],
							$_POST['link_color'],
							$_POST['ads_bg'],
							$_POST['ad_bg'],
							$_POST['ad_title_color'],
							$_POST['ad_desc_color'],
							$_POST['ad_url_color'],
							$_POST['ad_extra_color_1'],
							$_POST['ad_extra_color_2'],
							( isset($_POST['animation']) && $_POST['animation'] != '' ? $_POST['animation'] : 'none' ),
							( isset($_POST['space_categories']) && $_POST['space_categories'] != '' ? implode(",", $_POST['space_categories']) : null ),
							( isset($_POST['space_tags']) && $_POST['space_tags'] != '' ? implode(",", $_POST['space_tags']) : null ),
							( isset($_POST['show_in_country']) && $_POST['show_in_country'] != '' ? implode(",", $_POST['show_in_country']) : null ),
							( isset($_POST['hide_in_country']) && $_POST['hide_in_country'] != '' ? implode(",", $_POST['hide_in_country']) : null ),
							( isset($_POST['show_in_advanced']) && $_POST['show_in_advanced'] != '' ? $_POST['show_in_advanced'] : null ),
							( isset($_POST['hide_in_advanced']) && $_POST['hide_in_advanced'] != '' ? $_POST['hide_in_advanced'] : null ),
							( isset($_POST['devices']) && $_POST['devices'] != '' ? implode(",", $_POST['devices']) : null ),
							( isset($_POST['unavailable_dates']) && $_POST['unavailable_dates'] != '' ? $_POST['unavailable_dates'] : null ),
							'0,0,0',
							null,
							( bsa_space($_GET['space_id'], 'status') == 'active' ? 'active' : 'inactive' ),
							$_GET['site_id'],
							'user'
						);

						echo '
					<div class="updated settings-error">
						<p><strong>Space updated.</strong></p>
					</div>';
					}
				} else {

					echo '
				<div class="updated settings-error">
					<p><strong>Error, empty contracts!</strong> You should set at least one contract.</p>
				</div>';
				}

			} else {

				echo '
			<div class="updated settings-error">
				<p><strong>Space has not been saved.</strong> The name field is required!</p>
			</div>';
			}

		} else {

			echo '
			<div class="updated settings-error">
				<p><strong>Space has not been saved.</strong> You have not permissions to this site!</p>
			</div>';
		}

	} elseif ( $_SERVER["REQUEST_METHOD"] == "POST" && $_POST["bsaProAction"] == 'addMaNewSpace' ) {

		if ( isset($_POST["site_id"]) && bsa_verify_role($_POST['site_id'], 'site') ) {

			if ( $_POST["name"] != '' ) {

				$model = new BSA_PRO_Model();
				$cpc_status = $model->billingValidator('cpc');
				$cpm_status = $model->billingValidator('cpm');
				$cpd_status = $model->billingValidator('cpd');

				if ( $cpc_status == 'isset' || $cpc_status == 'incorrect' || $cpm_status == 'isset' || $cpm_status == 'incorrect' || $cpd_status == 'isset' || $cpd_status == 'incorrect' ) {

					if ( $cpc_status == 'incorrect' || $cpm_status == 'incorrect' || $cpd_status == 'incorrect' ) {

						echo '
					<div class="updated settings-error">
						<p><strong>Error, incorrect contract!</strong> Please enter the price and value for 1st Contract. Note! If you want to add more contracts, add them one by one.</p>
					</div>';

					} else {

						$model->addNewSpace(
							NULL,
							$_POST['name'],
							$_POST['title'],
							$_POST['add_new'],
							($_POST['cpc_price'] > 0 ? $_POST['cpc_price'] : 0),
							($_POST['cpm_price'] > 0 ? $_POST['cpm_price'] : 0),
							($_POST['cpd_price'] > 0 ? $_POST['cpd_price'] : 0),
							($_POST['cpc_price'] > 0 ? ($_POST['cpc_contract_1'] > 0 ? $_POST['cpc_contract_1'] : 0) : 0),
							($_POST['cpc_price'] > 0 ? ($_POST['cpc_contract_2'] > 0 ? $_POST['cpc_contract_2'] : 0) : 0),
							($_POST['cpc_price'] > 0 ? ($_POST['cpc_contract_3'] > 0 ? $_POST['cpc_contract_3'] : 0) : 0),
							($_POST['cpm_price'] > 0 ? ($_POST['cpm_contract_1'] > 0 ? $_POST['cpm_contract_1'] : 0) : 0),
							($_POST['cpm_price'] > 0 ? ($_POST['cpm_contract_2'] > 0 ? $_POST['cpm_contract_2'] : 0) : 0),
							($_POST['cpm_price'] > 0 ? ($_POST['cpm_contract_3'] > 0 ? $_POST['cpm_contract_3'] : 0) : 0),
							($_POST['cpd_price'] > 0 ? ($_POST['cpd_contract_1'] > 0 ? $_POST['cpd_contract_1'] : 0) : 0),
							($_POST['cpd_price'] > 0 ? ($_POST['cpd_contract_2'] > 0 ? $_POST['cpd_contract_2'] : 0) : 0),
							($_POST['cpd_price'] > 0 ? ($_POST['cpd_contract_3'] > 0 ? $_POST['cpd_contract_3'] : 0) : 0),
							($_POST['discount_2'] > 0 && $_POST['discount_2'] <= 100 ? $_POST['discount_2'] : 0),
							($_POST['discount_3'] > 0 && $_POST['discount_3'] <= 100 ? $_POST['discount_3'] : 0),
							$_POST['grid_system'],
							$_POST['template'],
							( isset($_POST['display_type']) && $_POST['display_type'] != '' ? $_POST['display_type'] : 'default' ),
							$_POST['random'],
							$_POST['max_items'],
							$_POST['col_per_row'],
							( isset($_POST['font']) && $_POST['font'] != '' ? $_POST['font'] : null ),
							( isset($_POST['font_url']) && $_POST['font_url'] != '' ? $_POST['font_url'] : null ),
							$_POST['header_bg'],
							$_POST['header_color'],
							$_POST['link_color'],
							$_POST['ads_bg'],
							$_POST['ad_bg'],
							$_POST['ad_title_color'],
							$_POST['ad_desc_color'],
							$_POST['ad_url_color'],
							$_POST['ad_extra_color_1'],
							$_POST['ad_extra_color_2'],
							( isset($_POST['animation']) && $_POST['animation'] != '' ? $_POST['animation'] : 'none' ),
							( isset($_POST['space_categories']) && $_POST['space_categories'] != '' ? implode(",", $_POST['space_categories']) : null ),
							( isset($_POST['space_tags']) && $_POST['space_tags'] != '' ? implode(",", $_POST['space_tags']) : null ),
							( isset($_POST['show_in_country']) && $_POST['show_in_country'] != '' ? implode(",", $_POST['show_in_country']) : null ),
							( isset($_POST['hide_in_country']) && $_POST['hide_in_country'] != '' ? implode(",", $_POST['hide_in_country']) : null ),
							( isset($_POST['show_in_advanced']) && $_POST['show_in_advanced'] != '' ? $_POST['show_in_advanced'] : null ),
							( isset($_POST['hide_in_advanced']) && $_POST['hide_in_advanced'] != '' ? $_POST['hide_in_advanced'] : null ),
							( isset($_POST['devices']) && $_POST['devices'] != '' ? implode(",", $_POST['devices']) : null ),
							( isset($_POST['unavailable_dates']) && $_POST['unavailable_dates'] != '' ? $_POST['unavailable_dates'] : null ),
							'0,0,0',
							null,
							'active',
							$_POST['site_id'],
							'user'
						);

						$_SESSION['bsa_space_status'] = 'space_added';

						?>
					<div class="updated settings-error">
						<p><strong>Space saved.</strong> Click <a href="<?php echo admin_url(); ?>admin.php?page=bsa-pro-sub-menu-ma-spaces">here</a> to show all spaces.</p>
					</div><?php
					}
				} else {

					echo '
				<div class="updated settings-error">
					<p><strong>Error, empty contracts!</strong> You should set at least one contract.</p>
				</div>';
				}

			} else {

				echo '
			<div class="updated settings-error">
				<p><strong>Space has not been saved.</strong> The name field is required!</p>
			</div>';
			}

		} else {

			echo '
			<div class="updated settings-error">
				<p><strong>Space has not been saved.</strong> You have not permissions to this site!</p>
			</div>';
		}
	}
}

function bsa_pro_sub_menu_ma_new_ad()
{
	?>
	<div class="wrap" style="display:none">
		<?php bsaMaAddNewAd(); ?>
		<?php require_once 'add-ad.php'; ?>
	</div>
<?php
}

function bsaMaAddNewAd()
{
	$plugin_id = 'bsa_pro_plugin_';

	if ($_SERVER["REQUEST_METHOD"] == "POST" && $_POST["bsaProAction"] == 'updateMaAd') {

		if ( isset($_GET['ad_id']) && bsa_verify_role($_GET['ad_id'], 'ad') ) {

			unset($_SESSION['bsa_ad_'.$_GET['ad_id']]); // Reset cache

			// validate form
			if ( isset($_GET['ad_id']) && bsa_space(bsa_ad($_GET['ad_id'], 'space_id'), 'template') != 'html' ) {
				foreach (explode(',', str_replace('desc', 'description', $_POST['inputs_required'])) as $input) {
					$error = FALSE;
					if ($input == 'img') {
						if ($_FILES['img']["name"] == '') {
							$error = FALSE; // img not required for updateAd Action
						}
					} else {
						if ($_POST[$input] == '') {
							$error = TRUE;
						}
					}
					if ($error == TRUE) {
						echo '
				<div class="updated settings-error">
					<p><strong>Ad has not been saved.</strong> The ' . str_replace(',', ', ', str_replace('desc', 'description', $_POST['inputs_required'])) . ' fields are required!</p>
				</div>';
						return;
					}
				}
			}

			if ( $_POST["buyer_email"] != '' ) {

				// if isset img
				$uploadName = strtolower($_FILES["img"]["name"]);
				if ( $uploadName ) {
					$allowedExts = array("gif", "jpeg", "jpg", "png");
					$temp = explode(".", $uploadName);
					$extension = end($temp);
					$fileName = NULL;

					if ((($_FILES["img"]["type"] == "image/gif")
							|| ($_FILES["img"]["type"] == "image/jpeg")
							|| ($_FILES["img"]["type"] == "image/jpg")
							|| ($_FILES["img"]["type"] == "image/pjpeg")
							|| ($_FILES["img"]["type"] == "image/x-png")
							|| ($_FILES["img"]["type"] == "image/png"))
						&& $_FILES["img"]["error"] == 0
						&& in_array($extension, $allowedExts)) {

						$fileName = time().'-'.$uploadName;
						$path     = bsa_upload_url('basedir').$fileName;
						$thumbLoc = $_FILES["img"]["tmp_name"];

						list($width, $height) = getimagesize($thumbLoc);
						$maxSize = get_option($plugin_id.'thumb_size');
						$maxWidth = get_option($plugin_id.'thumb_w');
						$maxHeight = get_option($plugin_id.'thumb_h');

						if (($_FILES["img"]["size"] > $maxSize * 1024) OR $width > $maxWidth OR $height > $maxHeight) {
							echo '
						<div class="updated settings-error">
							<p><strong>Ad has not been saved.</strong> Images was too high.</p>
						</div>';
							return;
						} else {
							// save img
							move_uploaded_file($thumbLoc, $path);
						}
					} else {
						echo '
					<div class="updated settings-error">
						<p><strong>Ad has not been saved.</strong> Type of image invalid.</p>
					</div>
					';
						return;
					}
				} else {
					$fileName = NULL;
				}

				$limit = bsa_ad($_GET['ad_id'], 'ad_limit');
				if ( isset($_POST["increase_limit"]) && $_POST["increase_limit"] != '' ) {
					if ( $_POST["increase_limit"] > 0 || $_POST["increase_limit"] < 0 ) { // increase / decrease limit
						if ( bsa_ad($_GET['ad_id'], 'ad_model') == 'cpd' ) {
							$time = time();
							$increase = $_POST["increase_limit"] * 24 * 60 * 60;
							$diff = $limit - $time;
							$increase_limit = ($diff <= 0) ? $time + $increase : $limit + $increase;
						} else {
							$increase_limit = $limit + $_POST["increase_limit"];
						}
					} else {
						$increase_limit = bsa_ad($_GET['ad_id'], 'ad_limit');
					}
				} else {
					$increase_limit = null;
				}

				$model = new BSA_PRO_Model();

				$capping = ( $_POST["capping"] > 0 ? number_format($_POST["capping"], 0, '', '') : 0);
				$ad_name = ( isset($_POST["ad_name"]) && $_POST["ad_name"] != '' ) ? $_POST["ad_name"] : null;
				$optional_field = ( isset($_POST["optional_field"]) && $_POST["optional_field"] != '' ) ? $_POST["optional_field"] : null;

				$model->updateAd( $_GET['ad_id'], $ad_name, $_POST["buyer_email"], $_POST["title"], $_POST["description"], $_POST["url"], $fileName, stripslashes( $_POST["html"] ), $capping, $optional_field, $increase_limit );

				unset($_SESSION['bsa_ad_'.$_GET['ad_id']]); // Reset cache

				echo '
						<div class="updated settings-error">
							<p><strong>Success!</strong> Ad saved.</p>
						</div>';

			} else {

				echo '
			<div class="updated settings-error">
				<p><strong>Ad has not been saved.</strong> The buyer email field is required!</p>
			</div>';

			}

		} else {

			echo '
			<div class="updated settings-error">
				<p><strong>Ad has not been saved.</strong> You have not permissions to this site!</p>
			</div>';
		}

	} elseif ($_SERVER["REQUEST_METHOD"] == "POST" && $_POST["bsaProAction"] == 'addMaNewAd') {

		if ( isset($_POST["space_id"]) && bsa_verify_role($_POST["space_id"], 'space') ) {

			// validate form
			if ( isset($_POST["space_id"]) && bsa_space($_POST["space_id"], 'template') != 'html' ) {
				foreach (explode(',', str_replace('desc', 'description', $_POST['inputs_required'])) as $input) {
					$error = FALSE;
					if ($input == 'img') {
						if ($_FILES['img']["name"] == '') {
							$error = TRUE;
						}
					} else {
						if ($_POST[$input] == '') {
							$error = TRUE;
						}
					}
					if ($error == TRUE) {
						echo '
				<div class="updated settings-error">
					<p><strong>Ad has not been saved.</strong> The ' . str_replace(',', ', ', str_replace('desc', 'description', $_POST['inputs_required'])) . ' fields are required!</p>
				</div>';
						return;
					}
				}
			}

			if ( isset($_POST["buyer_email"]) && $_POST["buyer_email"] != '' && isset($_POST["space_id"]) && $_POST["space_id"] != '' && isset($_POST["ad_model"]) && $_POST["ad_model"] != '' && isset($_POST["ad_limit_" . $_POST["ad_model"]]) && $_POST["ad_limit_" . $_POST["ad_model"]] != '' ) {

				// if isset img
				if ( $_FILES['img']["name"] ) {
					$allowedExts = array("gif", "jpeg", "jpg", "png", "GIF", "JPEG", "JPG", "PNG",);
					$temp = explode(".", $_FILES["img"]["name"]);
					$extension = end($temp);
					$fileName = NULL;

					if ((($_FILES["img"]["type"] == "image/gif")
							|| ($_FILES["img"]["type"] == "image/jpeg")
							|| ($_FILES["img"]["type"] == "image/jpg")
							|| ($_FILES["img"]["type"] == "image/pjpeg")
							|| ($_FILES["img"]["type"] == "image/x-png")
							|| ($_FILES["img"]["type"] == "image/png"))
						&& $_FILES["img"]["error"] == 0
						&& in_array($extension, $allowedExts)) {

						$fileName = time().'-'.$_FILES["img"]["name"];
						$path     = bsa_upload_url('basedir').$fileName;
						$thumbLoc = $_FILES["img"]["tmp_name"];

						list($width, $height) = getimagesize($thumbLoc);
						$maxSize = get_option($plugin_id.'thumb_size');
						$maxWidth = get_option($plugin_id.'thumb_w');
						$maxHeight = get_option($plugin_id.'thumb_h');

						if (($_FILES["img"]["size"] > $maxSize * 1024) OR $width > $maxWidth OR $height > $maxHeight) {
							echo '
						<div class="updated settings-error">
							<p><strong>Ad has not been saved.</strong> Images was too high.</p>
						</div>';
							return;
						} else {
							// save img
							move_uploaded_file($thumbLoc, $path);
						}
					} else {
						echo '
					<div class="updated settings-error">
						<p><strong>Ad has not been saved.</strong> Type of image invalid.</p>
					</div>
					';
						return;
					}
				} else {
					$fileName = '';
				}

				// set limit for cpd - change days to timestamp
				if ( $_POST["ad_model"] == 'cpd' ) {
					$ad_limit = time() + ($_POST["ad_limit_" . $_POST["ad_model"]] * 24 * 60 * 60);
				} else {
					$ad_limit = $_POST["ad_limit_" . $_POST["ad_model"]];
				}

				$model = new BSA_PRO_Model();

				$capping = ( $_POST["capping"] > 0 ? number_format($_POST["capping"], 0, '', '') : 0);
				$ad_name = ( isset($_POST["ad_name"]) && $_POST["ad_name"] != '' ) ? $_POST["ad_name"] : null;
				$optional_field = ( isset($_POST["optional_field"]) && $_POST["optional_field"] != '' ) ? $_POST["optional_field"] : null;
				$model->addNewAd(	NULL, $_POST["space_id"], $ad_name, $_POST["buyer_email"], $_POST["title"], $_POST["description"], $_POST["url"], $fileName,  stripslashes( $_POST["html"] ), $capping, $optional_field,
					$_POST["ad_model"], $ad_limit, 0.00, 2, 'active'); // paid 2 - Added via Admin Panel

				$_SESSION['bsa_ad_status'] = 'ad_added';

				echo '
						<div class="updated settings-error">
							<p><strong>Success!</strong> Ad saved.</p>
						</div>';

			} else {

				echo '
			<div class="updated settings-error">
				<p><strong>Ad has not been saved.</strong> The buyer email, space id, billing model fields are required!</p>
			</div>';

			}

		} else {

			echo '
			<div class="updated settings-error">
				<p><strong>Ad has not been saved.</strong> You have not permissions to this ad!</p>
			</div>';
		}
	}
}

function bsa_pro_sub_menu_ma_withdrawal()
{
	?>
	<div class="wrap" style="display:none">
		<?php bsaMaWithdrawal(); ?>
		<?php require_once 'withdrawal.php'; ?>
	</div>
<?php
}

function bsaMaWithdrawal()
{

}

function bsa_pro_create_ma_menu()
{
	if ( is_multisite() && is_main_site() || !is_multisite() ) {
		$privilege = (get_option('bsa_pro_plugin_'.'private_ma') == 'yes') ? 'manage_options' : 'read' ;
		add_submenu_page('bsa-pro-sub-menu', 'Marketing Agency', 'Marketing Agency', $privilege, 'bsa-pro-sub-menu-agency', 'bsa_pro_sub_menu_agency');
		add_submenu_page('bsa-pro-sub-menu', 'MA - Sites Manager', 'MA - Sites Manager', $privilege, 'bsa-pro-sub-menu-ma-spaces', 'bsa_pro_sub_menu_ma_items');
		add_submenu_page('bsa-pro-sub-menu', 'MA - Add new Site', 'MA - Add new Site', $privilege, 'bsa-pro-sub-menu-ma-new-site', 'bsa_pro_sub_menu_ma_new_site');
		add_submenu_page('bsa-pro-sub-menu', 'MA - Add new Space', 'MA - Add new Space', $privilege, 'bsa-pro-sub-menu-ma-new-space', 'bsa_pro_sub_menu_ma_new_space');
		add_submenu_page('bsa-pro-sub-menu', 'MA - Add new Ad', 'MA - Add new Ad', $privilege, 'bsa-pro-sub-menu-ma-new-ad', 'bsa_pro_sub_menu_ma_new_ad');
		add_submenu_page('bsa-pro-sub-menu', 'MA - Withdrawals', 'MA - Withdrawals', $privilege, 'bsa-pro-sub-menu-ma-withdrawal', 'bsa_pro_sub_menu_ma_withdrawal');
	}
}
add_action('admin_menu', 'bsa_pro_create_ma_menu');

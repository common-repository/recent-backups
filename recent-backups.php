<?php
/*
Plugin Name: Recent Backups
Plugin URI: http://www.andycheeseman.com/
Description: To be used with the BackupWordPress plugin to list the contents of the backup directory in a dashboard widget.
Version: 0.7
Author: Andy Cheeseman
Author URI: http://www.andycheeseman.com/
License: GPL2
*/

/*
With thanks to James Ellis (http://KasperWire.com/) for providing feedback for legacy compatibility addressed in 0.7.
*/


if( !class_exists( 'RecentBackups_DashboardWidget') ) {
	class RecentBackups_DashboardWidget {
		function recent_backups_dashboard_widget() {
		 
			//Define the backup folder location for glob
			$backup_location = get_option('custom_backup_location');
			
			//Define the plugin folder url for download-file.php link
			$plugin_location = site_url() . "/wp-content/plugins/recent-backups/";
			
			//File Count
			$file_count = count(glob($backup_location . "*.*"));
			
			//File Count Display
			if ($file_count < 1){
				echo 'You don&#039t seem to have any backups. If you know this is incorrect, try customising the backup location.</br></br>';
				
				//Echo the 'Backup Now' Button
				echo '<a href="' . site_url() . '/wp-admin/tools.php?page=backupwordpress&action=hmbkp_backup_now"><b>Create Your First Backup</b></a> &nbsp;<i>or</i>&nbsp; <a href="' . site_url() . '/wp-admin/options-general.php?page=recent-backups/recent-backups.php"><b>Customise Backup Location</b></a></br>';
			
			 
			} else {
			
			
			//Filter the discovered files
			$files = glob($backup_location . "*.*");
			
			//Add file to Array
			$files = array_combine($files, array_map("filemtime", $files));
			arsort($files);
			
			//Echo the filtered files in date order as download links via download-file.php
			foreach($files as $file => $mtime){
			
				//Get the File Size
				$filesize = filesize($file);
					if ($filesize < 1024){
						$display_size = round($filesize, 1) . ' B';
					} elseif ($filesize < 1048576){
						$display_size = round($filesize * .0009765625, 1) . ' kB';
					} elseif ($filesize < 1073741824){
						$display_size = round($filesize * .0009765625 * .0009765625, 1) . ' MB';
					} else {
						$display_size = round($filesize * .0009765625 * .0009765625 * .0009765625, 1) . ' GB';
					}
					
				//Get the Modified Date
				$display_date = date ("F d Y H:i", filemtime($file));
				
				//Define Hover Information
				$hover_info = basename($file);
				
				//Print
				echo $display_date . ' - <a href="' . $plugin_location . 'download-file.php?file_link=' . $backup_location . basename($file) . '" title="' . $hover_info . '"><b>' . $display_size . '</b></a></br>';
				
			}
				
				//Echo the 'Backup Now' Button
				echo '</br><a href="' . site_url() . '/wp-admin/tools.php?page=backupwordpress&action=hmbkp_backup_now"><b>Backup Now</b></a></br>';
			 
		}
		}

		function recent_backups_add_dashboard_widget() {
			wp_add_dashboard_widget( 'recent-backups', 'Recent Backups', array( 'RecentBackups_DashboardWidget', 'recent_backups_dashboard_widget' ) );
		}		
	}
	add_action( 'wp_dashboard_setup', array( 'RecentBackups_DashboardWidget', 'recent_backups_add_dashboard_widget' ) );
}




register_activation_hook(__FILE__,'recent_backups_install'); 
register_deactivation_hook( __FILE__, 'recent_backups_remove' );

function recent_backups_install() {
$backup_location = $_SERVER['DOCUMENT_ROOT'] . "wp-content/backups/";
add_option("custom_backup_location", $backup_location, '', 'yes');
}

function recent_backups_remove() {
delete_option('custom_backup_location');
}

add_action('admin_menu', 'recent_backups_admin_menu');
function recent_backups_admin_menu() {

	add_options_page('Recent Backups', 'Recent Backups', 'administrator', __FILE__, 'recent_backups_admin_menu_content');
		function recent_backups_admin_menu_content() {
			?>
				<div class="wrap">
					<?php screen_icon(); ?>
					<h2>Recent Backups Options</h2>
					
					<form method="post" action="options.php">
					<?php wp_nonce_field('update-options'); ?>
					
					<h3>Custom Backup Location</h3>
					<input name="custom_backup_location" type="text" size="70" id="custom_backup_location"
					value="<?php echo get_option('custom_backup_location'); ?>" /></br></br>
					By default this is <b><?php $backup_location = $_SERVER['DOCUMENT_ROOT'] . "wp-content/backups/"; echo $backup_location ?></b>
					
					<input type="hidden" name="action" value="update" />
					<input type="hidden" name="page_options" value="custom_backup_location" />
					
					<?php submit_button(); ?>
				</div>
			<?php
		};
}
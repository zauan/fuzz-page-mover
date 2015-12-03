<?php
/*
 * Plugin Name: Fuzz page mover
 * Plugin URI:
 * Description: Allows you to import a page made with ThemeFuzz pagebuilder
 * Author: ThemeFuzz
 * Version: 1.0
 */

class FuzzPageMover{

	private $file_name = 'page_export.txt';

	function __construct(){

		add_action( 'admin_menu', array( &$this, 'register_admin_menu' ) );
		add_action( 'init', array( &$this, 'check_page_import' ), 9999 );
	}

	function check_page_import(){

		if ( isset($_POST['zn_page_import']) && isset( $_POST['_wpnonce'] ) && wp_verify_nonce( $_POST['_wpnonce'], 'zn-page-mover')) {

			$this->do_import();

		}

	}

	function do_import(){
		$content = !empty( $_POST['page_data'] ) ? $_POST['page_data'] : false;
		$page_to_overwrite = !empty( $_POST['page_select'] ) ? $_POST['page_select'] : false;

		if( empty( $content ) || empty( $page_to_overwrite ) ) { return false; }
		$content = stripslashes( $content );
		$value = maybe_unserialize(
			preg_replace(
				'!s:(\d+):"(.*?)";!se',
				"'s:'.strlen('$2').':\"$2\";'",
				$content
			)
		);

		update_post_meta( $page_to_overwrite, 'zn_page_builder_els', $value );
		update_post_meta( $page_to_overwrite, 'zn_page_builder_status', 'enabled' );

	}


	function do_export(){
		header( 'Content-Description: File Transfer' );
		header( 'Content-Disposition: attachment; filename=' . $this->file_name );
		header( 'Content-Type: text/plain; charset=' . get_option( 'blog_charset' ), true );

		echo $this->get_data();

		die();
	}


	function get_data(){
		$options = $this->get_all_options();

		return json_encode($options);

		$data = array(

		);


	}

	function register_admin_menu(){
		add_menu_page( 'Fuzz Page mover', 'Fuzz Page mover', 'install_plugins', 'fuzz-page-mover', array( &$this, 'admin_page' ) );
	}

	function admin_page(){
		?>
		<div class="wrap">
			<h2>ThemeFuzz page mover</h2>

			<h3>Import page</h3>
			<form method="post" action="">

				<select name="page_select">
				 <option value="">
				<?php echo esc_attr( __( 'Select page' ) ); ?></option>
				 <?php
				  $pages = get_pages();
				  foreach ( $pages as $page ) {
				  	$option = '<option value="' . $page->ID . '">';
					$option .= $page->post_title;
					$option .= '</option>';
					echo $option;
				  }
				 ?>
				</select>

				<br />
				<textarea name="page_data"></textarea>

				<input type="hidden" name="zn_page_import" value="true"/>
				<?php wp_nonce_field( 'zn-page-mover' ); ?>
				<input type="submit" value="Import page" />
			</form>

		</div>

	<?php

		echo '<form>';
	}
}

new FuzzPageMover;
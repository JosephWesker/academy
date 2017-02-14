<?php
/**
 * Admin functions and actions.
 *
 * @author 		VibeThemes
 * @category 	Admin
 * @package 	Wplms-Wishlist/Includes
 * @version     1.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Wplms_Wishlist_Admin{

	public static $instance;
    
    public static function init(){

        if ( is_null( self::$instance ) )
            self::$instance = new Wplms_Wishlist_Admin();
        return self::$instance;
    }

	private function __construct(){

		$this->settings_save();
		$this->get();
		$this->add_settings();
	}

	function get(){
		$permalinks = get_option( 'wishlist_permalinks' );
		$defaults = array(
			'wishlist_slug'=>'wishlist',
			'collection_slug'=>'collection'
			);
		
		if(empty($permalinks['wishlist_slug'])){
			$permalinks['wishlist_slug'] = $defaults['wishlist_slug'];
		}
		if(empty($permalinks['collection_slug'])){
			$permalinks['collection_slug'] = $defaults['collection_slug'];
		}
		$this->permalinks = $permalinks;
	}
	function add_settings(){
		add_settings_section( 'wplms-wishlist-permalinks', __( 'Wishlist permalinks', 'wplms-wishlist' ), array( $this, 'settings' ), 'permalink' );
	}
	/**
	 * Show the settings.
	 */
	public function settings() {
		
		?>
		<table class="form-table">
			<tbody>
				<tr>
					<th><label><?php _e( 'Wishlist Slug', 'wplms-wishlist' ); ?></label></th>
					<td><input name="wishlist_slug" type="text" value="<?php echo esc_attr( $this->permalinks['wishlist_slug'] ); ?>" /></td>
				</tr>
				<tr>
					<th><label><?php _e( 'Collection Slug', 'wplms-wishlist' ); ?></label></th>
					<td><input name="collection_slug" type="text" value="<?php echo esc_attr( $this->permalinks['collection_slug'] ); ?>" /> </td>
				</tr>
			</tbody>
		</table>
		<?php
	}
	
	public function settings_save() {

		if ( ! is_admin() ) {
			return;
		}

		if ( !empty( $_POST['wishlist_slug'] ) && !empty( $_POST['collection_slug'] ) ) {
			$permalinks=array('wishlist_slug' => $_POST['wishlist_slug'],'collection_slug'=>$_POST['collection_slug']);
			update_option('wishlist_permalinks',$permalinks);
		}
	}

}

add_action( 'admin_init', array('Wplms_Wishlist_Admin','init'));




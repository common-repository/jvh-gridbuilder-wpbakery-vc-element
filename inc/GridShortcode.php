<?php

namespace JVH\Gridbuilder;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class GridShortcode
{
	private $atts;
	private $grid_id;
	private $css_classes;
	private $is_main_query;

	public function __construct( array $atts )
	{
		$this->atts = $atts;
		$this->setAttributeDefaults();

		$this->grid_id = $this->getGridId();
		$this->css_classes = $this->getCssClasses();
		$this->is_main_query = $this->isMainQuery() ? 'true' : '';
	}

	public function getOutput() : string
	{
		ob_start();

		$this->outputExtraCss();
		
		?>

		<div class="<?php echo $this->css_classes; ?>">
			<?php echo do_shortcode( $this->getOriginalWpgbShortcode() ); ?>
		</div>

		<?php

		return ob_get_clean();
	}

	private function getGridId() : int
	{
		if ( $this->hasDifferentMobileGrid() && $this->isMobile() ) {
			return (int) $this->atts['id_mobile'];
		}
		else {
			return (int) $this->atts['id'];
		}
	}

	private function isMobile() : bool
	{
		require_once __DIR__ . '/../lib/Mobile_Detect.php';
		$detect = new \Mobile_Detect();

		return $detect->isMobile();
	}

	private function hasDifferentMobileGrid() : bool
	{
		return $this->atts['different_mobile_grid'] == 'true';
	}

	private function outputExtraCss() : void
	{
		echo '<style>';

		$this->outputResponsiveCss( 'padding-left', $this->getPaddingsLeftRight() );
		$this->outputResponsiveCss( 'padding-right', $this->getPaddingsLeftRight() );
		$this->outputResponsiveCss( 'margin-left', $this->getMarginsLeftRight() );
		$this->outputResponsiveCss( 'margin-right', $this->getMarginsLeftRight() );
		$this->outputResponsiveCss( 'margin-top', $this->getMarginsTop() );
		$this->outputResponsiveCss( 'margin-bottom', $this->getMarginsBottom() );
		$this->outputResponsiveCss( 'padding-top', $this->getPaddingsTop() );
		$this->outputResponsiveCss( 'padding-bottom', $this->getPaddingsBottom() );

		$this->outputArrowsMarginTop();
		
		echo '</style>';
	}

	private function outputResponsiveCss( string $css_property, array $css_values )
	{
		if ( ! is_array( $css_values ) || count( $css_values ) === 0 ) {
			return;
		}
		
		foreach ( $css_values as $device => $css_value ) {
			switch ( $device ) {
				case 'desktop':
					echo ".wpgb_grid_jvh-$this->grid_id {";
						echo "$css_property: $css_value;";
					echo "}";
					break;
				case 'tablet':
					echo "@media (max-width: 1024px) {";
						echo ".wpgb_grid_jvh-$this->grid_id {";
							echo "$css_property: $css_value;";
						echo "}";
					echo "}";
					break;
				case 'tablet_portrait':
					echo "@media (max-width: 768px) {";
						echo ".wpgb_grid_jvh-$this->grid_id {";
							echo "$css_property: $css_value;";
						echo "}";
					echo "}";
					break;
				case 'mobile_landscape':
					echo "@media (max-width: 650px) {";
						echo ".wpgb_grid_jvh-$this->grid_id {";
							echo "$css_property: $css_value;";
						echo "}";
					echo "}";
					break;
				case 'mobile':
					echo "@media (max-width: 450px) {";
						echo ".wpgb_grid_jvh-$this->grid_id {";
							echo "$css_property: $css_value;";
						echo "}";
					echo "}";
					break;
			}
		}
	}

	private function outputArrowsMarginTop()
	{
		$margin_top = $this->getArrowsMarginTop();

		if ( ! is_array( $margin_top ) || count( $margin_top ) === 0 ) {
			return;
		}

		foreach ( $margin_top as $device => $css_value ) {
			switch ( $device ) {
				case 'desktop':
					echo ".wpgb_grid_jvh-$this->grid_id .wp-grid-builder .wpgb-prev-button, .wpgb_grid_jvh-$this->grid_id .wp-grid-builder .wpgb-next-button {";
						echo "margin-top: $css_value;";
					echo "}";
					break;
				case 'tablet':
					echo "@media (max-width: 1024px) {";
						echo ".wpgb_grid_jvh-$this->grid_id .wp-grid-builder .wpgb-prev-button, .wpgb_grid_jvh-$this->grid_id .wp-grid-builder .wpgb-next-button {";
							echo "margin-top: $css_value;";
						echo "}";
					echo "}";
					break;
				case 'tablet_portrait':
					echo "@media (max-width: 768px) {";
						echo ".wpgb_grid_jvh-$this->grid_id .wp-grid-builder .wpgb-prev-button, .wpgb_grid_jvh-$this->grid_id .wp-grid-builder .wpgb-next-button {";
							echo "margin-top: $css_value;";
						echo "}";
					echo "}";
					break;
				case 'mobile_landscape':
					echo "@media (max-width: 650px) {";
						echo ".wpgb_grid_jvh-$this->grid_id .wp-grid-builder .wpgb-prev-button, .wpgb_grid_jvh-$this->grid_id .wp-grid-builder .wpgb-next-button {";
							echo "margin-top: $css_value;";
						echo "}";
					echo "}";
					break;
				case 'mobile':
					echo "@media (max-width: 450px) {";
						echo ".wpgb_grid_jvh-$this->grid_id .wp-grid-builder .wpgb-prev-button, .wpgb_grid_jvh-$this->grid_id .wp-grid-builder .wpgb-next-button {";
							echo "margin-top: $css_value;";
						echo "}";
					echo "}";
					break;
			}
		}
	}

	private function getPaddingsLeftRight() : array
	{
		return $this->getResponsiveValues( $this->atts['padding_lr'] );
	}

	private function getMarginsLeftRight() : array
	{
		return $this->getResponsiveValues( $this->atts['margin_lr'] );
	}

	private function getMarginsTop() : array
	{
		return $this->getResponsiveValues( $this->atts['margin_top'] );
	}

	private function getMarginsBottom() : array
	{
		return $this->getResponsiveValues( $this->atts['margin_bottom'] );
	}

	private function getPaddingsTop() : array
	{
		return $this->getResponsiveValues( $this->atts['padding_top'] );
	}

	private function getPaddingsBottom() : array
	{
		return $this->getResponsiveValues( $this->atts['padding_bottom'] );
	}

	private function getArrowsMarginTop() : array
	{
		return $this->getResponsiveValues( $this->atts['arrows_margin_top'] );
	}

	private function getResponsiveValues( string $responsive_string ) : array
	{
		$css_values = [];
		
		$values = explode( ';', $responsive_string );
		
		foreach ( $values as $key => $value ) {
			$colon_position = strpos( $value, ':' );
			
			$device = substr( $value, 0, $colon_position );
			$css_value = substr( $value, $colon_position + 1 );
			
			$css_values[$device] = $css_value;	
		}
		
		return array_filter ( $css_values ); // array_filter removes empty array element
	}

	private function getOriginalWpgbShortcode() : string
	{
		return "[wpgb_grid id=\"$this->grid_id\" is_main_query=\"$this->is_main_query\"]";
	}

	private function getCssClasses() : string
	{
		$css_class = apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, vc_shortcode_custom_css_class( $this->atts['css'], ' ' ), 'wpgb_grid_jvh', $this->atts );
		$css_class = esc_attr( $css_class );
		$css_class .= ' ' . $this->atts['css_class'];
		$css_class .= ' wpgb_grid_jvh-' . $this->grid_id;
		$css_class .= ' wpb_content_element';

		return $css_class;
	}


	private function isMainQuery() : bool
	{
		// Backward compatibility, is_main_query was a separate VC option before version 2.0
		if ( $this->atts['is_main_query'] === 'true' ) {
			return true;
		}
		else if ( $this->atts['grid_type'] === 'main_query' ) {
			return true;
		}
		else {
			return false;
		}
	}

	private function setAttributeDefaults() : void
	{
		$this->atts = shortcode_atts( [
			'css' => '',
			'css_class' => '',
			'id' => '',
			'different_mobile_grid' => '',
			'id_mobile' => '',
			'is_main_query' => '',
			'grid_type' => '',
			'add_upsell_related' => '',
			'post_types' => '',
			'post__in' => '',
			'post__not_in' => '',
			'terms' => '',
			'terms__not_in' => '',
			'padding_lr' => '',
			'margin_lr' => '',
			'margin_top' => '',
			'margin_bottom' => '',
			'padding_top' => '',
			'padding_bottom' => '',
			'arrows_margin_top' => '',
		], $this->atts );
	}
}

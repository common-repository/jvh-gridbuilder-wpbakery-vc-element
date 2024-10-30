<?php

namespace JVH\Gridbuilder;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class FacetShortcode
{
	private $atts;

	public function __construct( $atts )
	{
		$this->atts = $atts;
	}

	public function getOutput() : string
	{
		$this->atts = shortcode_atts( array(
			'id' => '',
			'grid' => '',
			'css' => '',
		), $this->atts );

		$facet_id = $this->atts['id'];
		$grid_id  = $this->atts['grid'];

		$css_class = apply_filters( VC_SHORTCODE_CUSTOM_CSS_FILTER_TAG, vc_shortcode_custom_css_class( $this->atts['css'], ' ' ), 'wpgb_facet_jvh', $this->atts );
		$css_class = esc_attr( $css_class );

		ob_start();

		?>

		<div class="wpb_content_element <?php echo $css_class; ?>">
			<?php echo do_shortcode( '[wpgb_facet id="' . $facet_id . '" grid="' . $grid_id . '"]' ); ?>
		</div>

		<?php

		return ob_get_clean();
	}
}

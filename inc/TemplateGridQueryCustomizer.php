<?php

namespace JVH\Gridbuilder;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class TemplateGridQueryCustomizer
{
	private $template_content;

	public function activate() : void
	{
		add_action( 'admin_init', [$this, 'maybeAddAcfFields'] );
		add_filter( 'cptemplates/cptemplate/template_content', [$this, 'overwriteTemplateShortcodes'], 199 );
	}

	public function maybeAddAcfFields() : void
	{
		if ( $this->shouldAddAcfFields() ) {
			$this->addAcfFields();
			$this->populatePostChoices();
		}
	}

	public function overwriteTemplateShortcodes( $template_content ) : string
	{
		$this->template_content = self::addSnippetsContent( $template_content );

		foreach ( $this->getGridShortcodes() as $key => $shortcode ) {
			if ( ! $this->shouldReplaceShortcode( $key ) ) {
				continue;
			}

			$this->template_content = str_replace( $shortcode, $this->getReplacedShortcode( $key, $shortcode ), $this->template_content );
		}

		// If nothing is replaced, then just return original, otherwise issues might occur (WP Brain, console error)
		if ( $this->template_content === self::addSnippetsContent( $template_content ) ) {
			return $template_content;
		}

		$this->template_content = $this::renderSnippetWrapper( $this->template_content );

		return $this->template_content;
	}

	public static function renderSnippetWrapper( string $string ) : string
	{
		return preg_replace_callback(
			'/\[snippet-wrapper.*\](.*)\[\/snippet-wrapper\]/Us',
			function ( $matches ) {
				return do_shortcode( $matches[1] );
			},
			$string
		);
		return $string;
	}

	private function shouldReplaceShortcode( $key ) : bool
	{
		return 1 == get_post_meta( get_the_ID(), "gridbuilder_overwrite_shortcode_{$key}_should_overwrite", true );
	}

	private function getReplacedShortcode( $key, $shortcode ) : string
	{
		$grid_id = self::getGridIdFromShortcode( $shortcode );
		$atts = $this->getNewShortcodeAtts( $key );

		return "[wpgb_grid_jvh id=\"$grid_id\" grid_type=\"custom_query\" post_types=\"$atts->post_types\" post__in=\"$atts->post_in\" post__not_in=\"$atts->post_not_in\" terms=\"$atts->terms\"]";
	}

	private function getNewShortcodeAtts( int $key )
	{
		$atts = [
			'post_types' => $this->getPostTypes( $key ),
			'post__in' => $this->getPostIn( $key ),
			'post__not_in' => $this->getPostNotIn( $key ),
			'terms' => $this->getTerms( $key ),
		];

		return (object) $atts;
	}

	private function getPostTypes( int $key )
	{
		$post_types = get_post_meta( get_the_ID(), "gridbuilder_overwrite_shortcode_{$key}_post_types", true );

		return implode( ',', $post_types );
	}

	private function getPostIn( int $key )
	{
		$post__in = get_post_meta( get_the_ID(), "gridbuilder_overwrite_shortcode_{$key}_post__in", true );

		return implode( ',', $post__in );
	}

	private function getPostNotIn( int $key )
	{
		$post__not_in = get_post_meta( get_the_ID(), "gridbuilder_overwrite_shortcode_{$key}_post__not_in", true );

		return implode( ',', $post__not_in );
	}

	private function getTerms( int $key )
	{
		$terms = get_post_meta( get_the_ID(), "gridbuilder_overwrite_shortcode_{$key}_categories", true );

		return implode( ',', $terms );
	}

	public static function getGridIdFromShortcode( string $shortcode ) : int
	{
		preg_match( '/\[wpgb_grid_jvh.*id="(.*)" /U', $shortcode, $matches );

		return (int) $matches[1];
	}

	private function populatePostChoices() : void
	{
		add_filter( 'acf/load_field/name=post__in', [$this, 'getPostChoices'] );
		add_filter( 'acf/load_field/name=post__not_in', [$this, 'getPostChoices'] );
	}

	public function getPostChoices( $field ) : array
	{
		$field['choices'] = [];

		$the_query = new \WP_Query([
			'posts_per_page' => -1,
			'post_type' => 'any',
		]);

		foreach( \JVH\Gridbuilder\GridVcElement::getAllPosts() as $post ) {
			$field['choices'][$post->ID] = "$post->post_name ($post->post_type #$post->ID)";
		}
		
		return $field;
	}

	private function shouldAddAcfFields() : bool
	{
		if ( ! is_admin() ) {
			return false;
		}
		else if ( ! self::isAcfActive() ) {
			return false;
		}
		else if ( ! $this->isCptActive() ) {
			return false;
		}
		else if ( ! $this->hasPageTemplate() ) {
			return false;
		}
		else if ( ! $this->hasGridsInTemplate() ) {
			return false;
		}
		else {
			return true;
		}
	}

	private function addAcfFields() : void
	{
		acf_add_local_field_group(array(
			'key' => 'group_609ea3f6d993b',
			'title' => 'Gridbuilder overwrite Page Template shortcode queries',
			'fields' => $this->getAcfGroups(),
			'location' => array(
				array(
					array(
						'param' => 'post_type',
						'operator' => '==',
						'value' => 'all',
					),
				),
			),
			'menu_order' => 99,
			'position' => 'normal',
			'style' => 'default',
			'label_placement' => 'left',
			'instruction_placement' => 'label',
			'hide_on_screen' => '',
			'active' => true,
			'description' => '',
			'acfe_display_title' => '',
			'acfe_autosync' => array(
				0 => 'json',
			),
			'acfe_form' => 0,
			'acfe_meta' => '',
			'acfe_note' => '',
		));
	}

	private function getAcfGroups() : array
	{
		$groups = [];

		foreach ( $this->getGridShortcodes() as $key => $shortcode ) {
			$groups[] = [
					'key' => "field_609eadfa8bc6d_$key",
					'label' => "Gridbuilder overwrite shortcode",
					'name' => "gridbuilder_overwrite_shortcode_$key",
					'type' => 'group',
					'instructions' => '',
					'required' => 0,
					'conditional_logic' => 0,
					'wrapper' => array(
						'width' => '',
						'class' => '',
						'id' => '',
					),
					'dsv_formatting' => array(
						'format' => 'display',
						'disable' => '',
					),
					'group_template' => 0,
					'layout' => 'block',
					'acfe_seamless_style' => 0,
					'acfe_group_modal' => 0,
					'sub_fields' => array(
						array(
							'key' => "field_60a277aa3ce63_$key",
							'label' => 'Overwrite this Gridbuilder shortcode',
							'name' => "should_overwrite",
							'type' => 'true_false',
							'instructions' => $shortcode,
							'required' => 0,
							'conditional_logic' => 0,
							'wrapper' => array(
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'dsv_formatting' => array(
								'format' => 'display',
								'disable' => '',
							),
							'message' => '',
							'default_value' => 0,
							'ui' => 0,
							'ui_on_text' => '',
							'ui_off_text' => '',
						),
						array(
							'key' => "field_609eacb10ae91_$key",
							'label' => 'Post types',
							'name' => "post_types",
							'type' => 'acfe_post_types',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => array(
								array(
									array(
										'field' => "field_60a277aa3ce63_$key",
										'operator' => '==',
										'value' => '1',
									),
								),
							),
							'wrapper' => array(
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'dsv_formatting' => array(
								'format' => 'display',
								'disable' => '',
							),
							'post_type' => '',
							'field_type' => 'checkbox',
							'default_value' => array(
							),
							'return_format' => 'object',
							'layout' => 'horizontal',
							'toggle' => 0,
							'allow_custom' => 0,
							'multiple' => 0,
							'allow_null' => 0,
							'choices' => array(
							),
							'ui' => 1,
							'ajax' => 0,
							'placeholder' => '',
						),
						array(
							'key' => "field_60a29e42db363_$key",
							'label' => 'Included posts',
							'name' => 'post__in',
							'type' => 'select',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => array(
								array(
									array(
										'field' => "field_60a277aa3ce63_$key",
										'operator' => '==',
										'value' => '1',
									),
								),
							),
							'wrapper' => array(
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'dsv_formatting' => array(
								'format' => 'display',
								'disable' => '',
							),
							'choices' => array(
							),
							'default_value' => array(
							),
							'allow_null' => 1,
							'multiple' => 1,
							'ui' => 1,
							'return_format' => 'value',
							'placeholder' => '',
							'ajax' => 0,
						),
						array(
							'key' => "field_609eb2c5a8e38_$key",
							'label' => 'Exluded posts',
							'name' => 'post__not_in',
							'type' => 'select',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => array(
								array(
									array(
										'field' => "field_60a277aa3ce63_$key",
										'operator' => '==',
										'value' => '1',
									),
								),
							),
							'wrapper' => array(
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'dsv_formatting' => array(
								'format' => 'display',
								'disable' => '',
							),
							'choices' => array(
							),
							'default_value' => array(
							),
							'allow_null' => 1,
							'multiple' => 1,
							'ui' => 1,
							'return_format' => 'value',
							'placeholder' => '',
							'ajax' => 0,
						),
						array(
							'key' => "field_609ebbe3c1ed4_$key",
							'label' => 'Categories',
							'name' => 'categories',
							'type' => 'acfe_taxonomy_terms',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => array(
								array(
									array(
										'field' => "field_60a277aa3ce63_$key",
										'operator' => '==',
										'value' => '1',
									),
								),
							),
							'wrapper' => array(
								'width' => '',
								'class' => '',
								'id' => '',
							),
							'dsv_formatting' => array(
								'format' => 'display',
								'disable' => '',
							),
							'taxonomy' => '',
							'allow_terms' => '',
							'allow_level' => '',
							'field_type' => 'select',
							'default_value' => array(
							),
							'return_format' => 'id',
							'ui' => 1,
							'allow_null' => 0,
							'multiple' => 1,
							'save_terms' => 0,
							'load_terms' => 0,
							'choices' => array(
							),
							'ajax' => 0,
							'placeholder' => '',
							'layout' => '',
							'toggle' => 0,
							'allow_custom' => 0,
							'other_choice' => 0,
						),
					),
				];
		}

		return $groups;
	}

	private function getRequest() : string
	{
		$request = 'post_type_' . $this->getPostType();

		if ( $this->isRequestOverwritten() ) {
			$request = apply_filters( 'cptemplates/get_current_request_name', '' );
		}

		return $request;
    }

	private function isRequestOverwritten() : bool
	{
		$filtered_request = apply_filters( 'cptemplates/get_current_request_name', '' );

		return ! is_null( $filtered_request );
	}

	private function getPost()
	{
		return get_post( $this->getPostId() );
	}

	private function getPostId() : int
	{
		$post_id = 0;

		if ( isset( $_POST['post_ID'] ) ) {
			$post_id = $_POST['post_ID'];
		}
		else if ( isset( $_POST['post_id'] ) ) {
			$post_id = $_POST['post_id'];
		}
		else if ( isset( $_GET['post'] ) ) {
			$post_id = $_GET['post'];
		}

		return (int) $post_id;
	}

	private function getPostType() : string
	{
		$post_type = '';

		if ( isset( $_GET['post_type'] ) ) {
			$post_type = $_GET['post_type'];
		}
		else if ( isset( $_POST['post_type'] ) ) {
			$post_type = $_POST['post_type'];
		}
		else if ( isset( $_POST['post_ID'] ) ) {
			$post_type = get_post_type( $_POST['post_ID'] );
		}
		else if ( isset( $_POST['post_id'] ) ) {
			$post_type = get_post_type( $_POST['post_id'] );
		}
		else if ( isset( $_GET['post'] ) ) {
			$post_type = get_post_type( $_GET['post'] );
		}

		return $post_type;
	}

	private function hasPageTemplate() : bool
	{
		return $this->getPageTemplateId() !== 0;
	}

	private function hasGridsInTemplate() : bool
	{
		return count( $this->getGridShortcodes() ) > 0;
	}

	private function getGridShortcodes() : array
	{
		preg_match_all( '/\[wpgb_grid_jvh.*]/Us', $this->getPageTemplateContent(), $shortcodes );

		return array_filter( $shortcodes[0] );
	}

	private function getPageTemplateContent() : string
	{
		if ( is_admin() ) {
			$content = get_post_field( 'post_content', $this->getPageTemplateId() ); 
		}
		else {
			$content = $this->template_content;
		}

		return self::addSnippetsContent( $content );
	}

	public static function addSnippetsContent( string $string ) : string
	{
		return preg_replace_callback(
			'/\[vc-vc-snippet id="(\d*)".*]/U',
			function ( $matches ) {
				$snippet_id = $matches[1];
				$css = get_post_meta( $snippet_id, '_wpb_shortcodes_custom_css', true );

				$content = get_post_field( 'post_content', $snippet_id );
				$content .= "<style>$css</style>";

				return "[snippet-wrapper id=\"$matches[1]\"]{$content}[/snippet-wrapper]";
			},
			$string
		);
	}

	private function getPageTemplateId() : int
	{
		$request = $this->getRequest();

		$template = cptemplates_template()->get_global_template( $request );

		return (int) $template;
	}

	public static function isAcfActive() : bool
	{
		return function_exists( 'acf_add_local_field_group' );
	}

	private function isCptActive() : bool
	{
		return function_exists( 'cptemplates_template' );
	}
}

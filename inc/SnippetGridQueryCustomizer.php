<?php

namespace JVH\Gridbuilder;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class SnippetGridQueryCustomizer
{
	private $post_id;
	private $post_content;

	public function __construct()
	{
		$this->post_id = $this->getPostId();
		$this->post_content = get_post_field( 'post_content', $this->post_id ); 
	}

	/*
	 * To do: filter content with overwritten grids in snippets
	 */
	public function activate() : void
	{
		add_action( 'admin_init', [$this, 'maybeAddAcfFields'] );
		add_filter( 'the_content', [$this, 'overwriteSnippetGrids'] );
	}

	public function maybeAddAcfFields() : void
	{
		if ( $this->shouldAddAcfFields() ) {
			$this->addAcfFields();
		}
	}

	public function overwriteSnippetGrids( $post_content ) : string
	{
		$this->__construct();

		if ( ! $this->hasGridsInSnippets() ) {
			return $post_content;
		}

		$grid_shortcodes = $this->getGridsFromSnippets();
		$this->post_content = TemplateGridQueryCustomizer::addSnippetsContent( $post_content );

		foreach ( $grid_shortcodes as $key => $shortcode ) {
			if ( ! $this->shouldReplaceShortcode( $key ) ) {
				continue;
			}

			$this->post_content = str_replace( $shortcode, $this->getReplacedShortcode( $key, $shortcode ), $this->post_content );
		}

		// If nothing is replaced, then just return original, otherwise issue with WP Brain might occur
		if ( $this->post_content === TemplateGridQueryCustomizer::addSnippetsContent( $post_content ) ) {
			return $post_content;
		}

		$this->post_content = TemplateGridQueryCustomizer::renderSnippetWrapper( $this->post_content );

		return $this->post_content;
	}

	private function shouldReplaceShortcode( $key ) : bool
	{
		return 1 == get_post_meta( $this->post_id, "gridbuilder_overwrite_snippet_shortcode_{$key}_should_overwrite", true );
	}

	private function shouldAddAcfFields() : bool
	{
		if ( ! is_admin() ) {
			return false;
		}
		else if ( ! \JVH\Gridbuilder\TemplateGridQueryCustomizer::isAcfActive() ) {
			return false;
		}
		else if ( ! $this->hasSnippets() ) {
			return false;
		}
		else if ( ! $this->hasGridsInSnippets() ) {
			return false;
		}
		else {
			return true;
		}
	}

	private function addAcfFields() : void
	{
		acf_add_local_field_group(array(
			'key' => 'group_609ea3f6d993bsnippets',
			'title' => 'Gridbuilder overwrite Snippets shortcode queries',
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

		foreach ( $this->getGridsFromSnippets() as $key => $shortcode ) {
			$groups[] = [
					'key' => "field_609eadfa8bc6d_snippet_$key",
					'label' => "Gridbuilder overwrite snippet shortcode",
					'name' => "gridbuilder_overwrite_snippet_shortcode_$key",
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
							'key' => "field_60a277aa3ce63_snippet_$key",
							'label' => 'Overwrite this snippet Gridbuilder shortcode',
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
							'key' => "field_609eacb10ae91_snippet_$key",
							'label' => 'Post types',
							'name' => "post_types",
							'type' => 'acfe_post_types',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => array(
								array(
									array(
										'field' => "field_60a277aa3ce63_snippet_$key",
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
							'key' => "field_60a29e42db363_snippet_$key",
							'label' => 'Included posts',
							'name' => 'post__in',
							'type' => 'select',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => array(
								array(
									array(
										'field' => "field_60a277aa3ce63_snippet_$key",
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
							'key' => "field_609eb2c5a8e38_snippet_$key",
							'label' => 'Exluded posts',
							'name' => 'post__not_in',
							'type' => 'select',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => array(
								array(
									array(
										'field' => "field_60a277aa3ce63_snippet_$key",
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
							'key' => "field_609ebbe3c1ed4_snippet_$key",
							'label' => 'Categories',
							'name' => 'categories',
							'type' => 'acfe_taxonomy_terms',
							'instructions' => '',
							'required' => 0,
							'conditional_logic' => array(
								array(
									array(
										'field' => "field_60a277aa3ce63_snippet_$key",
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

	private function hasSnippets() : bool
	{
		return count( $this->getSnippetShortcodes() ) > 0;
	}

	private function getSnippetShortcodes() : array
	{
		preg_match_all( '/\[vc-vc-snippet.*]/Us', $this->post_content, $shortcodes );

		return array_filter( $shortcodes[0] );
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
		else {
			$post_id = get_the_ID();
		}

		return (int) $post_id;
	}

	private function hasGridsInSnippets() : bool
	{
		return count( $this->getGridsFromSnippets() ) > 0;
	}

	private function getGridsFromSnippets() : array
	{
		$grids = [];

		foreach ( $this->getSnippetContents() as $content ) {
			$snippet_grids = $this->getGrids( $content );
			$grids = array_merge( $grids, $snippet_grids );
		}

		return $grids;
	}

	private function getSnippetContents() : array
	{
		$contents = [];

		foreach ( $this->getSnippetShortcodes() as $shortcode ) {
			$snippet_id = $this->getSnippetIdFromShortcode( $shortcode );
			$contents[] = get_post_field( 'post_content', $snippet_id );
		}

		return $contents;
	}

	private function getGrids( $content ) : array
	{
		preg_match_all( '/\[wpgb_grid_jvh.*]/U', $content, $matches );

		return $matches[0];
	}

	private function getSnippetIdFromShortcode( $shortcode ) : int
	{
		preg_match( '/id="(.*)"/U', $shortcode, $matches );
		
		// If no id found, return 0, otherwise fatal error with [vc-vc-snippet]
		if ( count( $matches ) < 2 ) {
			return 0;
		}

		return $matches[1];
	}

	private function getReplacedShortcode( int $key, string $shortcode ) : string
	{
		$grid_id = TemplateGridQueryCustomizer::getGridIdFromShortcode( $shortcode );
		$atts = $this->getNewShortcodeAtts( $key );

		return "[wpgb_grid_jvh id=\"$grid_id\" grid_type=\"custom_query\" post_types=\"$atts->post_types\" post__in=\"$atts->post__in\" post__not_in=\"$atts->post_not_in\" terms=\"$atts->terms\"]";
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
		$post_types = get_post_meta( $this->post_id, "gridbuilder_overwrite_snippet_shortcode_{$key}_post_types", true );

		return implode( ',', $post_types );
	}

	private function getPostIn( int $key )
	{
		$post__in = get_post_meta( $this->post_id, "gridbuilder_overwrite_snippet_shortcode_{$key}_post__in", true );

		return implode( ',', $post__in );
	}

	private function getPostNotIn( int $key )
	{
		$post__not_in = get_post_meta( $this->post_id, "gridbuilder_overwrite_snippet_shortcode_{$key}_post__not_in", true );

		return implode( ',', $post__not_in );
	}

	private function getTerms( int $key )
	{
		$terms = get_post_meta( $this->post_id, "gridbuilder_overwrite_snippet_shortcode_{$key}_categories", true );

		return implode( ',', $terms );
	}
}

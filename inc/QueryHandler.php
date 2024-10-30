<?php

namespace JVH\Gridbuilder;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class QueryHandler
{
	public $query_args;
	private $grid_id;
	private $page_identifier;
	private $shortcode_atts;

	public function __construct( $query_args = [], $grid_id = 0 )
	{
		$this->query_args = $query_args;
		$this->grid_id = $grid_id;
		$this->page_identifier = self::getPageIdentifier();

		// Shortcode atts are saved as transient and contain the data regarding query changes
		$this->shortcode_atts = get_transient( "jvh_grid_atts_{$this->grid_id}_{$this->page_identifier}" );
	}

	public function filter() : void
	{
		if ( $this->grid_id != $this->shortcode_atts['id'] ) {
			return;
		}

		switch ( $this->getGridType() ) {
			case 'custom_query':
				$this->makeGridCustom();
				break;
			case 'related':
				$this->makeGridRelated();
				break;
			case 'cross_sell':
				$this->makeGridCrossSell();
				break;
			case 'upsell':
				$this->makeGridUpsell();
				break;
			case 'image_gallery':
				$this->makeGridImageGallery();
			case 'video_gallery':
				break;
		}
	}

	private function makeGridCustom() : void
	{
		$this->setPostTypes();
		$this->setPostIn();
		$this->setPostNotIn();
		$this->setTaxQuery();
	}

	private function setPostTypes() : void
	{
		$this->query_args['post_type'] = $this->getPostTypes();
	}

	private function setPostIn() : void
	{
		if ( ! $this->hasPostIn() ) {
			return;
		}

		$this->query_args['post__in'] = $this->getPostIn();
	}

	private function setPostNotIn() : void
	{
		if ( ! $this->hasPostNotIn() ) {
			return;
		}

		$this->query_args['post__not_in'] = $this->getPostNotIn();
	}

	private function setTaxQuery() : void
	{
		// Empty tax_query so we get expected results when you chose your post_types and included posts
		$this->query_args['tax_query'] = [];

		if ( ! $this->hasTerms() && ! $this->hasTermsNotIn() ) {
			return;
		}

		$this->query_args['tax_query'] = $this->getTaxQuery();
	}

	private function getPostTypes() : array
	{
		return explode( ',', $this->shortcode_atts['post_types'] );
	}

	private function getPostIn() : array
	{
		return explode( ',', $this->shortcode_atts['post__in'] );
	}

	private function getPostNotIn() : array
	{
		return explode( ',', $this->shortcode_atts['post__not_in'] );
	}

	private function getTaxQuery() : array
	{
		return [
			$this->getIncludedTaxQuery(),
			$this->getExcludedTaxQuery(),
		];
	}

	private function getIncludedTaxQuery() : array
	{
		$tax_query = [];

		$tax_query['relation'] = 'OR';

		foreach ( $this->getTerms() as $term ) {
			$tax_query[] = [
				'taxonomy' => $term->taxonomy,
				'field' => 'term_id',
				'terms' => $term->term_id,
			];
		}

		return $tax_query;
	}

	private function getExcludedTaxQuery() : array
	{
		$tax_query = [];

		$tax_query['relation'] = 'AND';

		foreach ( $this->getExcludedTerms() as $term ) {
			$tax_query[] = [
				'taxonomy' => $term->taxonomy,
				'field' => 'term_id',
				'terms' => $term->term_id,
				'operator' => 'NOT IN',
			];
		}

		return $tax_query;
	}

	private function getTerms() : array
	{
		$terms = [];

		foreach ( $this->getTermIds() as $term_id ) {
			$terms[] = get_term( $term_id );
		}

		return $terms;
	}

	private function getTermIds() : array
	{
		$term_ids = explode( ',', $this->shortcode_atts['terms'] );

		return array_filter( $term_ids );
	}

	private function getExcludedTerms() : array
	{
		$terms = [];

		foreach ( $this->getExcludedTermIds() as $term_id ) {
			$terms[] = get_term( $term_id );
		}

		return $terms;
	}

	private function getExcludedTermIds() : array
	{
		$term_ids = explode( ',', $this->shortcode_atts['terms__not_in'] );

		return array_filter( $term_ids );
	}

	private function hasPostIn() : bool
	{
		return ! empty ( $this->shortcode_atts['post__in'] );
	}

	private function hasPostNotIn() : bool
	{
		return ! empty ( $this->shortcode_atts['post__not_in'] );
	}

	private function hasTerms() : bool
	{
		return ! empty ( $this->shortcode_atts['terms'] );
	}

	private function hasTermsNotIn() : bool
	{
		return ! empty ( $this->shortcode_atts['terms__not_in'] );
	}

	private function hasShortcodeAtts() : bool
	{
		if ( ! is_array( $this->shortcode_atts ) ) {
			return false;
		}
		else {
			return count( $this->shortcode_atts ) > 0;
		}
	}

	private function makeGridRelated() : void
	{
		global $post;

		if ( $this->isRelatedWithUpsell() && $this->currentProductHasUpsells() ) {
			$this->addUpsells();
			return;
		}

		// Don't display current post in grid
		$this->query_args['post__not_in'] = [$post->ID];

		// Filter posts by posts with the same category
		$this->query_args['tax_query'] = $this->getRelatedTaxQuery();
	}

	private function addUpsells() : void
	{
		global $post;

		$product = \wc_get_product( $post->ID );
		
		$this->query_args['post__in'] = $product->get_upsell_ids();

		unset( $this->query_args['tax_query'] );
	}

	private function currentProductHasUpsells() : bool
	{
		global $post;

		if ( get_post_type( $post->ID ) !== 'product' ) {
			return false;
		}
		
		$product = \wc_get_product( $post->ID );
		
		return count( $product->get_upsell_ids() ) > 0;
	}

	/*
	 * @param int $post_id 
	 *
	 * @return bool
	 */
	function has_upsells( $post_id ) {
		if ( get_post_type( $post_id) !== 'product' ) {
			return false;
		}
		
		$product = wc_get_product( $post_id );
		
		return count( $product->get_upsell_ids() ) > 0;
	}

	private function makeGridCrossSell() : void
	{	
		if ( ! class_exists( 'woocommerce' ) ) {
			return;
		}

		global $post;
		
		unset( $this->query_args['tax_query'] );
		
		if ( is_product() ) {
			$product = wc_get_product( $post->ID );
			$this->query_args['post__in'] = $product->get_cross_sell_ids();		
		}
		elseif ( is_cart() ) {
			$not_in = [];
			$cross_sell_ids = [];
			
			foreach( WC()->cart->get_cart() as $cart_item ) {
				$not_in[] = $cart_item['product_id'];
				
				$product = wc_get_product( $cart_item['product_id'] );

				foreach ( $product->get_cross_sell_ids() as $product_id ) {
					$cross_sell_ids[] = $product_id;
				}
			}
			
			$this->query_args['post__in'] = $cross_sell_ids;
			$this->query_args['post__not_in'] = $not_in;
		}
	}

	private function makeGridImageGallery()
	{
		$this->query_args['post_type'] = [ 'attachment' ];
		$this->query_args['post__in'] = $this->getImageIds();
	}

	private function makeGridUpsell() : void
	{
		if ( ! class_exists( 'woocommerce' ) ) {
			return;
		}	

		if ( is_product() ) {
			global $post;

			unset( $this->query_args['tax_query'] );
			
			$product = wc_get_product( $post->ID );
			$this->query_args['post__in'] = $product->get_upsell_ids();		
		}
	}

	private function getRelatedTaxQuery() : array
	{
		global $post;
		
		$taxonomy = $this->getTaxonmyNameCurrentPost();
		$terms = get_the_terms( $post->ID, $taxonomy );
		
		if ( empty( $terms ) ) {
			$terms = [];
		}
		
		$term_list = wp_list_pluck( $terms, 'slug' );

		return [
			[
				'taxonomy' => $taxonomy,
				'field'    => 'slug',
				'terms'    => $term_list,
			],
		];
	}

	private function getTaxonmyNameCurrentPost() : string
	{
		switch ( get_post_type() ) {
			case 'post':
			case 'page':
				return 'category';
			case 'product':
				return 'product_cat';
			case 'dt_portfolio':
				return 'dt_portfolio_category';
			case 'dt_testimonials':
				return 'dt_testimonials_category';
			case 'dt_team':
				return 'dt_team_category';
			case 'dt_logos':
				return 'dt_logos_category';
			case 'dt_benefits':
				return 'dt_benefits_category';
			case 'dt_gallery':
				return 'dt_gallery_category';
			default:
				return '';
		}
	}

	public function getVideoPosts() : array
	{
		$posts = [];

		foreach ( $this->getVideoUrls() as $video_url ) {
			if ( empty( $video_url ) ) {
				continue;
			}

			$video = new \JVH\Video( $video_url );
			$posts[] = $video->getPost();
		}

		return $posts;
	}

	public function getImagePosts() : array
	{
		$posts = [];

		foreach ( $this->getImageIds() as $image_id ) {
			$image = new \JVH\Image( $image_id );
			$posts[] = $image->getPost();
		}

		return $posts;
	}

	private function getImageIds() : array
	{
		return explode( ',', $this->shortcode_atts['images'] );
	}

	private function getVideoUrls() : array
	{
		$videos = base64_decode( $this->shortcode_atts['video_urls'] );
		$videos = urldecode( $videos );

		return explode( PHP_EOL, $videos );
	}

	public function isVideoGrid() : bool
	{
		return $this->getGridType() === 'video_gallery';
	}

	public function isImageGrid() : bool
	{
		return $this->getGridType() === 'image_gallery';
	}

	private function getGridType() : string
	{
		if ( ! $this->hasShortcodeAtts() ) {
			return 'regular';
		}
		else if ( $this->hasGridType() ) {
			return $this->shortcode_atts['grid_type'];
		}

		// Backward compatibility: grid types were separate checkbox parameters before
		else if ( isset( $this->shortcode_atts['is_related'] ) && $this->shortcode_atts['is_related'] === 'true' ) {
			return 'related';
		}
		else if ( isset( $this->shortcode_atts['is_cross_sell'] ) && $this->shortcode_atts['is_cross_sell'] === 'true' ) {
			return 'cross_sell';
		}
		else if ( isset( $this->shortcode_atts['is_upsell_sell'] ) && $this->shortcode_atts['is_upsell_sell'] === 'true' ) {
			return 'upsell';
		}

		// Default to regular 
		else {
			return 'regular';
		}
	}

	private function hasGridType() : bool
	{
		return isset( $this->shortcode_atts['grid_type'] ) && ! empty( $this->shortcode_atts['grid_type'] );
	}

	private function isRelatedWithUpsell() : bool
	{
		return $this->getGridType() === 'related' && isset( $this->shortcode_atts['add_upsell_related'] ) && $this->shortcode_atts['add_upsell_related'] === 'true';
	}

	public static function getPageIdentifier()
	{
		if ( wp_doing_ajax() ) {
			return self::getAjaxPageIdentifier();
		}
		else {
			return self::getRegularPageIdentifier();
		}
	}

	public static function getAjaxPageIdentifier()
	{
		$page_identifier = self::getPostIdAjax();

		if ( $page_identifier == 0 ) {
			$page_identifier = self::getSanitizedAjaxUrlPath();
		}

		return $page_identifier;
	}

	public static function getRegularPageIdentifier()
	{
		if ( is_singular() ) {
			return get_the_ID();
		}
		else {
			$url_path = strtok( $_SERVER['REQUEST_URI'], '?' );

			return sanitize_title( $url_path );
		}
	}

	public static function getPostIdAjax()
	{
		return url_to_postid( self::getPermalinkAjax() );
	}

	public static function getPermalinkAjax()
	{
		$wpgb_data = self::getWpgbAjaxData();

		return $wpgb_data->permalink;
	}

	public static function getSanitizedAjaxUrlPath()
	{
		$url_path = parse_url( self::getPermalinkAjax(), PHP_URL_PATH );

		return sanitize_title( $url_path );
	}

	public static function getWpgbAjaxData()
	{
		$wpgb_data_json = stripslashes( $_REQUEST['wpgb'] );

		return json_decode( $wpgb_data_json );
	}

	public static function handle()
	{
		add_filter( 'wp_grid_builder/grid/query_args', function( $query_args, $grid_id ) {
			$query_handler = new \JVH\Gridbuilder\QueryHandler( $query_args, $grid_id );
			$query_handler->filter();

			return $query_handler->query_args;
		}, 10, 2 );

		add_filter( 'wp_grid_builder/grid/the_objects', function( $posts ) {
			$page_identifier = \JVH\Gridbuilder\QueryHandler::getPageIdentifier();
			$grid_id = get_transient( "jvh_grid_id_{$page_identifier}" );

			$query_handler = new \JVH\Gridbuilder\QueryHandler( [], $grid_id );

			if ( $query_handler->isVideoGrid() ) {
				$posts = $query_handler->getVideoPosts();
			}

			return $posts;
		} );
	}

	public static function getPageIdentifierSimple()
	{
		if ( is_singular() ) {
			return get_the_ID();
		}
		else {
			$url_path = strtok( $_SERVER['REQUEST_URI'], '?' );

			return sanitize_title( $url_path );
		}
	}
}

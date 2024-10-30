<?php

namespace JVH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Image
{
	private $image_id;

	public function __construct( $image_id )
	{
		$this->image_id = $image_id;
	}	

	public function getPost()
	{
		$post = new \stdClass();
		$post->type = 'post';
		$post->post_format = 'gallery';
		$post->post_media = [
			'sources' => [
				[
					'title' => $this->getTitle(),
					'description' => '',
					'mime_type' => 'image/jpeg',
					'alt' => '',
					'sizes' => $this->getSizes(),
				],
			],
			'format' => 'gallery',
		];

		return new \WP_Post( $post );
	}

	private function getTitle() : string
	{
		return get_the_title( $this->image_id );
	}

	private function getSizes() : array
	{
		return [
			'thumbnail' => $this->getSize( 'medium_large' ),
			'lightbox' => $this->getSize( 'thumbnail' ),
			'full' => $this->getSize( 'full' ),
		];
	}

	private function getSize( $size ) : array
	{
		$data = wp_get_attachment_image_src( $this->image_id, $size );

		return [
			'url' => $data[0],
			'width' => $data[1],
			'height' => $data[2],
		];
	}
}

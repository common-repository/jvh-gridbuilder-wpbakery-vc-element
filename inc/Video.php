<?php

namespace JVH;

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

class Video
{
	private $video_url;	
	private $id;	

	public function __construct( $video_url )
	{
		$this->video_url = $video_url;
		$this->type = $this->getVideoType();
		$this->video_id = $this->getVideoId();
	}

	public function getPost()
	{
		$post = new \stdClass();
		$post->object_type = 'post';
		$post->post_format = 'video';
		$post->post_media = [
			'type' => 'embedded',
			'sources' => [
				'provider' => $this->type,
				'url' => $this->getEmbedUrl(),
				'id' => $this->video_id,
			],
			'format' => 'video',
		];
		$post->metadata = [];
		$post->metadata['_wpgb_oembed'] = $this->getEmbedData();

		return new \WP_Post( $post );
	}

	private function getVideoType()
	{
		$type = '';

		if ( strpos( $this->video_url, 'youtu' ) !== false ) {
			$type = 'youtube';
		}
		else if ( strpos( $this->video_url, 'vimeo' ) !== false ) {
			$type = 'vimeo';
		}

		return $type;
	}

	private function getEmbedData()
	{
		$embed = new \stdClass();
		$embed->video_url = $this->getEmbedUrl();
		$embed->thumbnail_url = $this->getThumbnailUrl();

		return $embed;
	}

	private function getVideoId() : string
	{
		switch ( $this->type ) {
			case 'youtube':
				return $this->getYoutubeId();
				break;
			case 'vimeo':
				return $this->getVimeoId();
				break;
		}
	}

	private function getYoutubeId()
	{
		preg_match( "/^(?:http(?:s)?:\/\/)?(?:www\.)?(?:m\.)?(?:youtu\.be\/|youtube\.com\/(?:(?:watch)?\?(?:.*&)?v(?:i)?=|(?:embed|v|vi|user)\/))([^\?&\"'>]+)/", $this->video_url, $matches );

		return $matches[1];
	}

	private function getVimeoId() : int
	{
        $id = 0;
    
        if ( preg_match('%^https?:\/\/(?:www\.|player\.)?vimeo.com\/(?:channels\/(?:\w+\/)?|groups\/([^\/]*)\/videos\/|album\/(\d+)\/video\/|video\/|)(\d+)(?:$|\/|\?)(?:[?]?.*)$%im', $this->video_url, $matches ) ) {
            $id = $matches[3];
        }
    
        return (int) $id;
	}

	private function getThumbnailUrl() : string
	{
		switch ( $this->type ) {
			case 'youtube':
				return 'https://i.ytimg.com/vi/' . $this->video_id . '/hqdefault.jpg';
				break;
			case 'vimeo':
				return $this->getVimeoThumbnail();
				break;
		}
	}

	private function getVimeoThumbnail() : string
	{
		$vimeo = unserialize( file_get_contents( "https://vimeo.com/api/v2/video/$this->video_id.php" ) );

		return $vimeo[0]['thumbnail_large'];
	}

	private function getEmbedUrl() : string
	{
		switch ( $this->type ) {
			case 'youtube':
				return 'https://www.youtube.com/embed/' . $this->video_id;
				break;
			case 'vimeo':
				return 'https://vimeo.com/' . $this->video_id;
				break;
		}
	}
}

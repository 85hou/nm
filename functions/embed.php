<?php
global $nmjson;
if(!isset($nmjson)){
    $nmjson = new nmjson();
}

function nm_format_time( $seconds ) {
    if ( !$seconds ) return '--:--';
    $min = floor($seconds / 60);
    if ( $min < 10 ) $min = '0' . $min;
    $second = floor($seconds % 60);
    if ( $second < 10 ) $second = '0' . $second;

    return $min . ':' . $second;
}


function nms_like_btn($id) {
    global $post;
    if(!is_singular() ) return;
    $postid = $post->ID;
    $count = get_post_meta( $postid , '_song' . id , true ) ? get_post_meta( $postid , '_song' . id , true ) : 0;
    return '<span class="fxfont nm-heart" data-postid="' . $postid . '" data-id="' . $id . '"><span class="count">' . $count . '</span></span>';

}

function nm_single_playform( $id , $instance , $cover , $title , $artist , $duration ) {
    return '<div id="nm-player-' . $instance . '" class="nmsingle-container"><div class="nmsingle-cover" style="background-image:url(' . $cover . ')"><span class="nms-play-btn fxfont nm-play" data-index=' . $instance . '></span></div><div class="nmsingle-info"><div class="nmplayer-top"><span class="nmplayer-title">' . $title . ' - ' . $artist . '</span><span class="nmsingle-playtime"><span class="current-time">--:--</span> / <span class="duration">' . nm_format_time($data['duration']) . '</span></span></div><div class="nmplayer-mid"><div class="nmplayer-control" data-index=' . $instance . '><span class="fxfont nm-previous"></span><span class="fxfont nm-next"></span><span class="nm-mute fxfont"></span></div><div class="nmsingle-lrc">(*+﹏+*)</div></div><div class="nmsingle-process" data-index="' . $instance . '"><div class="nmsingle-process-bar"></div></div></div></div>';
}

wp_embed_register_handler( 'neteasemusicalbum', '#http:\/\/music\.163\.com\/\#\/(\w+)\?id=(\d+)#i', 'wp_embed_handler_neteasemusicalbum' );


function wp_embed_handler_neteasemusicalbum( $matches, $attr, $url, $rawattr ) {
    if(! is_singular() ) return $url;
    $type = $matches[1];
    wp_enqueue_style('nms');
    wp_enqueue_script('nm');
    wp_enqueue_script('nms');
    $id = $matches[2];
    global $nmjson;
    static $instance = 0;
    
    if( $instance === 0) $html ='<script>var playlist = []</script><div id="nm_jplayer" style="display:none"></div>';
    switch ($type) {
        case 'album':
            $data = $nmjson->netease_album($id);
            $songs = $data['songs'];
            $html .= nm_single_playform( $data['album_id'] , $instance , $data['album_cover'] , $data['album_title'] , $data['album_author'] , '' ); 
            $html .= '<div class="nms-list" id="nm-list-' . $instance . '" data-index="' . $instance . '">';
            foreach ($songs as $key => $song) {
                $html .= '<div class="nms-list-item">' . $song['title'] . ' - ' . $song['artist'] . '<span class="song-time">' . nm_format_time( $song['duration'] ) . '</span></div>';
            }
            $html .= '</div>';
            $html .= '<script>playlist.push(' . json_encode($songs). ');</script>';
            break;

        case 'song':
            $data = $nmjson->netease_song($id);
            $html .= nm_single_playform( $data['id'] , $instance , $data['cover'] , $data['title'] , $data['artist'] , $data['duration'] );       
            $html .= '<script>playlist.push(' . json_encode(array($data)). ');</script>';
            break;

        case 'playlist':
            $data = $nmjson->netease_playlist($id);
            $songs = $data['songs'];
            //var_dump($songs);
            $html .= nm_single_playform( $data['collect_id'] , $instance , $data['collect_cover'] , $data['collect_title'] , $data['collect_author'] , '' ); 
            $html .= '<div class="nms-list" id="nm-list-' . $instance . '" data-index="' . $instance . '">';
            foreach ($songs as $key => $song) {
                $html .= '<div class="nms-list-item"><span class="song-info">' . $song['title'] . ' - ' . $song['artist'] . '</span><span class="song-time">' . nm_format_time( $song['duration'] ) . '</span></div>';
            }
            $html .= '</div>';
            $html .= '<script>playlist.push(' . json_encode($songs). ');</script>';
            break;
        case 'program':
            $data = $nmjson->netease_radio($id);
            $html .= nm_single_playform( $data['id'] , $instance , $data['cover'] , $data['title'] , $data['artist'] , $data['duration'] );      
            $html .= '<script>playlist.push(' . json_encode(array($data)) . ');</script>';
            break;    
        default:
            return $url;
            break;
    }
    $instance++;
    return apply_filters( 'embed_forbes', $html, $matches, $attr, $url, $rawattr );
}
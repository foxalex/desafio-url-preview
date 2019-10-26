<?php 

// Example of Custom Source
// Youtube Source
class YoutubeSource {

  // Video Tags to Get in Meta Tags
  static $videoTags = array('width', 'height', 'tag');
  
  // Domains
  static $domains = array('youtube.com', 'youtu.be');
  
  // Curl Settings
  static $curlSettings = array(
    'agent' => 'Mozilla/5.0 (compatible; Googlebot/2.1; +http://www.google.com/bot.html)'
  );

  // Main Method
  static function get($html, $optsFields, $metaTags) {

    //die($html);
    
    $response = array();

    // Get Video Info    
    foreach($metaTags as $metaTagName => $metaTagValue) {
      if(strpos($metaTagName, 'og:video:') !== false) {
        $videoTagName = str_replace('og:video:','', $metaTagName);
        if(in_array($videoTagName, self::$videoTags)) {
          $response['video'][$videoTagName] = $metaTagValue;
        }
      }
    }

    // Get Video Time
    $videoTime = self::get_video_time($html);
    if($videoTime) {
      $response['video']['time'] = $videoTime;
    }

    return array_merge($optsFields, $response);

  }

  // Youtube Methods  
  // Get Video Time
  static function get_video_time($html) {
    if(preg_match('/videoLengthSeconds.*?\:.*?\"(\d*).*?\"/mi',$html, $thumbMatches)) {
      return $thumbMatches[1];
    }    
    return false;        
  }  

}
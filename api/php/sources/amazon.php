<?php 

// Example of Custom Source
// Amazon Source
class AmazonSource {
  
  // Domains
  static $domains = array('amazon.com');

  // Curl Settings
  static $curlSettings = false;

  // Main Method
  static function get($html, $optsFields, $metaTags) {
    
    // Source Custom Response
    $response = array(      
      'sitename' => 'amazon.com',
    );

    // Thumb
    $thumb = self::get_thumb($html);

    if($thumb) {
      $response['thumbnail'] = $thumb;
    }

    // Price
    $price = self::get_price($html);

    if($price) {
      $response['price'] = $price['value'];
      if(isset($price['currency'])) {
        $response['currency'] = $price['currency'];
      }
    }

    return array_merge($optsFields, $response);
  }

  // Amazon Methods

  // Get Thumb
  static function get_thumb($html) {
    if(preg_match('/landingImage(?:.*\"\{&quot\;)(.*?)\&quot\;/mi',$html, $thumbMatches)) {
      return $thumbMatches[1];
    }
    if(preg_match('/immersiveViewMainImage.*?source=\"(.*?)\"/mi',$html, $thumbMatches1)) {
      return $thumbMatches1[1];
    }
    return false;        
  }

  // Get Price
  static function get_price($html) {

    if(preg_match('/data-asin-price\=\"(.*?)\"(?:.*)data-asin-currency-code\="(.*?)\"/mi', $html, $priceMatches)) {
      return array(
        'value' => floatval($priceMatches[1]),
        'currency' => $priceMatches[2]
      );
    }

    if(preg_match('/itemDetails(?:.*?)price(?:.*?):(.*?)\,/mi', $html, $priceMatches1)) {
      return array(
        'value' => floatval($priceMatches1[1])
      );      
    }
    
  }
  
}
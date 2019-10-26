<?php

class UrlPreview {

  // Url Preview Default Settings
  private $settings = array(
    // Sources
    'useSources' => true,
    'sources' => './sources',
    'sourceClassPrefix' => 'Source',
    // Thumbs
    'useDefaultThumb' => true,
    'defaultThumb' => '../assets/defaultThumb.svg',
    // Network
    'scheme' => 'http',
    'curl' => array(
      'follow' => true,  
      'agent' => 'Mozilla/5.0 (X11; Ubuntu; Linux i686; rv:28.0) Gecko/20100101 Firefox/28.0',
      'encoding' => 'gzip',
      'headers' => array(
        'Accept: text/html,application/xhtml+xml,application/xml;q=0.9,*/*;q=0.8',
      )
    ),
    // Meta Tags Fields Default References
    'metaFields' => array(
      'title' => array('og:title', 'twitter:title', 'title'),
      'description' => array('og:description', 'twitter:description', 'description'),
      'sitename' => array('og:site_name', 'site_name', 'sitename'),
      'thumbnail' => array('og:image', 'twitter:image', 'twitter:image:src', 'image'),
      'url' => array('og:url', 'url')        
    )
  );

  // Reference for Optionals Fields Function
  private $optsFunc = null;

  // Constructor
  public function __construct($newSettings = false, $optsFunc = false) {

    $this->optsFunc = is_callable($optsFunc) ? $optsFunc : array($this, 'default_get_optionals_fields');
    
    if($newSettings) {
      $this->settings = array_replace_recursive($this->settings, $newSettings);
    }
  }

  // Network Get Url with Curl
  private function get_url($url, $newSettings = false) {

    $curlSettings = $this->settings['curl'];
    
    if($newSettings) {      
      $curlSettings = array_merge($curlSettings, $newSettings);
    }
    
    $curl = curl_init();
    
    curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($curl, CURLOPT_FOLLOWLOCATION, $curlSettings['follow']);

    // Testing

    if($curlSettings['headers']) {
      curl_setopt($curl, CURLOPT_HTTPHEADER, $curlSettings['headers']);    
    }
    
    // Fix Giz Content-Type
    // Just to note that this option sets the Accept-Encoding:
    // gzip header on the request and uncompresses the response if it is compressed
    if($curlSettings['encoding']) {
      curl_setopt($curl, CURLOPT_ENCODING , $curlSettings['encoding']);
    }
    
    // End Testing
    
    // Think about this --> $_SERVER['HTTP_USER_AGENT']
    if($curlSettings['agent']) {      
      curl_setopt($curl, CURLOPT_USERAGENT, $curlSettings['agent']);
    }
    
    curl_setopt($curl, CURLOPT_URL, $url);

    $response = curl_exec($curl);

    if($response === false) {
      $this->handle_error('Curl error: '.curl_error($curl));
    }
    
    curl_close($curl);

    return $response;
    
  }
  
  // Handling Errors
  private function handle_error($msg, $code = 500) {
    
    http_response_code($code);
    
    die(json_encode(array(
      'error' => true,
      'error_message' => $msg
    )));
    
  }

  // https://www.php.net/manual/pt_BR/function.get-meta-tags.php#117766
  private function get_meta_tags($html) {
    $pattern = '
    ~<\s*meta\s

    # using lookahead to capture type to $1
      (?=[^>]*?
      \b(?:name|property|http-equiv)\s*=\s*
      (?|"\s*([^"]*?)\s*"|\'\s*([^\']*?)\s*\'|
      ([^"\'>]*?)(?=\s*/?\s*>|\s\w+\s*=))
    )

    # capture content to $2
    [^>]*?\bcontent\s*=\s*
      (?|"\s*([^"]*?)\s*"|\'\s*([^\']*?)\s*\'|
      ([^"\'>]*?)(?=\s*/?\s*>|\s\w+\s*=))
    [^>]*>

    ~ix';
   
    if(preg_match_all($pattern, $html, $out)) {
      $resultArray = array_combine($out[1], $out[2]);
      return array_map(function($str) {
        return $this->normalize_str($str);
      }, $resultArray); 
    }
    return array();    
  }

  // Default Function to get Optional Fields
  private function default_get_optionals_fields($metaTags) {

    $optsFields = array();
    
    // Optional Fields
    // Processing Optionals Fields
    foreach($this->settings['metaFields'] as $fieldName => $fieldOpts) {      
      foreach($fieldOpts as $fieldAliase) {        
        if(array_key_exists(strtolower($fieldAliase), $metaTags)) { 
          $optsFields[$fieldName] = $metaTags[$fieldAliase];
          break;
        }
      }
    }

    return $optsFields;

  }

  // Getting Preview Data From Default Url
  public function get($url) {

    try {

      // Prevent NULL Domain/Scheme forcing default Scheme
      $scheme = parse_url($url, PHP_URL_SCHEME);
      if(empty(parse_url($url, PHP_URL_SCHEME))) {
        $url = $this->settings['scheme'].'://'.$url;
      }

      if(!filter_var($url, FILTER_VALIDATE_URL)) {
        throw new Exception('Invalid Url');
      }

      $source = false;
      $curlSettings = false;      

      if($this->settings['useSources']) {
        $source = $this->get_source($url);
        if($source) {
          $curlSettings = $source::$curlSettings;
        }
      }

      $html = $this->get_url($url, $curlSettings);    
      
      // Title    
      preg_match('/<title.*?>\s*(.*)\s*<\/title>/mi', $html, $titleMatches);
      $title = isset($titleMatches[1]) ? $this->normalize_str($titleMatches[1]) : '';
  
      // Domain
      $domain = parse_url($url, PHP_URL_HOST);
      
      // Basic Response
      $response = array(
        'title' => $title,
        'domain' => $domain
      );

      // Meta Tags
      $metaTags = $this->get_meta_tags($html);    
      
      // Set Default Case Lower in all Keys
      $metaTags = array_change_key_case($metaTags, CASE_LOWER);
      
      if($this->settings['useSources'] !== true) {      
        return null;
      }          

      // Getting Optional Fields

      // Get Default Optional Fields
      $optsFields = call_user_func($this->optsFunc, $metaTags);

      // Check Sources if Setted
      /*
      if($this->settings['useSources']) {

        $sourceFunc = $this->get_optionals_fields_func($url); // Get Source function to get optional fields

        if($sourceFunc) {
          $optsFields = call_user_func($sourceFunc, $html, $optsFields, $metaTags);
        }

      } 
      */
      if($source) {
        $optsFields = $source::get($html, $optsFields, $metaTags);
      }     
  
      if($optsFields) {
        $response = array_merge($response, $optsFields);
      }

      // Check and Set a Default Thumb
      if($this->settings['useDefaultThumb'] && !isset($response['thumbnail'])) {
        $response['thumbnail'] = $this->settings['defaultThumb'];
      }

      $json = json_encode($response, JSON_PRETTY_PRINT);

      if(json_last_error()) {
        throw new Exception('Error Encoding JSON');
      }

      return $json;

    } catch(Exception $e) {      
      $this->handle_error('Get URL Exception: '.$e->getMessage());
    } catch(Throwable $e) {
      // OR Error $e - https://www.php.net/manual/en/language.errors.php7.php
      $this->handle_error('Get URL Error: '.$e->getMessage());
    }

    $this->handle_error('Get URL: '.'Server Error.');

  }

  // External Sources
  public function get_source($url) {
  
    $sourcePath = $this->settings['sources'];
    
    $sources = [];

    try {

      if(is_dir($sourcePath)) {
        if (($handle = opendir($sourcePath))) {
          while ($file = readdir($handle)) {
            if (is_file($sourcePath . '/' . $file)) {
              $filepath = $sourcePath . '/' . $file;
              $name = pathinfo($filepath, PATHINFO_FILENAME);
              $extension = pathinfo($filepath, PATHINFO_EXTENSION);            
              if($extension === 'php' && $name !== 'default') {
                require_once($filepath);
                $className = $name.$this->settings['sourceClassPrefix'];
                if(class_exists($className)) {
                  $source = new $className();                  
                  $regex = $this->generate_regex($source::$domains);                  
                  if(preg_match($regex, $url)) {                    
                    //return array($source, 'get');                    
                    return $source;
                  }
                }
              }
            }
          }
        }
      }

    } catch(Throwable $e) {    
      $this->handle_error('Get Sources Error: '.$e->getMessage());  
    } catch(Exception $e) {      
      $this->handle_error('Get Sources Exception: '.$e->getMessage());
    }

    return false;
   
  }
  // end get_sources
  
  // Func to generate a Regex with all domains of sources
  private function generate_regex($domains) {
    $str = implode('|', array_map(function($r){
      return str_replace('.', '\.', $r);
    }, $domains));
    return '/^(?:https?:\/\/)?(?:www\.)?(?:'.$str.')/i';    
  }

  // Func to normalize string encodings
  private function normalize_str($str) {
    //$str = html_entity_decode(preg_replace("/U\+([0-9A-F]{4})/", "&#x\\1;", $str), ENT_NOQUOTES, 'UTF-8');
    //$str = utf8_encode($str);
    $encoder = mb_detect_encoding($str);

    if($encoder === 'ASCII') {      
      $str = html_entity_decode($str, ENT_QUOTES, 'UTF-8');
    }
    if($encoder === 'UTF-8' || $encoder === false) {
      // Check if is not already encoded
      if(utf8_encode(utf8_decode($str)) !== $str) {
        $str = mb_convert_encoding($str, 'UTF-8');
      }      
    }
    return $str;
  }

}
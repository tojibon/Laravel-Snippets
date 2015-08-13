<?php 
namespace App\Http\Controllers;

class InfoController extends Controller
{
  
    /*
    * 
    * Checking if cache file exist
    * @ param string $name cache file name
    * 
    */
    private function _checkCacheAvailable($name){
        if($this->enableCache){
            $cachefile = $this->cacheLocation . $name;
            $cachetime = 5 * 60; //5 min
            if (file_exists($cachefile) && (time() - $cachetime < filemtime($cachefile)))  {
                return true;
            } else {
                return false;
            }    
        } else {
            return false;
        }
    }
    
    /*
    * 
    * Reading cache file
    * @ param string $name cache file name
    * 
    */
    private function _readCache($name){
        $cachefile = $this->cacheLocation . $name;
        $output = file_get_contents($cachefile, FILE_USE_INCLUDE_PATH);
        return $output;
    }
    
    
    /*
    * 
    * Writing cache file with contents
    * @ param string $name cache file name
    * @ param string $content cache content to write on cache file
    * 
    */
    private function _writeCache($name, $content){
        if($this->enableCache){
            $cachefile = $this->cacheLocation . $name;
            $fp = fopen($cachefile, 'w'); 
            fwrite($fp, $content); 
            fclose($fp);     
        }        
    }
    
    /*
    * 
    * @ param string $data Full html content which you want to parse
    * @ param string $s_tag Start tag of html content
    * @ param string $e_tag End tag of html content
    * @ return middle html content from given start tag and end tag of $data
    * */
    private function _getValueByTagName( $data, $s_tag, $e_tag) {
        $pos = strpos($data, $s_tag);
        if ($pos === false) {
            return '';
        } else {  
            $s = strpos( $data,$s_tag) + strlen( $s_tag);
            $e = strlen( $data);
            $data= substr($data, $s, $e);
            $s = 0;
            $e = strpos( $data,$e_tag);
            $data= substr($data, $s, $e);
            $data= substr($data, $s, $e);
            return  $data;
        }
    }  
    
    /*
    * 
    * Reading curl get contents with cache support
    * @ param string $url target URL to be scrapped
    * @ param string $content of target URL in HTML
    * 
    */
    private function _curl_get($url)
    {
        if($this->enableCache){
            $name = md5($url);
            if ( $this->_checkCacheAvailable($name) ) {
                return $this->_readCache($name);
            }
        }
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, 1);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, 0);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, 0);
        $data = curl_exec($ch);
        curl_close($ch);
        
        if($this->enableCache){
            $name = md5($url);
            $this->_writeCache($name, $data);
        }
        return $data;
    }
    
    /*
    getIndex method
    
    @return resource
    */
    public function getIndex() {
      $this->enableCache = true;
      $this->cacheLocation = storage_path() . '/.cache/'; //You must needs to create this directory under storage/ of your Laravel app
      $url = "http://example.com";
      $content = $this->_curl_get($url);
      $h1 = $this->_getValueByTagName($content, '<h1>', '</h1>');
      pr($h1); //Your target scraped heading from h1 tag
    }
}
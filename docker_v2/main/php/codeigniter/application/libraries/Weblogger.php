<?php  if ( ! defined('BASEPATH')) exit('No direct script access allowed');

/**
 *  This lib allows developer to log in JS console
 */
class WebLogger
{
  public function log($msg)
  {
    if (getenv('ENABLED_JSCONSOLE_DEBUG') == true)
    {
      echo("<script>console.log('".$msg."');</script>");
    }
  }
}
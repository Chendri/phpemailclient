<?php
if(!defined('BASEPATH')) exit('No direct script access allowed');

class MY_Controller extends CI_Controller{
   function __construct(){
      parent::__construct();
   }
   function _output($content)
   {
      $data['content'] = &$content;
      echo($this->load->view('base', $data, true));
   }
}

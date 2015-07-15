<?php 
$this->load->helper('email_helper');
$client = open_all();
foreach ($threads as $key => $val) {
   $tree = explode('.', $key);
   if ($tree[1] == 'num' && $val) {
      $header = imap_fetch_overview($client, $val, FT_UID)[0];
      echo "<ul>\n\t<li>" . $header->fromaddress . " ". $header->subject  . $val . "\n";
   } elseif ($tree[1] == 'branch') {
      echo "\t</li>\n</ul>\n";
   }
}
?>

<h1> THREADS </h1>
<?php echo '<pre>';var_dump($threads);echo '</pre>';?>
<h1> FOLDERS </h1>
<?php echo '<pre>';var_dump($folders);echo '</pre>';?>
<h1> PHP INFO </h1>
<?php echo phpinfo();?>

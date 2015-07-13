<?php echo '<pre>';var_dump($branch);echo '</pre>';?>
<?php 
$this->load->helper('email_helper');
$client = open_all();
foreach ($threads as $key => $val) {
   $tree = explode('.', $key);
   if ($tree[1] == 'num' && $val) {
      $header = imap_headerinfo($client, $val);
      echo "<ul>\n\t<li>" . $header->fromaddress . " ". $header->subject  . $tree[0] . "\n";
   } elseif ($tree[1] == 'branch') {
      echo "\t</li>\n</ul>\n";
   }
}
?>

<h1> THREADS </h1>
<?php echo '<pre>';var_dump($threads);echo '</pre>';?>
<h1> FOLDERS </h1>
<?php echo '<pre>';var_dump($folders);echo '</pre>';?>
<h1> STRUCTURE </h1>
<?php echo '<pre>';var_dump($structure);echo '</pre>';?>
<h1> HEADER </h1>
<?php echo '<pre>';var_dump($header);echo '</pre>';?>
<h1> BODY </h1>
<?php echo '<pre>';var_dump($body);echo '</pre>';?>
<h1> PHP INFO </h1>
<?php echo phpinfo();?>

<?php
// Start - LDAP Details
$ldap_server = 'dc01.example.com.au';
$ldap_domain = 'example.com.au';
$ldap_dn = "DC=example,DC=com,DC=au";
$ldap_username = "Username";
$ldap_password = "Password";
// End - LDAP Details

// Start - Get User Account
if (isset($_GET["account"])) {
	$account = $_GET["account"];
} else {
	exit(1);
}
// End - Get User Account

// Start - LDAP Connection
$ds = ldap_connect( $ldap_server );
ldap_set_option($ds, LDAP_OPT_PROTOCOL_VERSION, 3);
ldap_set_option($ds, LDAP_OPT_REFERRALS, 0);

$login = ldap_bind( $ds, $ldap_username . "@" . $ldap_domain, $ldap_password );
try{
	$attributes = array("thumbnailphoto");
	$filter = "(&(objectCategory=person)(sAMAccountName=" . $account . "))";
	$result = ldap_search($ds, $ldap_dn, $filter, $attributes);
	$entries = ldap_get_entries($ds, $result);
	if($entries["count"] > 0){
		// Account found
		if ($entries[0]['thumbnailphoto'][0]) {
			// Thumbnail found
			$thumbnail_file = tempnam(sys_get_temp_dir(), 'thumbnail');
			file_put_contents($thumbnail_file, $entries[0]['thumbnailphoto'][0]);
			$file_info = new finfo(FILEINFO_MIME_TYPE);
			$mime  = explode(';', $file_info->file($thumbnail_file));
			header("Content-type: " . $mime[0]);
			echo $entries[0]['thumbnailphoto'][0];
			unlink($thumbnail_file);
		} else {
			// No thumbnail set
			exit(1);
		}
	} else {
		// No Results found
		exit(1);
	}
}catch(Exception $e){
	ldap_unbind($ds);
	return;
}
ldap_unbind($ds);
// End - LDAP Connection
?>

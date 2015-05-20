<html>
<head>
<title>Sharing secrets</title>
</head>
<body>

<h1>Sharing secrets</h1>

<h3>Welcome! On this webpage you will be able to share some secret with someone else.</h3>
<p><b>To create a new secret</b>: enter your email address, a password and the secret, and then press the button. You can now send the link to this webpage, the password and your email address to your buddy. Note that using the same email address and password twice will erase the first secret.<br/ >
<b>To get a secret</b>: enter the email address and the password that your buddy gave you, and then press the button.<br/ >
<b>Beware</b>: the secret can be viewed only once!<br />
<b>About this webpage</b>: the password is not saved. The secret is encrypted using a key that depends on the password and the email address and then is saved on the hard drive. The secret is deleted right after viewing.
</p>

<?php
require 'PHPMailer/PHPMailerAutoload.php';

function send_email($subject, $content, $dest)
{
	$email = new PHPMailer();
	$email->From      = '<from email address>';
	$email->FromName  = '<from name>';
	$email->Subject   = $subject;
	$email->Body      = $content;
	$email->AddAddress($dest);
	$email->Send();
}

function encryptText($txt, $key) {
	$key = substr($key, 0, 32);
	$key_size = strlen($key);

	$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
	$iv = mcrypt_create_iv($iv_size, MCRYPT_RAND);
	$ciphertext = mcrypt_encrypt(MCRYPT_RIJNDAEL_128, $key, $txt, MCRYPT_MODE_CBC, $iv);
	$ciphertext = $iv . sha1($ciphertext) . $ciphertext;
	$ciphertext_base64 = base64_encode($ciphertext);

	return $ciphertext_base64;
}

function decryptText($txt, $key) {
	$key = substr($key, 0, 32);
	$key_size = strlen($key);

	$ciphertext_dec = base64_decode($txt);

	$iv_size = mcrypt_get_iv_size(MCRYPT_RIJNDAEL_128, MCRYPT_MODE_CBC);
	$iv_dec = substr($ciphertext_dec, 0, $iv_size);
	$hash_size = strlen(sha1("dummy"));
	$hash = substr($ciphertext_dec, $iv_size, $hash_size);
	$ciphertext_dec = substr($ciphertext_dec, $iv_size + $hash_size);

	if ($hash != sha1($ciphertext_dec)) {
		return "Integrity error! Cannot recover secret.";
	} else {
		$plaintext_dec = mcrypt_decrypt(MCRYPT_RIJNDAEL_128, $key, $ciphertext_dec, MCRYPT_MODE_CBC, $iv_dec);
		return trim($plaintext_dec);
	}
}

function displaySecretIfPossible($pass, $email) {
	$hash = sha1($pass . $email);
	$hh = substr($hash, 0, 7);

	if (!file_exists($hash)) {
		echo 'There is no secret for this email and password. Maybe you type the wrong credentials, or the secret has already been retrieved. Give this hash code to your buddy: ' . $hh;
		$header = 'Someone tried to access a secret that does not exist with your email!';
	} else {
		$myfile = fopen($hash, "r") or die("Unable to open file!");
		$enc = fread($myfile, filesize($hash));
		fclose($myfile);
		unlink($hash);

		$secret = decryptText($enc, $hash);

		echo $secret;
		$header = 'The secret has been retrieved!';
	}

	$msg = "Hash: " . $hh . "\nRemote IP address: " . $_SERVER['REMOTE_ADDR'] . "\nDate: " . date('Y-m-d H:i:s');
	send_email($header, $msg, $email);
}

function createNewSecret($pass, $email, $secret) {
	$hash = sha1($pass . $email);
	$hh = substr($hash, 0, 7);

	$enc = encryptText($secret, $hash);

	$myfile = fopen($hash, "w") or die("Unable to open file!");
	fwrite($myfile, $enc);
	fclose($myfile);

	$msg = "Confirmation hash: " . $hh . "\nRemote IP address: " . $_SERVER['REMOTE_ADDR'] . "\nDate: " . date('Y-m-d H:i:s');
	send_email("A secret has been created!", $msg, $email);
	echo "A secret has been created! An email has been sent to " . $email;
}
?>

<form method="POST" action="share_secret.php" id="passform" name="passform">
<table border="0">
<tbody>
<tr>
  <td align="left">Email: </td>
  <td><input name="email" value="" type="email" /></td>
</tr>
<tr>
  <td align="left">Password: </td>
  <td><input name="pass" value="" type="password" /></td>
</tr>
<tr>
  <td colspan="2" align="left">Secret: </td>
</tr>
<tr>
  <td colspan="2" align="left"><textarea name="secret" rows="10" cols="40" wrap="auto"><?php
  $pass = isset($_POST['pass']) ? trim($_POST['pass']) : null;
  $email = isset($_POST['email']) ? trim($_POST['email']) : null;
  $secret = isset($_POST['secret']) ? trim($_POST['secret']) : null;
  if (!empty($pass) && !empty($email)) {
    if (empty($secret)) {
  	   displaySecretIfPossible($pass, $email);
	 } else {
  		createNewSecret($pass, $email, $secret);
	 }
  }
  ?></textarea></td>
</tr>
<tr>
  <td colspan="2" align="left"><input type="submit" name="getsecret" value="Get secret" /></td>
</tr>
</tbody>
</table>
</form>

</body>
</html>

# Share Secrets

Share Secrets is a small webpage that allows one to share some secret with someone else.
It has the particularity to send emails when creating a secret and accessing a secret, so that you are informed on accesses on your secret (e.g., to be sure that the person accessing it is the right person and has accessed it). Moreover, once a secret is shared it is permanently destroyed: secrets can be accessed only once.
Note that secrets are encrypted on the disk using AES with a 128bits key. The server stores nothing in clear.

## Installation

0. Your server should be able to send emails, otherwise no email will be sent.
1. Git clone this repository and put it somewhere in your web server.
2. Modify the two following lines in `share_secret.php`, in the `send_email()` function:
```
	$email->From      = '<from email address>'; // email address with which you send the email
	$email->FromName  = '<from name>';          // name in the From field of the email
```

## Usage

### Create a secret

1. Go to the page `share_secret.php`.
2. Enter your email, choose a password, enter the secret, and click on the button.
3. The secret is encrypted and stored on the server.
4. You will receive an email containing a confirmation hash of your email and password.
5. Give the link to the page and your email address to your buddy. Also give him the password, using any mechanism you like, such as paper, sms, email, etc.
6. For another secret, use a different password: the email and password uniquely identify (modulo the collision residstance of the `sha1` hash function) the secret.

### Access a secret

1. Go to the page `share_secret.php`.
2. Enter the email and the password your buddy gave you.
3. Press the button. You now have access to the secret! Meanwhile, the secret is removed from the server and an email, with the confirmation hash related to this secret, is sent to your buddy. As for now no one else can access the secret.

### Check the access to the secret

When you create a secret, you receive an email that looks like the following one, where `Remote IP address` is the IP address from which you have created the secret:
```
Subject: A secret has been created!

Confirmation hash: 486e289
Remote IP address: 192.168.1.1
Date: 2015-05-20 22:54:42
```

When someone accesses the secret for the first time, you receive another email:
```
Subject: A secret has been retrieved!

Confirmation hash: 486e289
Remote IP address: 192.168.2.5
Date: 2015-05-20 23:02:42
```
Receiving this email means that someone has read the secret from the mentionned IP address, and now the secret is no longer accessible.

When someone tries to access the secret after the first time, he will get the following message:
```
There is no secret for this email and password. Maybe you type the wrong credentials, or the secret
has already been retrieved. Give this hash code to your buddy: 486e289
```
If the intended person has not read the secret, then he should contact his buddy and give him this code, so that they both now that the secret has been read by a third-party and, as a result, compromised.

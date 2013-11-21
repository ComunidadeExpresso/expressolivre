#!/usr/bin/perl

# Perl code to create and print MD5 hash of password
# shamelessly stolen from the openLDAP Faq-O-Matic
# written 19-Jul-01 by Ed Truitt
# Modificado em 15/03/2010 por William Fernando Merlotto <william@prognus.com.br>

$pass = $ARGV[0];
chomp($pass);

use Digest::MD5;
use MIME::Base64;
$ctx = Digest::MD5->new;
$ctx->add($pass);
$hashedMD5Passwd = '{MD5}' . encode_base64($ctx->digest,'');
$hexpass = $ctx->hexdigest;
$b64pass = $ctx->b64digest;
#print $hexpass . "\n";
#print $b64pass . "\n";
print $hashedMD5Passwd . "\n";

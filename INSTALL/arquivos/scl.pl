#!/usr/bin/perl

# Versao 5, ldapS

#use strict;
use warnings;
use Fcntl;
#use Net::LDAP qw(:all);
use Net::LDAP;
use Sys::Syslog qw(:DEFAULT setlogsock);

my $ldap_host = "127.0.0.1";
my $base_dn = "LDAP_DN";

#my $ldap_host = "ldaps://LDAP_SERVER:636";
#my $base_dn = "BASE_DN";

my $syslog_socktype = 'unix'; # inet, unix, stream, console
my $syslog_facility="mail";
my $syslog_options="pid";
my $syslog_priority="info";

setlogsock $syslog_socktype;
openlog $0, $syslog_options, $syslog_facility;

select((select(STDOUT), $| = 1)[0]);

sub fatal_exit {
    my($first) = shift(@_);
    syslog "err", "fatal: $first", @_;
    exit 1;
}

#
# Receive a bunch of attributes, evaluate the policy, send the result.
#
while (<STDIN>) {

	if (/([^=]+)=(.*)\n/) {
		$attr{substr($1, 0, 512)} = substr($2, 0, 512);
	}
	elsif ($_ eq "\n")
	{
		#for (keys %attr) {
		#	syslog $syslog_priority, "Attribute: %s=%s", $_, $attr{$_};
		#}

		fatal_exit "unrecognized request type: '%s'", $attr{request}
			unless $attr{"request"} eq "smtpd_access_policy";


		my $ldap = Net::LDAP->new($ldap_host, version => 3);
        my $mesg = $ldap->bind ;    # an anonymous bind
		#my $mesg = $ldap->bind ( 'cn=admin,dc=expresso,dc=com,dc=br', password=>'SENHA');
	
		my $filter = "(|(\&(mail=$attr{recipient})(mailSenderAddress=$attr{sender})(phpgwAccountType=l))(\&(mail=$attr{recipient})(participantCanSendMail=TRUE)(mailForwardingAddress=$attr{sender})(phpgwAccountType=l)))";
		
		my $search = $ldap->search(filter=>"$filter",
					   base=>"$base_dn",
					   attrs=> ['uid'] );

		if ( $search->code ) 
		{
			syslog $syslog_priority, "Erro na conexao com ldap.";
			print STDOUT "action=reject\n\n";
			exit 0;
		}

		if ($search->count)
		{
			syslog $syslog_priority, "Lista Restrita: DUNNO: %s->%s", $attr{sender}, $attr{recipient}, $filter;
			print STDOUT "action=DUNNO\n\n";
		}
		else
		{
			syslog $syslog_priority, "Lista Restrita: Denied: %s->%s", $attr{sender}, $attr{recipient}, $filter;
			print STDOUT "action=reject\n\n";
		}

		%attr = ();
		$ldap->disconnect();
	}
	else
	{
		chop;
		syslog $syslog_priority, "warning: ignoring garbage: %.100s", $_;
	}
}

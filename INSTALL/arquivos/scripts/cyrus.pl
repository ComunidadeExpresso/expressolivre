#!/usr/bin/perl 

#################################################################
# Script que cria uma mailbox no cyrus, necessario pois o       #
# imapsync não faz isso automaticamente                         #
# 20/03/09 - William (at) prognus.com.br                        #
# Prognus Software Livre                                        #
# Baseado em: http://wiki.seduc.ce.gov.br/wiki/                 #
#################################################################

use Cyrus::IMAP::Admin;

#
# CONFIGURATION PARAMS
#
my $cyrus_server = "localhost";
my $cyrus_user = "expresso-admin";
my $usuario = "expresso-admin";
my $mechanism = "plain";

if (!$ARGV[0]) {
	die "Usage: $0 [expresso-admin password]\n";
} else {
	$cyrus_pass = "$ARGV[0]";
}

print "Criando usuario: $usuario. \n";
criarMailbox($usuario,'INBOX');
criarMailbox($usuario,'Sent');
criarMailbox($usuario,'Trash');
criarMailbox($usuario,'Drafts');
criarMailbox($usuario,'Spam');

sub criarMailbox {

    my ($usuario, $mailbox) = @_;

    $cyrus = Cyrus::IMAP::Admin->new($cyrus_server)
        or die "Falha ao conectar com o servidor Cyrus";
    $cyrus->authenticate($mechanism,'imap','',$cyrus_user,'0','10000',$cyrus_pass)
        or die "Falha ao autenticar";

    if ($mailbox eq "INBOX") {
            $mailbox = "user/". $usuario;
    }
    else {
            $mailbox = "user/". $usuario ."/". $mailbox;
    }

    $cyrus->create($mailbox)
}

print "Usuário $user criado com sucesso. \n";

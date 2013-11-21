#!/usr/bin/perl
# Takes a theme file and parses out the properties
# and makes a template file from it.
# Use it like so:
# theme2template.pl < infile > outfile
# by Stephan
# $Id: theme2template.pl,v 1.5 2003/08/28 14:29:08 ralfbecker Exp $

$t=localtime;
print << 'EOF';
# template file for making phpGroupWare themes using template2theme.pl

EOF
while( $_ = <STDIN> ) {
  chomp($_);
  next unless ( $_ =~ /\$phpgw_info\[\'theme\'\]\[\'(.*)\'\].*=.*\'(.*)\'.*/ );
  print '$1=$2\n';
}

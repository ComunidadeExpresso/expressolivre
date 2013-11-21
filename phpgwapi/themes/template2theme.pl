#!/usr/bin/perl
# Takes a simple text file of properties and turns them
# into a themes files.
# Use it like so:
# template2theme.pl < infile > outfile
# by Stephan
# $Id: template2theme.pl,v 1.5 2003/08/28 14:29:08 ralfbecker Exp $
$wrap='$phpgw_info['theme']['_KEY_']= '_VAL_'';
print '<?\n';
print << 'EOF';
# phpGroupWare Theme file
EOF

while( $_ = <STDIN> ) {
  next unless ( $_ =~ /^\s*(\w+)\s*=(.+)/ );
  $k=$1;
  $v=$2;
  my $foo = $wrap;
  $foo =~ s/_KEY_/$k/;
  $foo =~ s/_VAL_/$v/;
  print '$foo;\n';
}
print '?>';

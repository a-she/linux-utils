#! /usr/bin/perl

# LICENSE (GPL v3)
# Please refer to the LICENSE file in the same directory as this file
#
# PURPOSE
# This script parses and formats a list of all directories in the $PATH
# environment variable, making it easier for a human to see what's in $PATH.

my @setOut = `env | grep ^PATH=`;
#print "setOut =\|@setOut[0]\|\n";

my @pathSet = split(':', @setOut[0]);
# join('\n', split(':', $setOut)), "\n");

my $indent = 0;
foreach my $path (@pathSet) {
  if ($indent) {
    print "\n     ";
    $indent = 1;
  }
  $indent = 1;
  print "$path";
}

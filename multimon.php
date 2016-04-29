#! /usr/bin/php

<?php

# multimon.php - by Alfred She
#
# LICENSE (GPL v3)
#
# This program is free software: you can redistribute it and/or modify it under
# the terms of version 3 of the GNU General Public License, as published by the
# Free Software Foundation.
#
# This program is distributed in the hope that it will be useful, but WITHOUT
# ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or FITNESS
# FOR A PARTICULAR PURPOSE.  See the GNU General Public License for more details.
#
# You should have received a copy of the GNU General Public License along with
# this program.  If not, see <http://www.gnu.org/licenses/>.
#
# PURPOSE
# This command-line script activates multiple connected monitors using xrandr
# under Linux, and lays out monitor viewports horizontally, either left-to-right
# or right-to-left, depending on the command line arg.
#
# USAGE
#   multimon.php [arg]
#
# If [arg] is missing, or is the zero character ("0"), monitors are laid out
# horizonatally right-to-left, with the main display furthest to the right, and
# monitors are placed further and further to the left based on the order monitors
# are listed by xrandr. Any other value of [arg] specifies a left-to-right layout
# ordering, where the main display is furthest to the left.
#
# TERMINOLOGY
#
# "Connected": The display is physically connected to the Linux host AND the
# display is detected by xrandr.  A connected display may be active or inactive,
# where "active" is defined as follows.
#
# "Active": Display is connected AND xrandr can get its current resolution (and
# not just a list of possible resolutions), which implies the display showed at
# least a portion of the Linux desktop at the moment xrandr queried the display.

$xrandr_cmd = "xrandr"; # Use full path if xrandr isn't in your $PATH
$hr = "----------------";
$hr = "$hr$hr";
$hr = "$hr$hr";

$r2l = 1; # Default layout: right to left
if ($argc > 1) {
  $arg1 = $argv[1];
  if ($arg1 != "0") {
    $r2l = 0;
  }
}

if ($r2l) {
  echo "Layout: Right-to-left w/ main display furthest to the right.\n";
}
else {
  echo "Layout: Left-to-right w/ main display furthest to the left.\n";
}

# Capture xrandr output
$filebuf = shell_exec($xrandr_cmd);
echo "$hr\nxrandr output\n-------------\n$filebuf$hr\n";
# Note: next 3 lines also capture cmd output
#ob_start();
#system($xrandr_cmd);
#$filebuf = ob_end_flush();

$Lines = explode("\n", $filebuf);
$res_next = 0; # True when next line is expected to contain resolution info
$dispIdx = 0; # "Number of displays present" index / counter
$active = 0; # True when current line describes an active display

foreach ($Lines as $line) {

  if ($res_next) {
    # Process the line after the line where a connected display is found
    if ( preg_match('/^\D+(\d+)x(\d+) .+$/', $line, $matches) ) {
      $w = $matches[1];
      $h = $matches[2];
      $res = $w."x".$h;
      $dispW[$dispIdx]    = $w; # Save width for xrandr positioning
      $dispName[$dispIdx] = $disp;
      $dispRes[ $dispIdx] = $res; # Resolution
      if (! $active) { echo " max res: $res\n"; }
    }
    $dispIdx++;
    $res_next = 0;
    $active   = 0;
  }

  if ( preg_match('/^(.+)( connected.+)$/', $line, $matches) ) {
    # Connected display found (can be active or inactive)
    $res_next = 1;
    $disp = $matches[1];
    $rest = $matches[2];
     echo "Display $disp";
     if ( preg_match('/^\s+\D+\s+(\d+x\d+)\+.+$/', $rest, $matches) ) {
       $res = $matches[1];
       echo " active; current res: $res\n";
       $active = 1;
     }
  }
}

if ($dispIdx > 1) {
  # Form command string one display at a time
  $cmd = "xrandr";
  # Horizontal offset for display viewport placement
  $hpos = 0;
  for ($ii = 0; $ii < $dispIdx; $ii++) {
    $res = $dispRes[$ii];
    $cmd .= " --output {$dispName[$ii]} --mode $res --pos {$hpos}x0 --rotate normal";
    if ($r2l) { $hpos += $dispW[$ii]; }
    else      { $hpos -= $dispW[$ii]; }
  }
  echo "cmd=|$cmd|\n";
# system($cmd);
}
 else {
  echo "No external displays found, so this script isn't changing anything.
Make sure both ends of all power and signal cables are seated firmly,
and all displays are powered on, then re-run this script.  If you used xrandr
to remove a display, you may need to unplug its signal cable and plug it back
in to let xrandr detect the display again.\n";
  }

?>

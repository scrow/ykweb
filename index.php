<?php
/*

ykweb - Web based TOTP code access for Yubikeys
github.com/scrow/ykweb
Copyright (C) 2024 Steven Crow

This program is free software: you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation, either version 3 of the License, or
(at your option) any later version.

This program is distributed in the hope that it will be useful,
but WITHOUT ANY WARRANTY; without even the implied warranty of
MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
GNU General Public License for more details.

You should have received a copy of the GNU General Public License
along with this program.  If not, see <https://www.gnu.org/licenses/>.

*/

$system_ver = "1.0";
$system_rel = "2024-11-28";
$page_footer = 'ykweb ' . $system_ver . ' by Steve Crow, <A HREF="https://github.com/scrow/ykweb" TARGET="_blank">github.com/scrow/ykweb</A>';

echo('<HTML');
echo(' <HEAD>');
echo('  <TITLE>ykweb '.$system_ver.'</TITLE>');
echo(' </HEAD>');
echo(" <BODY onLoad=\"document.getElementById('ykpin').focus()\" BGCOLOR=\"#FFFFFF\" LINK=\"#0000FF\" VLINK=\"#0000FF\" ALINK=\"#FF0000\">");

$next_action = '';

# Read OATH info from device
$yk_status = shell_exec('ykman oath info');

# Display optional logo or other image
if(file_exists('logo.png')) {
	echo('<p><img src="logo.png"/></p>');
};

# If shell_exec returns blank, display error and exit
if(trim($yk_status)=="") {
	echo('<P>Fatal error:  No hardware token detected or ykman not found.  Verify physical connection to host and try again.</P>');
	echo('<HR><P><I>' . $page_footer . '</I></P></BODY></HTML>');
	die();
};

# Determine whether to prompt for PIN
if(strpos($yk_status, 'Password protection: enabled')) {
	$use_pin = true;
} else {
	$use_pin = false;
};

# Display OATH system information and current host time
echo('<PRE>ykweb ver ' . $system_ver . ' (' . $system_rel . ')' . "\r\n");
echo($yk_status);
echo('Host system time ' . date(DATE_RFC2822));
echo('</PRE>');

# Fetch codes
if(isset($_POST['ykpin'])) {
	# A PIN has been submitted or is not needed
	if($use_pin) {
		# PIN required
		# For security, drop non-alpha characters from PIN before running shell_exec
		$ykpin = preg_replace('/[\W]/', '', $_POST['ykpin']);
		# Retrieve code list from device with PIN provided
		$code_list = shell_exec('ykman oath accounts code -p ' . $ykpin);
	} else {
		# No PIN needed; password protection disabled on device
		# Retrieve code list from device with no PIN
		$code_list = shell_exec('ykman oath accounts code');
	};
	# Check for results
	if ((strpos($code_list,"ERROR: Authentication to the YubiKey failed. Wrong password?")) || strlen(trim($code_list))==0) {
		# Query failed, re-prompt for PIN
		$next_action = 'reauth';
	} else {
		# It should have worked.  Try to display codes.
		$next_action = 'show_codes';
	};
} else {
	# No PIN has been submitted.  Prompt for PIN
	$next_action = 'auth';
};

switch ($next_action) {
	case 'reauth':
		# Handle invalid PIN.  Show error message and prompt for PIN.
		echo('<P>Error:  PIN is incorrect or no TOTP accounts exist on hardware token</P>');
	case 'auth':
		# Handle PIN entry prompt
		echo('<P><FORM METHOD="post" ACTION="index.php">');
		if($use_pin) {
			# TOTP password protection is enabled on device.  Prompt for PIN
			echo('<P><LABEL FOR "ykpin">PIN: <label><INPUT TYPE="password" ID="ykpin" NAME="ykpin"/>');
			echo('&nbsp;<INPUT TYPE="Submit" VALUE="Generate codes"/></FORM></P>');
		} else {
			# No PIN required.  Display Generate codes button.
			echo('<INPUT TYPE="hidden" NAME="ykpin" VALUE="000000"/>');
			echo('&nbsp;<INPUT TYPE="Submit" VALUE="Generate codes"/></FORM></P>');
		};
		break;
	case 'show_codes':
		# Display the code list in a table
		$code_array = explode("\n",trim($code_list));
		$parsed_code_list = array();
		foreach($code_array as $this_code) {
			# Display code, unless touch is required for this slot
			if(!strpos($this_code, "Requires Touch")) {
				# Break apart service, account, and code from shell_exec return
				$space_pos = strrpos($this_code, " ");
				$account_info = explode(":", substr($this_code, 0, $space_pos));
				$output_code = array();
				$output_code['service'] = trim($account_info[0]);
				$output_code['account'] = trim($account_info[1]);
				$code_length = strlen($this_code) - $space_pos;
				$output_code['code'] = trim(substr($this_code, $space_pos));
				$parsed_code_list[] = $output_code;
			}; # else do not display
		};

		# prevent onLoad focus error when no PIN entry field exists
		echo('<DIV ID="ykpin"/>');

		# Build the table
		echo('<P><TABLE CELLSPACING=2 CELLPADDING=1 BORDER=1>');
		echo('<TR><TH>Service</TH><TH>Account</TH><TH>TOTP</TH></TR>');
		# Output each code
		foreach($parsed_code_list as $this_code) {
			echo('<TR><TD>' . trim($this_code['service']) . '</TD><TD>' . trim($this_code['account']) . '</TD><TD>' . $this_code['code'] . '</TD></TR>');
		};
		echo('</TABLE>');

		# Refresh and Help buttons
		echo('<FORM METHOD="POST" ACTION="index.php">');
		echo('<INPUT TYPE="hidden" NAME="ykpin" VALUE="' . $_POST['ykpin'] . '"/>');
		echo('<INPUT TYPE="submit" VALUE="Refresh Codes"/>&nbsp;');
		echo("<INPUT TYPE=\"button\" VALUE=\"Help\" onClick=\"javascript:alert('Code validity and reusability varies by service.  TOTP codes typically change every 30 seconds and can be used only once.  Refresh the page for new codes.  Codes will not be accurate if the host clock is incorrect.')\"/>");
		echo('</FORM></P>');

		break;
};

echo('<HR/><ADDRESS>' . $page_footer . '</ADDRESS></BODY></HTML>');
?>

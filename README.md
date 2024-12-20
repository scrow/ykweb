<!--
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
-->

# ykweb
Web based TOTP code access for Yubikeys

# Introduction

This simple utility reads and displays TOTP tokens generated by a Yubikey
device connected to the host via USB, using the official Yubikey Manager CLI.
It supports PIN-less and PIN-protected TOTP.

`ykweb` was developed to fill a very specific need for internal display of TOTP
tokens in a browser across a local area network.  It is not intended for
deployment on an Internet-exposed web server.  No authentication mechanisms are
provided.  It is up to the user to properly secure the installation against
unauthorized access.

No TOTP key management features are provided.  It is not possible to add,
delete, or change a TOTP record, nor add, change, or remove the Yubikey OATH
PIN via the web interface.  This is a read-only utility.

TOTP entries which require touch are not supported and will be filtered from
the results.  This utility returns all keys on the device.

Requires [Yubikey Manager CLI](https://github.com/Yubico/yubikey-manager).

# Installation

Connect a Yubikey TOTP device to a USB port on the host.  Ensure the Yubikey
Manager `ykman` application is installed and executable by your web server.

Clone this Github project into a content folder on your web server.

Refer to your web server and firewall documentation for guidance on securing
the installation.

It may be necessary to grant your web server permissions to access the Yubikey
Manager application.  Ubuntu users may need to implement the fixes [here][1]
and [here][2].

# Configuration

Copy the included `config.inc.php.sample` to `config.inc.php` and modify it
to suit your preferences with respect to activity logging.  Activity can be
logged to the file of your choice, and if desired, users can be permitted to
access the last 25 lines of the log file after authenticating to the Yubikey.

For purposes of logging, two authentication types are available: HTTP Basic
and Cloudflare Zero Trust Access.  The Cloudflare option is for `ykweb`
instances placed behind a [Cloudflare Zero Trust application][3].


# Usage

To retrieve TOTP codes, simply access the URL corresponding to the content
folder into which this project was cloned.  If the Yubikey has an OATH PIN
established (recommended) enter that PIN when prompted, otherwise press the
Generate Codes button.

# Disclaimer

The user assumes all risks associated with the installation of this utility.

This project is not associated with Yubico, manufacturers of the Yubikey
security devices and developer of the Yubikey Manager application.


[1]: https://github.com/Yubico/yubikey-manager/issues/630#issuecomment-2319051815
[2]: https://github.com/Yubico/yubikey-manager/issues/630#issuecomment-2476838966
[3]: https://developers.cloudflare.com/cloudflare-one/applications/

# Sort by custom fields

## Description
This extension allows you to sort the member list according to the custom profile fields.

## Requirements
- phpBB 3.3.1 or higher
- php 8.0.0 or higher

## Installation
Copy the extension to `phpBB3/ext/imcger/sortbycustomfields`.
Go to "ACP" > "Customise" > "Manage extensions" and enable the "Sort by custom fields" extension.

## Update
- Navigate in the ACP to `Customise -> Manage extensions`.
- Click the `Disable` link for "Sort by custom fields".
- Delete the `sortbycustomfields` folder from `phpBB3/ext/imcger/`.
- Copy the extension to `phpBB3/ext/imcger/sortbycustomfields`.
- Go to "ACP" > "Customise" > "Manage extensions" and enable the "Sort by custom fields" extension.

## Changelog

### v0.4.0 (30-09-2025)
- Changed to constructor property promotion
- Changed add Extension sufix to main_listener
- php min 8.0.0

### v0.3.0 (27-09-2025)
- Minor code change

### v0.2.0 (26-09-2025)
- Published

## Uninstallation
- Navigate in the ACP to `Customise -> Manage extensions`.
- Click the `Disable` link for "Sort by custom fields".
- To permanently uninstall, click `Delete Data`, then delete the `sortbycustomfields` folder from `phpBB3/ext/imcger/`.

## License
[GPLv2](https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html)

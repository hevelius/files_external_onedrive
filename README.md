# Files External Onedrive
### external storage support for NextCloud

[![License: GPL v2](https://img.shields.io/badge/License-GPL%20v2-blue.svg)](https://www.gnu.org/licenses/old-licenses/gpl-2.0.en.html)
[![Build Status](https://travis-ci.org/hevelius/files_external_onedrive.svg?branch=master)](https://travis-ci.org/hevelius/files_external_onedrive)
[![codecov](https://codecov.io/gh/hevelius/files_external_onedrive/branch/master/graph/badge.svg?token=AnnDxRQkSj)](https://codecov.io/gh/hevelius/files_external_onedrive)
[![Donate](https://img.shields.io/badge/Donate-PayPal-green.svg)](https://www.paypal.com/cgi-bin/webscr?cmd=_s-xclick&hosted_button_id=VUYAAKGJZB9U6)

Requirements:
* php with zlib support
* files_external app must be enabled :exclamation:

## Steps For Installation:
- Get the code
```bash
$ cd /server_path/apps
$ git clone https://github.com/hevelius/files_external_onedrive
$ cd files_external_onedrive
$ composer install
```
- Activate the files_external app from NC settings page or cli
```bash
$ cd /server_path/
$ php -f occ app:enable files_external
```
- Activate files_external_onedrive from NC settings page or cli
```bash
$ cd /server_path/
$ php -f occ app:enable files_external_onedrive
```
- Fill up the storage details (See Below _Configuring OAuth2_)
- Fire up the files page to see the ```OneDrive``` mounted as external storage

## Configuring OAuth2
Connecting OneDrive is a little more work because you have to create a onedrive app. 
- Log into the https://portal.azure.com/ page and search App Registration

##### Create new app
- Click New registration 
- Name Your App
- Make sure to select:  
`Accounts in any organizational directory (Any Azure AD directory - Multitenant) and personal Microsoft accounts (e.g. Skype, Xbox)`

![](./images/img.png)

- For **Redirect URL (optional)** add the following URL ```http://path/to/nextcloud/index.php/settings/user/externalstorages``` _(Replace http://path/to/nextcloud/index.php with you valid Nextcloud installation path, ensure that you don't leave `index.php` in the path.  Example: `https://nextcloud.myserver.com/settings/admin/externalstorages`)_
- Click **Register** to create your app.

##### Add client secret
- Under the section **Certificates and secrets** add new client secret, Name, choose expires:never.  
Make sure to copy the created key (you will not be able to do that later on). Key needs to be pasted in Nextcloud in last step

##### Add permissions to app
- Under the section **API permissions**
- Click add a permissions 
- Choose  Microsoft Graph, Delegated permissions
- add auth for [ User.Read | Files.ReadWrite.All | offline_access ] (the last is necessary to perform a correct token refresh)

##### On Nextcloud
- Copy Client Id and client secret then Go to Nextcloud ```/settings/user/externalstorages``` and Add a new storage **OneDrive**
- Fill the details Client Id, Client Secrets from you onedrive App page
- Click Grant Access and then you will be redirected for OAuth login
- After completing the OAuth you will be redirect back to Storage Section and you should see **green** mark along your storage configuration

References:
* https://github.com/NastuzziSamy/files_external_gdrive
* https://github.com/icewind1991/files_external_dropbox


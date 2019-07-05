# files_external_onedrive
OneDrive backend for NextCloud

Requires NextCloud 15.0 or later (Not tested on previous versions )

## Steps For Installation:
- Get the code
```bash
git clone https://github.com/hevelius/files_external_onedrive
cd files_external_onedrive
composer install
```
- Move this folder ```files_external_onedrive``` to ```nextcloud/apps```
- Activate the app from settings page
- Fill up the storage details (See Below _Configuring OAuth2_)
- Fire up the files page to see the ```OneDrive``` mounted as external storage

## Configuring OAuth2
- Connecting OneDrive is a little more work because you have to create a onedrive app. Log into the https://portal.azure.com/ page and click Create Your App
- Then choose which folders to share, or to share everything in your onedrive.
- Name Your App and then click Create App
- Under the section **OAuth2** Redirect URIs add a new URL ```http://path/to/nextcloud/index.php/settings/admin?sectionid=storage``` _(Replace http://path/to/nextcloud/index.php with you valid nextcloud installation path)_
- Then Go to nextcloud ```/settings/admin?sectionid=storage``` and Add a new storage **onedrive**
- Fill the details Client Id, Client Secrets from you onedrive App page
- Click Grant Access and then you will be redirected for OAuth login
- After completing the OAuth you will be redirect back to Storage Section and you should see **green** mark along your storage configuration
- That's it

References:
* https://github.com/NastuzziSamy/files_external_gdrive
* https://github.com/icewind1991/files_external_dropbox

## ToDo
* add logic to refresh token
* upload large files (graph limit upload to 4MB)
* add unit tests

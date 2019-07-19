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
- Connecting OneDrive is a little more work because you have to create a onedrive app. Log into the https://portal.azure.com/ page and search Azure Active Directory - App Registration
- Click on add new registration and fill form.
- Name Your App and then click Create App
- Under the section **Authentication** Redirect URIs add a new URL ```http://path/to/nextcloud/index.php/settings/user/externalstorages``` _(Replace http://path/to/nextcloud/index.php with you valid nextcloud installation path)_
- Under the section **Certificates and secrets** add new client secret
- Copy Client Id and client secret then Go to nextcloud ```/settings/user/externalstorages``` and Add a new storage **OneDrive**
- Fill the details Client Id, Client Secrets from you onedrive App page
- Click Grant Access and then you will be redirected for OAuth login
- After completing the OAuth you will be redirect back to Storage Section and you should see **green** mark along your storage configuration

References:
* https://github.com/NastuzziSamy/files_external_gdrive
* https://github.com/icewind1991/files_external_dropbox

## Latest features added
* a logic to refresh token (maybe it's not an elegat way to do this). After new token came back it's saved directly in Files_external config table)
* upload large files (over graph limit of 4MB per request) [using uploadSession]

## ToDo
* add unit tests

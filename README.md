# files_external_onedrive
OneDrive backend for ownCloud

Requires ownCloud 10.0 or later

## Steps For Installation:
- Get the code
```bash
git clone https://github.com/Alx2000y/files_external_onedrive
cd files_external_onedrive
composer install
```
- Move this folder ```files_external_onedrive``` to ```owncloud/apps```
- Activate the app from settings page
- Fill up the storage details (See Below _Configuring OAuth2_)
- Fire up the files page to see the ```OneDrive``` mounted as external storage

## Configuring OAuth2
- Connecting OneDrive is a little more work because you have to create a onedrive app. Log into the https://apps.dev.microsoft.com/ page and click Create Your App
- Then choose which folders to share, or to share everything in your onedrive.
- Name Your App and then click Create App
- Under the section **OAuth2** Redirect URIs add a new URL ```http://path/to/owncloud/index.php/settings/admin?sectionid=storage``` _(Replace http://path/to/owncloud/index.php with you valid owncloud installation path)_
- Then Go to owncloud ```/settings/admin?sectionid=storage``` and Add a new storage **onedrive**
- Fill the details Client Id, Client Secrets from you onedrive App page
- Click Grant Access and then you will be redirected for OAuth login
- After completing the OAuth you will be redirect back to Storage Section and you should see **green** mark along your storage configuration
- That's it


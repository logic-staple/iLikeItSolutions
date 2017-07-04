GoogleAnalytic
==============

A Symfony project created on July 4, 2017, 06:14 pm.


Bundle Configuration to set Google Analytics Accounts
======================================================

Add following code in app/config/config.yml
assetic:
    debug:          '%kernel.debug%'
    use_controller: '%kernel.debug%'
    filters:
        cssrewrite: ~

Add following code in app/config/routing.yml
google_analytics_api:
    resource: "@GoogleAnalyticsApiBundle/Resources/config/routing.yml"
    prefix:   /

Register the following bundle in AppKernal.php

new Symfony\Bundle\AsseticBundle\AsseticBundle(),
new iLikeItSolutions\GoogleAnalyticsApiBundle\GoogleAnalyticsApiBundle(),

Client Secret File steps:

1. Go to Google API console https://console.developers.google.com
2. Create APP and Activate the Analytics API in the Google API Console. 
3. Redirect URI http://localhost:8000/callback
4. After save Download JSON file and renamed it to client_secrets.json
5. Copy and paste client_secrets.json file to bundle location "iLikeItSolutions/GoogleAnalyticsApiBundle/Data/"

Google Analytics Account View ID steps:

1. Go to Google analytics site and login https://analytics.google.com
2. Click on Top left Corner to see accounts then 
   click Account Name >> Click Property Name >> then Copy View ID (Numeric) from Views Tab
3. Now Paste above View ID into bundle file path "iLikeItSolutions/GoogleAnalyticsApiBundle/Resources/config/service.yml" variable ga_view_id




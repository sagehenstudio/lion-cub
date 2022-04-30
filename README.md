# Lioncub

Lioncub is an extension for Easy Digital Downloads which creates dynamic IonCube licenses on-the-fly during download. Each EDD download can have unique license settings, which include a passphrase, headers, properties, restrictions and expiry. Customer name {NAME}, email {EMAIL}, date {DATE}, and time {TIME} shortcodes can be used in headers and properties for personalization of licenses.

## API endpoint

You can verify your make_license installation works after you have placed the make_license file on the server and set the location in the Lioncub general settings. Use the API key which you also set (any random characters will do) in the general settings to ping the API. This is mostly just for testing.

yourwebsite.com/wp-json/lioncub/make_license/?api_key=apikeysetinlioncubsettings

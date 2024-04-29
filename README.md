## SMS Connector
A third-party SMS connector module for FreePBX 16 and 17

### Overview

FreePBX offers SMS and MMS functionality through UCP (User Control Panel) and the Sangoma Connect softphones. 
This integrates tightly with Sangoma number services (SIPStation and VoIP Innovations) but until now there have been
no open source modules allowing integration with third-party providers. The aim of this module is to provide
a generic, expandable connector, with new providers added as contributed by the community.

### Features

* Send and receive SMS and MMS through UCP and Sangoma Connect 
* Mix-and-match: if you have numbers on multiple providers you are not limited to just one

### Providers

* Bandwidth: Messaging API v2 (https://dev.bandwidth.com/apis/messaging/) (Doesn't currently support status callbacks)
* Bulk Solutions (Bulkvs): API v1 (https://portal.bulkvs.com/api/v1.0/documentation)
* Commio/Thinq: (https://apidocs.thinq.com/#bac2ace6-7777-47d8-931e-495b62f01799)
* Flowroute: Messaging API v2.2, webhook v2.1 (https://developer.flowroute.com/api/messages/v2.2/)
* Sinch: SMS API (https://developers.sinch.com/docs/sms)
* SignalWire: Messaging API (https://developer.signalwire.com/guides/messaging-overview/)
* Siptrunk: Messaging API
* Skyetel: SMS and MMS API (https://support.skyetel.com/hc/en-us/articles/360056299914-SMS-MMS-API)
* Telnyx: Messaging API v2 (https://developers.telnyx.com/docs/api/v2/messaging)
* Twilio: Messaging API version 2010-04-01 (https://www.twilio.com/docs/sms)
* Voip.ms: SMS and MMS API (https://voip.ms/m/apidocs.php)
* Voxtelesys: Messaging API v1 (https://smsapi.voxtelesys.com)

### Installation

* `fwconsole ma downloadinstall https://github.com/simontelephonics/smsconnector/releases/download/v16.0.16.1/smsconnector-16.0.16.1.tar.gz`
* `fwconsole r`

### Configuration

#### General requirements

For inbound SMS/MMS and outbound MMS to work, you will need an HTTPS path inbound to your PBX from your provider(s). This means:
* Import or generate a TLS certificate in Certificate Manager
* Enable it on the web server using Sysadmin Pro or by manually configuring Apache
* Allow your provider(s) webhook addresses through the FreePBX Firewall and/or your network firewall
* Set the public DNS name in the `AMPWEBADDRESS` setting: Advanced Settings -> FreePBX Web Address

Sending of SMS/MMS requires verification and registration performed through your provider and is outside of the scope of this 
module or document. 

#### Provider Settings

Once set up with your provider, generate or locate the required credentials, typically an API key and secret,
and enter these into the SMS Connector -> Provider Settings screen.

In the provider portal, set the webhook URL for inbound SMS/MMS in the format shown on the Provider Settings screen ("Webhook Provider").

#### Adding Numbers

Enter a number (DID), pick the user(s) to which the DID should be assigned, and select the provider for that number.

### Possible Improvements

* Configuration within User Management (dummy screen right now)
* Automatic addition of FreePBX Firewall rules when module is installed and removal when module is uninstalled

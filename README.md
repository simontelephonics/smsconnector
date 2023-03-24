## SMS Connector
A third-party SMS connector module for FreePBX 16

### Overview

FreePBX offers SMS and MMS functionality through UCP (User Control Panel) and the Sangoma Connect softphones. 
This integrates tightly with Sangoma number services (SIPStation and VoIP Innovations) but until now there have been
no open source modules allowing integration with third-party providers. The aim of this module is to provide
a generic, expandable connector, with new providers added as contributed by the community.

### Features

* Send and receive SMS and MMS through UCP and Sangoma Connect 
* Mix-and-match: if you have numbers on multiple providers you are not limited to just one

### Limitations

* One-to-one number/user association currently (no shared numbers or multiple numbers on the same user)

### Providers

* Telnyx: Messaging API v2
* Flowroute: Messaging API v2.2, webhook v2.1

### Installation

* `fwconsole ma downloadinstall https://github.com/simontelephonics/smsconnector/releases/download/v16.0.2beta/smsconnector-16.0.2beta.tar.gz`
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

Once setup with your provider, generate an API key (Telnyx) or an API key and secret (Flowroute) and enter these into the 
SMS Connector -> Provider Settings screen.

In the provider portal, set the webhook URL in the format shown on the Provider Settings screen.

#### Adding Numbers

Enter a number (DID), pick the user to which the DID should be assigned, and select the provider for that number.

### Possible Improvements

* Multiple numbers per user or multiple users per number
* Configuration within User Management (dummy screen right now)
* Automatic addition of FreePBX Firewall rules when module is installed and removal when module is uninstalled

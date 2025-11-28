# DIDforsale Provider

This document describes how to configure and test the DIDforsale provider for the FreePBX SMS Connector module.

## Summary
- Provider: `providers/provider-Didforsale.php`
- API: DIDforsale API v4.0
- Features: Outbound SMS, MMS (via `media_urls`), inbound webhook handling, dual-credential auth (API Key + Access Token)

## Configuration
1. In FreePBX SMS Connector UI, add provider `DIDforsale` and enter the following:
   - **API Key** (`apikey`) - required
   - **Access Token** (`api_secret`) - required (API v4.0)

2. Save configuration and add your DIDs to the SMS Connector `DIDs` list so inbound messages map to the correct DID record.

## Webhook (Inbound SMS)
- Webhook URL: `https://<your-hostname>/smsconn/provider.php?provider=didforsale`
- DIDforsale sends `from`, `to`, `text` parameters via POST (or GET). The provider accepts either.
- Example webhook test using `curl`:

```
curl -X POST \
  -d "from=+12066907593" \
  -d "to=+12066907593" \
  -d "text=Test from DIDforsale" \
  https://<your-hostname>/smsconn/provider.php?provider=didforsale
```

Notes:
- The module stores inbound messages in the `sms_messages` table and emits the standard SMS inbound event so UCP and other modules receive notifications.

## Outbound SMS/MMS
- Endpoint used: `https://api.didforsale.com/didforsaleapi/index.php/api/V4/SMS/Send`
- Authentication: `APIKEY` and `accesstoken` are sent as HTTP headers by the provider implementation.
- MMS: Attach media by saving URLs in the message `media_urls` field (the module `sendMedia()` passes these automatically).

Example outbound curl (what the provider sends):

```
POST /didforsaleapi/index.php/api/V4/SMS/Send HTTP/1.1
Host: api.didforsale.com
Content-Type: application/json
APIKEY: <your-apikey>
accesstoken: <your-access-token>

{
  "from": "+1206...",
  "to": "+1612...",
  "text": "Hello from FreePBX",
  "media_urls": ["https://example.com/image.jpg"]
}
```

## Known Notes & Troubleshooting
- Ensure the DID used for inbound tests exists in the SMS Connector DID list; otherwise the inbound handler will fail with a missing `didid` mapping.
- If outbound sends fail with authentication errors, verify your DIDforsale account has the correct API credentials and that any regional/campaign registration (required by DIDforsale for certain origination) is completed.
- The provider logs failures using FreePBX logging (search for `DIDforsale Send Failed:` in logs).

## Compatibility
- Module Version: 16.0.19 (this change)
- FreePBX Versions: 16.0+
- FreePBX 17+: Compatible with the exception of issue #87 (https://github.com/simontelephonics/smsconnector/issues/87)

## Security
- Keep `api_secret` confidential. The provider marks that setting as `confidential` in the module UI.
- If you expose the webhook endpoint publicly, consider restricting IPs via firewall or using provider-side secrets if DIDforsale supports them.

## Further work
- If you want the provider to accept authentication in the request body instead of headers, we can add a configuration toggle and fallbacks for header/body methods.

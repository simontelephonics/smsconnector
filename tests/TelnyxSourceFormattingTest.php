<?php
// Run with: php tests/TelnyxSourceFormattingTest.php
// Executes the real provider methods against a fake HTTP client.
// No native PBX classes, database, credentials or network client are loaded.
namespace FreePBX\modules\Smsconnector\Provider {
    class providerBase {
        protected $name, $nameRaw, $APIUrlInfo, $APIVersion, $configInfo;
        public static array $delivered = [];
        public function __construct() {}
        protected function getConfig($name) { return ['api_key' => 'example-test-key']; }
        public function media_urls($id) { return ['https://example.invalid/test-media.png']; }
        protected function setDelivered($id) { self::$delivered[] = $id; }
    }
}
namespace {
    if (!function_exists('_')) { function _($s) { return $s; } }
    if (!defined('FPBX_LOG_INFO')) { define('FPBX_LOG_INFO', 1); }
    function freepbx_log($level, $message) {}
    class FakeTelnyxClient {
        public static array $payloads = [];
        public static bool $accept = true;
        public function requests($url) {
            if ($url !== 'https://api.telnyx.com/v2/messages') { throw new \RuntimeException('Unexpected test URL'); }
            return $this;
        }
        public function post($path, $headers, $json, $options) {
            self::$payloads[] = json_decode($json, true, 512, JSON_THROW_ON_ERROR);
            return (object)['success' => self::$accept, 'status_code' => self::$accept ? 200 : 400,
                'body' => self::$accept ? '{"data":{"id":"fixture"}}' : '{"errors":[{"code":"fixture-error"}]}'];
        }
    }
    class FreePBX { public static function Curl() { return new FakeTelnyxClient(); } }
    function requireTrue($value, $label) { if (!$value) { throw new \RuntimeException($label); } }
    require __DIR__ . '/../providers/provider-Telnyx.php';
    $fixedProvider = new \FreePBX\modules\Smsconnector\Provider\Telnyx();
    $checks = 0;
    foreach (['12025550100', '+12025550100'] as $from) {
        foreach (['12025550101', '12345', '123456'] as $to) {
            $fixedProvider->sendMessage(2, $to, $from, 'fixture'); $p = end(FakeTelnyxClient::$payloads);
            requireTrue($p['from'] === '+12025550100', 'SMS source must have one plus');
            requireTrue($p['to'] === (strlen($to) <= 6 ? $to : '+' . $to), 'Destination/shortcode behavior preserved');
            requireTrue($p['text'] === 'fixture', 'SMS body preserved'); $checks++;
        }
        $fixedProvider->sendMedia(3, '12025550101', $from, null); $p = end(FakeTelnyxClient::$payloads);
        requireTrue($p['from'] === '+12025550100', 'MMS source must have one plus');
        requireTrue($p['media_urls'] === ['https://example.invalid/test-media.png'], 'MMS URLs preserved');
        requireTrue(!array_key_exists('text', $p), 'Null MMS text behavior preserved'); $checks++;
    }
    FakeTelnyxClient::$accept = false;
    $deliveredBefore = \FreePBX\modules\Smsconnector\Provider\providerBase::$delivered;
    $threw = false;
    try { $fixedProvider->sendMessage(4, '12025550101', '+12025550100', 'fixture'); }
    catch (\Exception $e) { $threw = str_contains($e->getMessage(), 'Unable to send message'); }
    requireTrue($threw, 'Provider failures still propagate');
    requireTrue($deliveredBefore === \FreePBX\modules\Smsconnector\Provider\providerBase::$delivered, 'Rejected request is not marked delivered');
    echo json_encode(['isolated_provider_test' => true,
        'successful_payload_cases' => $checks, 'sms_mms_source_formats_destinations_shortcodes_and_failure_behavior_verified' => true,
        'actual_network_requests' => 0, 'pbx_files_modified' => 0,
        'carrier_delivery_tested' => false, 'php_version' => PHP_VERSION], JSON_PRETTY_PRINT) . "\n";
}

<?php

namespace Telnyx;

/**
 * @internal
 * @covers \Telnyx\Telnyx
 */
final class TelnyxTest extends \Telnyx\TestCase
{
    /** @var array */
    protected $orig;

    /**
     * @before
     */
    public function saveOriginalValues()
    {
        $this->orig = [
            'caBundlePath' => Telnyx::$caBundlePath,
        ];
    }

    /**
     * @after
     */
    public function restoreOriginalValues()
    {
        Telnyx::$caBundlePath = $this->orig['caBundlePath'];
    }

    public function testCABundlePathAccessors()
    {
        Telnyx::setCABundlePath('path/to/ca/bundle');
        static::assertSame('path/to/ca/bundle', Telnyx::getCABundlePath());
    }
    
    public function testAppInfo() {
        $app_info = [];
        $app_info['name'] = 'test_app';
        $app_info['partner_id'] = 'partner_id';
        $app_info['url'] = 'url/to/app';
        $app_info['version'] = '123';

        Telnyx::setAppInfo($app_info['name'], $app_info['version'], $app_info['url'], $app_info['partner_id']);
        static::assertSame($app_info, Telnyx::getAppInfo());
    }

    public function testSetsGets()
    {
        Telnyx::setApiKey('TEST89328');
        static::assertSame('TEST89328', Telnyx::getApiKey());

        Telnyx::setLogger(new \Telnyx\Util\DefaultLogger());
        $this->assertInstanceOf(\Telnyx\Util\LoggerInterface::class, Telnyx::getLogger());

        Telnyx::setClientId('CLIENTID455654');
        static::assertSame('CLIENTID455654', Telnyx::getClientId());

        Telnyx::setPublicKey('PUBLICKEY293847');
        static::assertSame('PUBLICKEY293847', Telnyx::getPublicKey());

        Telnyx::setApiVersion(2);
        static::assertSame(2, Telnyx::getApiVersion());

        Telnyx::setVerifySslCerts(true);
        static::assertSame(true, Telnyx::getVerifySslCerts());

        Telnyx::setAccountId('ACCOUNT38749');
        static::assertSame('ACCOUNT38749', Telnyx::getAccountId());

        Telnyx::setEnableTelemetry(false);
        static::assertSame(false, Telnyx::getEnableTelemetry());

        Telnyx::setMaxNetworkRetries(4);
        static::assertSame(4, Telnyx::getMaxNetworkRetries());
    }
}

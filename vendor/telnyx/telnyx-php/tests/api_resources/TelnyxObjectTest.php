<?php

namespace Telnyx;

/**
 * @internal
 * @covers \Telnyx\TelnyxObject
 */
final class TelnyxObjectTest extends \Telnyx\TestCase
{
    /** @var \ReflectionMethod */
    private $deepCopyReflector;

    /** @var \ReflectionMethod */
    private $optsReflector;

    /**
     * @before
     */
    public function setUpReflectors()
    {
        // Sets up reflectors needed by some tests to access protected or
        // private attributes.

        // This is used to invoke the `deepCopy` protected function
        $this->deepCopyReflector = new \ReflectionMethod(\Telnyx\TelnyxObject::class, 'deepCopy');
        $this->deepCopyReflector->setAccessible(true);

        // This is used to access the `_opts` protected variable
        $this->optsReflector = new \ReflectionProperty(\Telnyx\TelnyxObject::class, '_opts');
        $this->optsReflector->setAccessible(true);
    }

    public function testArrayAccessorsSemantics()
    {
        $s = new TelnyxObject();
        $s['foo'] = 'a';
        static::assertSame($s['foo'], 'a');
        static::assertTrue(isset($s['foo']));
        unset($s['foo']);
        static::assertFalse(isset($s['foo']));
    }

    public function testNormalAccessorsSemantics()
    {
        $s = new TelnyxObject();
        $s->foo = 'a';
        static::assertSame($s->foo, 'a');
        static::assertTrue(isset($s->foo));
        $s->foo = null;
        static::assertFalse(isset($s->foo));
    }

    public function testArrayAccessorsMatchNormalAccessors()
    {
        $s = new TelnyxObject();
        $s->foo = 'a';
        static::assertSame($s['foo'], 'a');

        $s['bar'] = 'b';
        static::assertSame($s->bar, 'b');
    }

    public function testCount()
    {
        $s = new TelnyxObject();
        static::assertCount(0, $s);

        $s['key1'] = 'value1';
        static::assertCount(1, $s);

        $s['key2'] = 'value2';
        static::assertCount(2, $s);

        unset($s['key1']);
        static::assertCount(1, $s);
    }

    public function testKeys()
    {
        $s = new TelnyxObject();
        $s->foo = 'bar';
        static::assertSame($s->keys(), ['foo']);
    }

    public function testValues()
    {
        $s = new TelnyxObject();
        $s->foo = 'bar';
        static::assertSame($s->values(), ['bar']);
    }

    public function testToArray()
    {
        $array = [
            'foo' => 'a',
            'list' => [1, 2, 3],
            'null' => null,
            'metadata' => [
                'key' => 'value',
                1 => 'one',
            ],
        ];
        $s = TelnyxObject::constructFrom($array);

        $converted = $s->toArray();

        static::assertInternalType('array', $converted);
        static::assertSame($array, $converted);
    }

    public function testToArrayRecursive()
    {
        // deep nested associative array (when contained in an indexed array)
        // or TelnyxObject
        $nestedArray = ['id' => 7, 'foo' => 'bar'];
        $nested = TelnyxObject::constructFrom($nestedArray);

        $obj = TelnyxObject::constructFrom([
            'id' => 1,
            // simple associative array that contains a TelnyxObject to help us
            // test deep recursion
            'nested' => ['object' => 'list', 'data' => [$nested]],
            'list' => [$nested],
        ]);

        $expected = [
            'id' => 1,
            'nested' => ['object' => 'list', 'data' => [$nestedArray]],
            'list' => [$nestedArray],
        ];

        static::assertSame($expected, $obj->toArray());
    }

    public function testNonexistentProperty()
    {
        $capture = \tmpfile();
        $origErrorLog = \ini_set('error_log', \stream_get_meta_data($capture)['uri']);

        try {
            $s = new TelnyxObject();
            static::assertNull($s->nonexistent);

            static::assertRegExp(
                '/Telnyx Notice: Undefined property of Telnyx\\\\TelnyxObject instance: nonexistent/',
                \stream_get_contents($capture)
            );
        } finally {
            \ini_set('error_log', $origErrorLog);
            \fclose($capture);
        }
    }

    public function testPropertyDoesNotExists()
    {
        $s = new TelnyxObject();
        static::assertNull($s['nonexistent']);
    }

    public function testJsonEncode()
    {
        $s = new TelnyxObject();
        $s->foo = 'a';

        static::assertSame('{"foo":"a"}', \json_encode($s));
    }

    public function testToString()
    {
        $s = new TelnyxObject();
        $s->foo = 'a';

        $string = (string) $s;
        $expected = "Telnyx\TelnyxObject JSON: {\n    \"foo\": \"a\"\n}";
        static::assertSame($expected, $string);
    }

    public function testReplaceNewNestedUpdatable()
    {
        $s = new TelnyxObject();

        $s->metadata = ['bar'];
        static::assertSame($s->metadata, ['bar']);
        $s->metadata = ['baz', 'qux'];
        static::assertSame($s->metadata, ['baz', 'qux']);
    }

    public function testSetPermanentAttribute()
    {
        $this->expectException(\InvalidArgumentException::class);

        $s = new TelnyxObject();
        $s->id = 'abc_123';
    }

    public function testSetEmptyStringValue()
    {
        $this->expectException(\InvalidArgumentException::class);

        $s = new TelnyxObject();
        $s->foo = '';
    }

    public function testSerializeParametersOnEmptyObject()
    {
        $obj = TelnyxObject::constructFrom([]);
        static::assertSame([], $obj->serializeParameters());
    }

    public function testSerializeParametersOnNewObjectWithSubObject()
    {
        $obj = new TelnyxObject();
        $obj->metadata = ['foo' => 'bar'];
        static::assertSame(['metadata' => ['foo' => 'bar']], $obj->serializeParameters());
    }

    public function testSerializeParametersOnBasicObject()
    {
        $obj = TelnyxObject::constructFrom(['foo' => null]);
        $obj->updateAttributes(['foo' => 'bar']);
        static::assertSame(['foo' => 'bar'], $obj->serializeParameters());
    }

    public function testSerializeParametersOnMoreComplexObject()
    {
        $obj = TelnyxObject::constructFrom([
            'foo' => TelnyxObject::constructFrom([
                'bar' => null,
                'baz' => null,
            ]),
        ]);
        $obj->foo->bar = 'newbar';
        static::assertSame(['foo' => ['bar' => 'newbar']], $obj->serializeParameters());
    }

    public function testSerializeParametersOnArray()
    {
        $obj = TelnyxObject::constructFrom([
            'foo' => null,
        ]);
        $obj->foo = ['new-value'];
        static::assertSame(['foo' => ['new-value']], $obj->serializeParameters());
    }

    public function testSerializeParametersOnArrayThatShortens()
    {
        $obj = TelnyxObject::constructFrom([
            'foo' => ['0-index', '1-index', '2-index'],
        ]);
        $obj->foo = ['new-value'];
        static::assertSame(['foo' => ['new-value']], $obj->serializeParameters());
    }

    public function testSerializeParametersOnArrayThatLengthens()
    {
        $obj = TelnyxObject::constructFrom([
            'foo' => ['0-index', '1-index', '2-index'],
        ]);
        $obj->foo = \array_fill(0, 4, 'new-value');
        static::assertSame(['foo' => \array_fill(0, 4, 'new-value')], $obj->serializeParameters());
    }

    public function testSerializeParametersOnArrayOfHashes()
    {
        $obj = TelnyxObject::constructFrom(['foo' => null]);
        $obj->foo = [
            TelnyxObject::constructFrom(['bar' => null]),
        ];

        $obj->foo[0]->bar = 'baz';
        static::assertSame(['foo' => [['bar' => 'baz']]], $obj->serializeParameters());
    }

    public function testSerializeParametersDoesNotIncludeUnchangedValues()
    {
        $obj = TelnyxObject::constructFrom([
            'foo' => null,
        ]);
        static::assertSame([], $obj->serializeParameters());
    }

    public function testSerializeParametersOnUnchangedArray()
    {
        $obj = TelnyxObject::constructFrom([
            'foo' => ['0-index', '1-index', '2-index'],
        ]);
        $obj->foo = ['0-index', '1-index', '2-index'];
        static::assertSame([], $obj->serializeParameters());
    }

    public function testSerializeParametersWithTelnyxObject()
    {
        $obj = TelnyxObject::constructFrom([]);
        $obj->metadata = TelnyxObject::constructFrom(['foo' => 'bar']);

        $serialized = $obj->serializeParameters();
        static::assertSame(['foo' => 'bar'], $serialized['metadata']);
    }

    public function testSerializeParametersOnReplacedTelnyxObject()
    {
        $obj = TelnyxObject::constructFrom([
            'source' => TelnyxObject::constructFrom(['bar' => 'foo']),
        ]);
        $obj->source = TelnyxObject::constructFrom(['baz' => 'foo']);

        $serialized = $obj->serializeParameters();
        static::assertSame(['baz' => 'foo'], $serialized['source']);
    }

    public function testSerializeParametersOnReplacedTelnyxObjectWhichIsMetadata()
    {
        $obj = TelnyxObject::constructFrom([
            'metadata' => TelnyxObject::constructFrom(['bar' => 'foo']),
        ]);
        $obj->metadata = TelnyxObject::constructFrom(['baz' => 'foo']);

        $serialized = $obj->serializeParameters();
        static::assertSame(['bar' => '', 'baz' => 'foo'], $serialized['metadata']);
    }

    public function testSerializeParametersOnArrayOfTelnyxObjects()
    {
        $obj = TelnyxObject::constructFrom([]);
        $obj->metadata = [
            TelnyxObject::constructFrom(['foo' => 'bar']),
        ];

        $serialized = $obj->serializeParameters();
        static::assertSame([['foo' => 'bar']], $serialized['metadata']);
    }

    public function testSerializeParametersOnSetApiResource()
    {
        $address = Address::constructFrom(['id' => 'cus_123']);
        $obj = TelnyxObject::constructFrom([]);

        // the key here is that the property is set explicitly (and therefore
        // marked as unsaved), which is why it gets included below
        $obj->address = $address;

        $serialized = $obj->serializeParameters();
        static::assertSame(['address' => $address], $serialized);
    }

    public function testSerializeParametersOnNotSetApiResource()
    {
        $address = Address::constructFrom(['id' => 'cus_123']);
        $obj = TelnyxObject::constructFrom(['address' => $address]);

        $serialized = $obj->serializeParameters();
        static::assertSame([], $serialized);
    }

    public function testSerializeParametersOnApiResourceFlaggedWithSaveWithParent()
    {
        $address = Address::constructFrom(['id' => 'cus_123']);
        $address->saveWithParent = true;

        $obj = TelnyxObject::constructFrom(['address' => $address]);

        $serialized = $obj->serializeParameters();
        static::assertSame(['address' => []], $serialized);
    }

    public function testSerializeParametersRaisesExceotionOnOtherEmbeddedApiResources()
    {
        // This address doesn't have an ID and therefore the library doesn't know
        // what to do with it and throws an InvalidArgumentException because it's
        // probably not what the user expected to happen.
        $address = Address::constructFrom([]);

        $obj = TelnyxObject::constructFrom([]);
        $obj->address = $address;

        try {
            $serialized = $obj->serializeParameters();
            static::fail('Did not raise error');
        } catch (\InvalidArgumentException $e) {
            static::assertSame(
                'Cannot save property `address` containing an API resource of type Telnyx\\Address. ' .
                "It doesn't appear to be persisted and is not marked as `saveWithParent`.",
                $e->getMessage()
            );
        } catch (\Exception $e) {
            static::fail('Unexpected exception: ' . \get_class($e));
        }
    }

    public function testSerializeParametersForce()
    {
        $obj = TelnyxObject::constructFrom([
            'id' => 'id',
            'metadata' => TelnyxObject::constructFrom([
                'bar' => 'foo',
            ]),
        ]);

        $serialized = $obj->serializeParameters(true);
        static::assertSame(['id' => 'id', 'metadata' => ['bar' => 'foo']], $serialized);
    }

    public function testDirty()
    {
        $obj = TelnyxObject::constructFrom([
            'id' => 'id',
            'metadata' => TelnyxObject::constructFrom([
                'bar' => 'foo',
            ]),
        ]);

        // note that `$force` and `dirty()` are for different things, but are
        // functionally equivalent
        $obj->dirty();

        $serialized = $obj->serializeParameters();
        static::assertSame(['id' => 'id', 'metadata' => ['bar' => 'foo']], $serialized);
    }

    public function testDeepCopy()
    {
        $opts = [
            'api_base' => Telnyx::$apiBase,
            'api_key' => 'apikey',
        ];
        $values = [
            'id' => 1,
            'name' => 'Telnyx',
            'arr' => [
                TelnyxObject::constructFrom(['id' => 'index0'], $opts),
                'index1',
                2,
            ],
            'map' => [
                '0' => TelnyxObject::constructFrom(['id' => 'index0'], $opts),
                '1' => 'index1',
                '2' => 2,
            ],
        ];

        $copyValues = $this->deepCopyReflector->invoke(null, $values);

        // we can't compare the hashes directly because they have embedded
        // objects which are different from each other
        static::assertSame($values['id'], $copyValues['id']);
        static::assertSame($values['name'], $copyValues['name']);
        static::assertSame(\count($values['arr']), \count($copyValues['arr']));

        // internal values of the copied TelnyxObject should be the same,
        // but the object itself should be new (hence the assertNotSame)
        static::assertSame($values['arr'][0]['id'], $copyValues['arr'][0]['id']);
        static::assertNotSame($values['arr'][0], $copyValues['arr'][0]);

        // likewise, the Util\RequestOptions instance in _opts should have
        // copied values but be a new instance
        static::assertSame(
            $this->optsReflector->getValue($values['arr'][0])->apiKey,
            $this->optsReflector->getValue($copyValues['arr'][0])->apiKey
        );
        static::assertNotSame(
            $this->optsReflector->getValue($values['arr'][0]),
            $this->optsReflector->getValue($copyValues['arr'][0])
        );

        // scalars however, can be compared
        static::assertSame($values['arr'][1], $copyValues['arr'][1]);
        static::assertSame($values['arr'][2], $copyValues['arr'][2]);

        // and a similar story with the hash
        static::assertSame($values['map']['0']['id'], $copyValues['map']['0']['id']);
        static::assertNotSame($values['map']['0'], $copyValues['map']['0']);
        static::assertNotSame(
            $this->optsReflector->getValue($values['arr'][0]),
            $this->optsReflector->getValue($copyValues['arr'][0])
        );
        static::assertSame(
            $this->optsReflector->getValue($values['map']['0'])->apiKey,
            $this->optsReflector->getValue($copyValues['map']['0'])->apiKey
        );
        static::assertNotSame(
            $this->optsReflector->getValue($values['map']['0']),
            $this->optsReflector->getValue($copyValues['map']['0'])
        );
        static::assertSame($values['map']['1'], $copyValues['map']['1']);
        static::assertSame($values['map']['2'], $copyValues['map']['2']);
    }

    public function testDeepCopyMaintainClass()
    {
        $phone_number = PhoneNumber::constructFrom(['id' => 1], null);
        $copyPhoneNumber = $this->deepCopyReflector->invoke(null, $phone_number);
        static::assertSame(\get_class($phone_number), \get_class($copyPhoneNumber));
    }

    public function testIsDeleted()
    {
        $obj = TelnyxObject::constructFrom([]);
        static::assertFalse($obj->isDeleted());

        $obj = TelnyxObject::constructFrom(['deleted' => false]);
        static::assertFalse($obj->isDeleted());

        $obj = TelnyxObject::constructFrom(['deleted' => true]);
        static::assertTrue($obj->isDeleted());
    }

    public function testDeserializeEmptyMetadata()
    {
        $obj = TelnyxObject::constructFrom([
            'metadata' => [],
        ]);

        static::assertInstanceOf(\Telnyx\TelnyxObject::class, $obj->metadata);
    }

    public function testDeserializeMetadataWithKeyNamedMetadata()
    {
        $obj = TelnyxObject::constructFrom([
            'metadata' => ['metadata' => 'value'],
        ]);

        static::assertInstanceOf(\Telnyx\TelnyxObject::class, $obj->metadata);
        static::assertSame('value', $obj->metadata->metadata);
    }
}

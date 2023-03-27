<?php

namespace Telnyx;

/**
 * Events are our way of letting you know when something interesting happens in
 * your account. See webhooks: 
 * https://developers.telnyx.com/docs/v2/development/api-guide/webhooks
 *
 * Events occur when the state of another API resource changes.
 *
 * @property string $id Unique identifier for the object.
 * @property string $object String representing the object's type. Objects of the same type share the same value.
  */
class Event extends ApiResource
{
    const OBJECT_NAME = 'event';
}

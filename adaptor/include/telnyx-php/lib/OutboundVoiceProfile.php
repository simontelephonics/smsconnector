<?php

namespace Telnyx;

/**
 * Class OutboundVoiceProfile
 *
 * @package Telnyx
 */
class OutboundVoiceProfile extends ApiResource
{
    const OBJECT_NAME = "outbound_voice_profile";

    use ApiOperations\All;
    use ApiOperations\Create;
    use ApiOperations\Delete;
    use ApiOperations\Retrieve;
    use ApiOperations\Update;

}

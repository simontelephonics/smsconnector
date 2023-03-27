<?php
require_once(__DIR__ . '/../init.php');

// Please fetch your API key from here https://portal.telnyx.com/#/app/api-keys
\Telnyx\Telnyx::setApiKey('######');

?>
<style>
    .code {
        background: #ddd;
        border: 1px solid #333;
        padding: 20px;
        border-radius: 3px;
    }
</style>
<?php
if (isset($_POST['action'])) {
    switch ($_POST['action']) {
        case 'send_verification':

            // Create a Verification profile
            $verify_profile = \Telnyx\VerifyProfile::create(["name" => "Test Profile"]);

            // Trigger a verification request and send SMS
            $verification = \Telnyx\Verification::create([
                'verify_profile_id' => $verify_profile['id'],
                'phone_number' => $_POST['phone'],
                'type' => 'sms'
            ]);
            ?>
            <h3>Verification was sent to: <?php echo $_POST['phone'];?></h3>
            <form method="post" action="">

                <input type="hidden" name="action" value="check_verification">
                <input type="hidden" name="verification_id" value="<?php echo $verification['id']; ?>">

                <button type="submit">Check Verification Status</button>
                <pre class="code">
                // Retrieve the status of the verification
                $verification = \Telnyx\Verification::retrieve('<?php echo $verification['id']; ?>');
                </pre>
            </form>
            <?php
        break;

        case 'check_verification':

            // Retrieve the status of the verification
            $verification = \Telnyx\Verification::retrieve($_POST['verification_id']);

            ?>
            <h3>Verification Status for ID: <?php echo $_POST['verification_id'];?></h3>
            <pre><?php print_r($verification); ?></pre>

            <form method="post" action="">
                
                <input type="hidden" name="action" value="check_verification">
                <input type="hidden" name="verification_id" value="<?php echo $_POST['verification_id']; ?>">

                <button type="submit">Check Verification Status</button>
                <pre class="code">
                // Retrieve the status of the verification
                $verification = \Telnyx\Verification::retrieve('<?php echo $_POST['verification_id']; ?>');
                </pre>
            </form>


            <h3>Submit Verification Code</h3>

            <form method="post" action="">
                
                <input type="hidden" name="action" value="submit_verification_code">
                <input type="hidden" name="verification_id" value="<?php echo $_POST['verification_id']; ?>">

                <input id="verify-code-text" type="text" name="verification_code" placeholder="000000" oninput="update_verification_code()">

                <button type="submit">Submit Verification Code</button>
                <pre class="code">
                // Submit verificaiton code
                $verify_status = \Telnyx\Verification::submit_verification('<?php echo $verification['phone_number']; ?>', '<span id="verify-code">000000</span>');
                </pre>
            </form>
            <script>
                function update_verification_code() {
                    var textbox = document.getElementById("verify-code-text");
                    var span = document.getElementById("verify-code");
                    span.innerHTML = textbox.value;
                }
            </script>
            <?php
        break;

        case 'submit_verification_code':

            // Retrieve the status of the verification
            $verification = \Telnyx\Verification::retrieve($_POST['verification_id']);

            // Submit verification code here
            $verify_status = \Telnyx\Verification::submit_verification($verification['phone_number'], $_POST['verification_code']);
            ?>
            <h3>Submitted Verification Code: <?php echo $_POST['verification_code']; ?></h3>

            <pre><?php print_r($verify_status); ?></pre>

            <form method="post" action="">
                
                <input type="hidden" name="action" value="check_verification">
                <input type="hidden" name="verification_id" value="<?php echo $_POST['verification_id']; ?>">

                <button type="submit">Check Verification Status</button>
                <pre class="code">
                // Retrieve the status of the verification
                $verification = \Telnyx\Verification::retrieve('<?php echo $_POST['verification_id']; ?>');
                </pre>
            </form>
            <?php
        break;
    }
}
else {
    ?>
    <h1>Telnyx Verify Demo</h1>
    <p>Hi and welcome to the Telnyx Verify API demo.</p>
    <form method="post" action="">

        <input type="hidden" name="action" value="send_verification">

        <p><label>Enter a phone number. Please remember to include <a target="_blank" href="https://support.telnyx.com/en/articles/1130706-sip-connection-number-formats">country code</a>:</label></p>
        <input id="phone-number-text" type="text" name="phone" placeholder="+15557770000" oninput="update_phone()">
        <button type="submit">Send Verification Code to Phone</button>
        <pre class="code">
        // Create a Verification profile
        $verify_profile = VerifyProfile::create(["name" => "Test Profile"]);

        // Trigger a verification request and send SMS
        $verification = Verification::create([
            'verify_profile_id' => $verify_profile['id'],
            'phone_number' => '<span id="phone-number-code">+15557770000</span>',
            'type' => 'sms'
        ]);
        </pre>
    </form>
    <script>
        function update_phone() {
            var textbox = document.getElementById("phone-number-text");
            var span = document.getElementById("phone-number-code");
            span.innerHTML = textbox.value;
        }
    </script>
    <?php
}


<?php echo !empty($statusMsg)?'<p class="alert alert-'.$statusMsg['status'].'">'.$statusMsg['msg'].'</p>':''; ?>

<?php if($verified == 1){ ?>
    <p>Mobile No: <?php echo $recipient_no; ?></p>
    <p>Verification Status: <b>Verified</b></p>
<?php }else{ ?>
<!-- OTP Verification form -->
<form method="post">
    <div class="form-group">
        <label>Enter Mobile No</label>
        <input type="text" name="mobile_no" value="<?php echo !empty($recipient_no)?$recipient_no:''; ?>" <?php echo ($otpDisplay == 1)?'readonly':''; ?>>
    </div>
    <?php if($otpDisplay == 1){ ?>
        <div class="form-group">
            <label>Enter OTP</label>
            <input type="text" name="otp_code">
        </div>
        <input type="submit" name="resend_otp" class="resend" value="Resend OTP"/>
    <?php } ?>

    <input type="submit" name="<?php echo ($otpDisplay == 1)?'submit_otp':'submit_mobile'; ?>" value="VERIFY"/>
</form>
<?php } ?>
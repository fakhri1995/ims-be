
<body>

<p>Hi <b><?= $data->name?></b>,</p>

<p>Thank you for applying as <b><?= $data->role_name ?></b> at Mitramas Infosys Global and we're thrilled that you'd like to join us here. We've now received your application and appreciate your interest with us. Our recruitment team is currently reviewing all applications, including yours. If you are selected to the next stage of hiring process, one of our recruiters will be in touch with you. </p>

<p>Every application matters to us, so we might take a couple of days to check your fit. We will try our best to deliver the feedback on your application as soon as possible, but if you don’t get a follow-up email from us within 2 weeks, it’s probably because:</p> 
<ul>
    <li>Your profile is still on hold</li>
    <li>Your profile doesn’t match with the qualifications</li>
    <li>The job position is already closed because we have selected a candidate who suits our requirement</li>
</ul>
<p>In the meantime, to stay connected with the latest company updates, you can find out more about us on our company <a href="https://id.linkedin.com/company/pt-mitramas-infosys-global" style="
    text-decoration: none;
    color: #0a66c2;
    font-weight: bold;">LinkedIn</a> page.Hope this helps. Thank you and good luck! </p>
<br>
<p>
    Regards,<br>
    Recruitment Team<br>
    Mitramas Infosys Global<br>
</p>
<img src="<?= $message->embed($image_url) ?>" height="75px" alt="" />
</body>
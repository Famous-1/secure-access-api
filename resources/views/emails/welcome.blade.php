@component('mail::message')
<!-- Header -->
<table width="100%" cellpadding="0" cellspacing="0" border="0" style="background-color: #f9f9f9; padding: 20px;">
    <tr>
        <td align="center">
            <img src="https://admin.greenquote.ng/images/email-temp-logo.png" alt="GreenQuote Logo" width="200" style="display: block; margin: 0 auto;">
        </td>
    </tr>
</table>

<!-- Main Content -->
<table width="600" cellpadding="20" cellspacing="0" border="0" align="center" style="background-color: #ffffff; font-family: Arial, sans-serif; font-size: 16px; color: #333333;">
    <tr>
        <td>
            <h1 style="font-size: 24px; margin-bottom: 20px;">Welcome to GreenQuote</h1>
            <p style="margin-bottom: 20px;">Dear {{ $user->firstname }} {{ $user->lastname }},</p>
            <p style="margin-bottom: 20px;">We are excited to welcome you to the GreenQuote family!</p>
            <p style="margin-bottom: 20px;">Thank you for choosing GreenQuote for your solar energy solutions. We take pride in delivering reliable, efficient, and timely services tailored to meet your unique energy needs.</p>

            <!-- Overview Section -->
            <h2 style="font-size: 20px; margin-bottom: 15px;">Here's a brief overview of what you can expect as a valued member of our community:</h2>
            <ul style="list-style-type: disc; padding-left: 20px; margin-bottom: 20px;">
                <li><strong>Seamless Ordering Process:</strong> Easily place orders through our intuitive platform.</li>
                <li><strong>Reliable Installation Service:</strong> Our dedicated team of professionals ensures timely installations.</li>
                <li><strong>Exceptional Customer Support:</strong> Our support team is available 24/7 to assist you with any questions or concerns. Reach out to us anytime at <a href="mailto:support@greenquote.com" style="color: #007bff; text-decoration: none;">support@greenquote.com</a> or call us at (234) 567-8901.</li>
                <li><strong>Special Offers and Updates:</strong> Stay tuned for exclusive offers and updates tailored just for you. Follow us on social media for the latest news and promotions.</li>
            </ul>

            <!-- Footer -->
            <hr style="border: 1px solid #e0e0e0; margin: 20px 0;">
            <p style="text-align: center; font-size: 14px; color: #666666;">
                GreenQuote<br>
                No 22 Solar Avenue, Eco Park, Green City, Lagos<br>
                Phone: (234) 567-8901<br>
                Email: <a href="mailto:support@greenquote.com" style="color: #007bff; text-decoration: none;">support@greenquote.com</a><br>
                Website: <a href="http://www.greenquote.ng" style="color: #007bff; text-decoration: none;">www.greenquote.ng</a>
            </p>
        </td>
    </tr>
</table>
@endcomponent
Alpine_Cms module provides an implementation of "QUOTE FORM" feature based on sending email message, allows to configure email recipients, email template, etc...

Customers can complete the quote form to send a quote to your store.

To configure "Quote form" in Admin Panel:

1. On the Admin sidebar, tap Stores. Then under Settings, choose "Configuration".

2. In the panel on the left choose "Alpine".

3. Expand  the "Quote form" section. If necessary, set Enable "Quote Form" to Yes or No.

4. Expand  the Email Options section. Then, fill the following Email Options:

	4.1. In the "Send Emails To" field, enter the email address where messages from the Quote form are sent.
	
	4.2. Set "Email Sender" to the store identity that appears as the sender of the message from the Quote form. For example: Custom Email 2.
	
	4.3. Set "Email Template" to the template that is used for messages sent from the Quote form.

        Note: new email templates can be added in /app/code/Alpine/Cms/etc/email_templates.xml config file.

5. When compete, tap Save Config.

6. Open the "Quote form" at the front using /quote URL, for example: https://www.accuride.com/quote

    After the quote form is submitted, a confirm message appears and the email will be send to store administrator.

        Note: "Quote form" is inherited from implementation of "Contact Us" Magento 2 core code: https://docs.magento.com/m2/ee/user_guide/stores/contact-us.html

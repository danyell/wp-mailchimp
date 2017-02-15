# wp-mailchimp
Preliminary code for a WordPress function to see if a user is subscribed to a specific MailChimp list

Wouldn't it be great to quickly know if a given WordPress (or Drift, or Hubspot) user was subscribed to a MailChimp mailing list?

The MailChimp API lets you do that. Problem is, you don't want to call the MailChimp API for every page-load.

My use-case requires that. I want to make parts of a home page conditionally visible, based on whether my visitor is/isn't subscribed to a specific MailChimp list.

So I've created this (rough draft of a) 2-part solution:

* **mclists.php** is a script you could run as a CRON job. It downloads all your MailChimp lists and all the members of all those lists, and stores it in JSON in files in a directory you specify.

* **is_user_sub.php** is a very simple PHP class that consults those pre-fetched files and provides a boolean function that tells you Yes/No if a given email address (or WordPress UserID) is subscribed to given list.

This is preliminary work with no error-checking, code-hardening, etc. I will update this README when the code gets closer to production-grdae.

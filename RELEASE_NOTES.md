Release song: https://youtu.be/9S_4bp2kyJs

tirreno is announcing version v0.9.6.

This release introduces a flexible rule engine that allows to create custom security
rules based on user context, SQL query improvements, minor bug fixes, and includes a
security patch.

We received a report from security expert Juan Soberanes (@cyberducky0o0) regarding a
blind SQL injection vulnerability in tirreno. After receiving the report, we
confirmed receipt within one hour and immediately reproduced the problem, developing
a patch the same day.

It's important to mention that this vulnerability can only be exploited if the user is
logged in and has knowledge of both the CSRF token and session token. Without these
conditions, the vulnerability cannot be abused. However, for authenticated users
who possess this token information, the blind SQL injection vulnerability allows them
to execute malicious SQL queries through AJAX requests intended for loading data grids.

The tirreno team highly appreciates Juan's report and help in maintaining tirreno’s
application security.

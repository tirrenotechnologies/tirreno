Release song: https://youtu.be/8hYgxwmHbxA

Important security update: tirreno v0.9.2.

Today, we received a report from IT security expert Sandro Bauer regarding
an XSS vulnerability in Tirreno. After receiving the report, we confirmed
receipt and immediately reproduced the problem, developing a patch the same
day. Briefly, the XSS vulnerability potentially allows attackers to post
malicious scripts by sending them through a payload. However, it's important
to clarify that the Tirreno platform does not directly receive user event data,
as it must come from the main web application, which we expect to be trustworthy.
Another aspect that makes it difficult to exploit this vulnerability is the
truncation of all data displayed in the dashboard.

The Tirreno team highly appreciates Sandro's report and help in maintaining Tirreno's application security.

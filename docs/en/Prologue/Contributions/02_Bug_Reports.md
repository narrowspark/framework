## Bug Reports

To encourage active collaboration, Narrowspark strongly encourages pull requests, not just bug reports. "Bug reports" may also be sent in the form of a pull request containing a failing test.

> :warning:
> If you think you’ve found a security issue, please use the special procedure [security][6]  instead.

#### Before submitting a bug:
* Double-check the official [documentation][1] to see if you’re not misusing the framework.
* Ask for assistance on [Stack Overflow][2], on the #support channel of the [Narrowspark Slack][3] if you’re not sure if your issue really is a bug.

If your problem definitely looks like a bug, report it using the [official bug tracker][4].
* Your issue should contain a title and a clear description of the issue.
* You should also include as much relevant information (OS, PHP version, Narrowspark version, enabled extensions, ...) as possible and a code sample (providing a unit test that illustrates the bug is best) that demonstrates the issue.
* If you want to provide a stack trace you got on an HTML page, be sure to provide the plain text version, which should appear at the bottom of the page. Do not provide it as a screenshot, since search engines will not be able to index the text inside them.
Same goes for errors encountered in a terminal, do not take a screenshot, but copy/paste the contents.
If the stack trace is long, consider enclosing it in a [<details> HTML tag][5].

> **Be wary that stack traces may contain sensitive information, and if it is the case, be sure to redact them prior to posting your stack trace.**

The goal of a bug report is to make it easy for yourself - and others - to replicate the bug and develop a fix.

Remember, bug reports are created in the hope that others with the same problem will be able to collaborate with you on solving it. Do not expect that the bug report will automatically see any activity or that others will jump to fix it. Creating a bug report serves to help yourself and others start on the path of fixing the problem.

[1]: https://narrowspark.com/doc
[2]: https://stackoverflow.com/questions/tagged/narrowspark
[3]: @todo_slack
[4]: https://github.com/narrowspark/framework/issues/new?template=Bug_report.md
[5]: https://developer.mozilla.org/en-US/docs/Web/HTML/Element/details
[6]: 06_Security_Issues.md

## Security Issues

This document explains how Narrowspark security issues are handled by the Narrowspark core team (Narrowspark being the code hosted on the main [narrowspark/framework][1], [narrowspark/narrowspark][2] Git repositories).

### Reporting a Security Issue

If you think that you have found a security issue in Narrowspark, **don’t use the bug tracker** and **don’t publish it publicly**. Instead, all security issues must be sent to **security [at] anolilab.com**.
Emails sent to this address are forwarded to the Narrowspark core team private mailing-list.

### Resolving Process

For each report, we first try to confirm the vulnerability. When it is
confirmed, the core team works on a solution following these steps:

* Send an acknowledgement to the reporter.
* Work on a patch.
* Get a CVE identifier from [mitre.org][3].
* Write a security announcement for the official [Narrowspark Blog][4] about the
   vulnerability. This post should contain the following information:

   * A title that always include the "Security release" string.
   * A description of the vulnerability.
   * The affected versions.
   * The possible exploits.
   * How to patch/upgrade/workaround affected applications.
   * The CVE identifier.
   * Credits.
* Send the patch and the announcement to the reporter for review.
* Apply the patch to all maintained versions of Narrowspark.
* Package new versions for all affected versions.
* Publish the post on the official [Narrowspark Blog][4] (it must also be added to
   the "[Security Advisories][5]" category).
* Update the public [security advisories database][6] maintained by the
   FriendsOfPHP organization.

> :note:
> Releases that include security issues should not be done on Saturday or Sunday, except if the vulnerability has been publicly posted.
>
> While we are working on a patch, please do not reveal the issue publicly.
>
> The resolution takes anywhere between a couple of days to a month depending on its complexity.

### Issue Severity

In order to determine the severity of a security issue we take into account
the complexity of any potential attack, the impact of the vulnerability and
also how many projects it is likely to affect. This score out of 15 is then
converted into a level of: Low, Medium, High, Critical, or Exceptional.

### Attack Complexity

*Score of between 1 and 5 depending on how complex it is to exploit the
vulnerability*

* 4 - 5 Basic: attacker must follow a set of simple steps
* 2 - 3 Complex: attacker must follow non-intuitive steps with a high level
  of dependencies
* 1 - 2 High: A successful attack depends on conditions beyond the attacker’s
  control. That is, a successful attack cannot be accomplished at will, but
  requires the attacker to invest in some measurable amount of effort in
  preparation or execution against the vulnerable component before a successful
  attack can be expected.

### Impact

*Scores from the following areas are added together to produce a score. The
score for Impact is capped at 6. Each area is scored between 0 and 4.*

* Integrity: Does this vulnerability cause non-public data to be accessible?
  If so, does the attacker have control over the data disclosed? (0-4)
* Disclosure: Can this exploit allow system data (or data handled by the
  system) to be compromised? If so, does the attacker have control over
  modification? (0-4)
* Code Execution: Does the vulnerability allow arbitrary code to be executed
  on an end users system, or the server that it runs on? (0-4)
* Availability: Is the availability of a service or app affected? Is
  it reduced availability or total loss of availability of a service /
  application? Availability includes networked services (e.g., databases) or
  resources such as consumption of network bandwidth, processor cycles, or
  disk space. (0-4)

### Affected Projects

*Scores from the following areas are added together to produce a score. The
score for Affected Projects is capped at 4.*

* Will it affect some or all using a component? (1-2)
* Is the usage of the component that would cause such a thing already
  considered bad practice? (0-1)
* How common/popular is the component (e.g. Console versus.HttpFoundation vs
  Cache)? (0-2)
* Are a number of well-known open source projects using Narrowspark affected
  that requires coordinated releases? (0-1)

# Score Totals

* Attack Complexity: 1 - 4
* Impact: 1 - 6
* Affected Projects: 1 - 4

# Severity levels

* Low: 1 - 5
* Medium: 6 - 10
* High: 11 - 12
* Critical: 13 - 14
* Exceptional: 15

> :tip:
> You can check your Narrowspark application for known security vulnerabilities using the `composer audit` command, see [Automatic Security Audit][8].

> This work, "Security Issues", is a derivative of "Security Issues" by [Symfony][7], used under [CC BY-SA 3.0](https://creativecommons.org/licenses/by-sa/3.0/).
> "Security Issues" is licensed under [CC BY-SA 4.0](https://creativecommons.org/licenses/by-sa/4.0/) by Narrowspark.

[1]: https://github.com/narrowspark/framework
[2]: https://github.com/narrowspark/narrowspark
[3]: https://mitre.org
[4]: @todo_blog
[5]: @todo_advisories
[6]: https://github.com/FriendsOfPHP/security-advisories
[7]: https://symfony.com/doc/current/contributing/code/security.html
[8]: @todo_missing_docs

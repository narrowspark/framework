## Backward Compatibility

Our [Backward Compatibility Promise][1] is very strict and allows developers to upgrade with confidence from one minor version of Narrowspark to the next one.

When a feature implementation cannot be replaced with a better one without breaking backward compatibility, Narrowspark deprecates the old implementation and adds a new preferred one along side. Read the conventions document to learn more about how deprecations are handled in Narrowspark.

### Rationale

This release process was adopted to give more predictability and transparency. It was discussed based on the following goals:

* Shorten the release cycle (allow developers to benefit from the new features faster);
* Give more visibility to the developers using the framework and Open-Source projects using Narrowspark;
* Improve the experience of Narrowspark core contributors: everyone knows when a feature might be available in Narrowspark;
* Coordinate the Narrowspark timeline with popular PHP projects that work well with Narrowspark and with projects using Narrowspark;
* Give time to the Narrowspark ecosystem to catch up with the new versions (package authors, documentation writers, translators, ...);
* Give companies a strict and predictable timeline they can rely on to plan their own projects development.

The six month period was chosen as two releases fit in a year. It also allows for plenty of time to work on new features and it allows for non-ready features to be postponed to the next version without having to wait too long for the next cycle.

The dual maintenance mode was adopted to make every Narrowspark user happy. Fast movers, who want to work with the latest and the greatest, use the standard version: a new version is published every six months, and there is a two months period to upgrade. Companies wanting more stability use the **LTS** versions: a new version is published every two years and there is a year to upgrade.

> This work, "Backward Compatibility", is a derivative of "Backward Compatibility" by [Symfony][2], used under [CC BY-SA 3.0](https://creativecommons.org/licenses/by-sa/3.0/).
> "Backward Compatibility" is licensed under [CC BY-SA 4.0](https://creativecommons.org/licenses/by-sa/4.0/) by Narrowspark.

[1]: Contributions/01_Our_Backward_Compatibility_Promise.md
[2]: https://symfony.com/doc/current/contributing/community/releases.html#backward-compatibility

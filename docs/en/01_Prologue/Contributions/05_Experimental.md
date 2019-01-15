## Experimental Features

All Narrowspark features benefit from our [Backward Compatibility Promise][1] to give developers the confidence to upgrade to new
versions safely and more often.

But sometimes, a new feature is controversial. Or finding a good API is not
easy. In such cases, we prefer to gather feedback from real-world usage, adapt
the API, or remove it altogether. Doing so is not possible with a no BC-break
approach.

To avoid being bound to our backward compatibility promise, such features can
be marked as **experimental** and their classes and methods must be marked with
the ``@experimental`` tag.

A feature can be marked as being experimental for only one minor version, and
can never be introduced in an [LTS version <releases-lts>][3]. The core team
can decide to extend the experimental period for another minor version on a
case by case basis.

To ease upgrading projects using experimental features, the change log must
explain backward incompatible changes and explain how to upgrade code.

> This work, "Experimental Features", is a derivative of "Experimental Features" by [Symfony][2], used under [CC BY-SA 3.0](https://creativecommons.org/licenses/by-sa/3.0/).
> "Experimental Features" is licensed under [CC BY-SA 4.0](https://creativecommons.org/licenses/by-sa/4.0/) by Narrowspark.

[1]: 01_Our_Backward_Compatibility_Promise.md
[2]: https://symfony.com/doc/current/contributing/code/experimental.html
[3]: ../03_Support_Policy.md

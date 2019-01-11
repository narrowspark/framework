## Versioning Scheme
Narrowspark's follow the **semantic versioning** strategy and they are published through a time-based model:

* A new **Narrowspark patch version** (e.g. 1.1.15, 2.1.7) comes out roughly every month. It only contains bug fixes, so you can safely upgrade your apps;
* A new **Narrowspark minor version** (e.g. 1.1, 2.2, 3.1) comes out every six months: one in **February** and one in **August**. It contains bug fixes and new features, but it **doesn't include any breaking** change, so you can safely upgrade your apps;
* A new **Narrowspark major version** (e.g. 2.0, 3.0) comes out every two years. It can contain breaking changes, so you may need to do some changes in your apps before upgrading.

When referencing the Narrowspark components from your application or package, you should always use a version constraint such as `1.1.*`, since major releases of Narrowspark do include breaking changes. However, we strive to always ensure you may update to a new major release in one day or less.

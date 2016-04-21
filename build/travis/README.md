# Build Scripts

This directory contains the scripts that travis uses to build the project.

The scripts in [the php directory](php/) are ran when travis is testing the given version. [`all.sh`](php/all.sh) is ran for every version.

Tests on the entire [`narrowspark/framework`](https://github.com/narrowspark/framework) repository,
and then sends code coverage reports out to [Codecov](https://codecov.io/github/narrowspark/framework).

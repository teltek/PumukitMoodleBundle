Installation Guide
==================

The following files are used to install PuMoodle into Moodle:
* install/mod.zip
* install/repository.zip
* install/filter.zip

Follow the steps at [PuMoodle Installation Guide](Resources/doc/PuMoodleInstallationGuide.md).


Admin Guide
===========

If you modify mod, repository or filter folders,
create the new zip files following these instructions:

```bash
$ cd /path/to/pumoodle
$ cd mod
$ zip -r ../install/mod.zip pumukit/
$ cd ../repository
$ zip -r ../install/repository.zip pumukit/
$ cd ../filter
$ zip -r ../install/filter.zip pumukit/
```

NOTE: `/path/to/pumoodle` could be, in a PuMuKIT2 installation, on `/path/to/pumukit2/vendor/teltek/pmk2-moodle-bundle/Resources/data/pumoodle/`

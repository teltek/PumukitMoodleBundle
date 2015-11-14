Installation Guide
==================

The following files are used to install PuMoodle into Moodle:
* [install/mod.zip](install/mod.zip?raw=true)
* [install/repository.zip](install/repository.zip?raw=true)
* [install/filter.zip](install/filter.zip?raw=true)

Follow the steps at [PuMoodle Installation Guide](../../doc/PuMoodleInstallationGuide.md).


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

NOTE: `/path/to/pumoodle` location:
* In this Bundle: `Resources/data/pumoodle/`
* In a PuMuKIT2 installation: `/path/to/pumukit2/vendor/teltek/pmk2-moodle-bundle/Resources/data/pumoodle/`

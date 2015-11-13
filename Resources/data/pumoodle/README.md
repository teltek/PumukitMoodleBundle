Installation Guide
==================

The following files are used to install PuMoodle into Moodle:
* install/mod.zip
* install/repository.zip
* install/filter.zip


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
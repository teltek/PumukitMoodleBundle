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
$ cd mod
$ zip -r ../install/modpersonal.zip pmkpersonalvideos
$ zip -r ../install/modurl.zip pmkurlvideos
$ cd ../repository
$ zip -r ../install/pmksearch.zip pmksearch
$ zip -r ../install/repository.zip pumukit/
$ cd ../filter
$ zip -r ../install/filter.zip pumukit/
$ cd ../blocks
$ zip -r ../install/pmkbackoffice.zip pmkbackoffice
```

NOTE: `/path/to/pumoodle` location:
* In this Bundle: `Resources/data/pumoodle/`
* In a PuMuKIT2 installation: `/path/to/pumukit/vendor/teltek/pumukit-moodle-bundle/Resources/data/pumoodle/`


Backwards Compatibility
=======================

In the 1.1.0 version the `pumukit` module was renamed to `pmkpersonalvideos`. To ensure backwards compatibility with old installations execute these instructions:

```bash
cp -r ./mod/pmkpersonalvideos/ ./mod/pumukit
mv ./mod/pumukit/backup/moodle2/restore_pmkpersonalvideos_activity_task.class.php ./mod/pumukit/backup/moodle2/restore_pumukit_activity_task.class.php
mv ./mod/pumukit/backup/moodle2/restore_pmkpersonalvideos_stepslib.php ./mod/pumukit/backup/moodle2/restore_pumukit_stepslib.php
mv ./mod/pumukit/backup/moodle2/backup_pmkpersonalvideos_stepslib.php ./mod/pumukit/backup/moodle2/backup_pumukit_stepslib.php
mv ./mod/pumukit/backup/moodle2/backup_pmkpersonalvideos_activity_task.class.php ./mod/pumukit/backup/moodle2/backup_pumukit_activity_task.class.php
mv ./mod/pumukit/lang/es/pmkpersonalvideos.php ./mod/pumukit/lang/es/pumukit.php
mv ./mod/pumukit/lang/en/pmkpersonalvideos.php ./mod/pumukit/lang/en/pumukit.php
sed -i "s/pmkpersonalvideos/pumukit/g" ./mod/pumukit/version.php
sed -i "s/pmkpersonalvideos/pumukit/g" ./mod/pumukit/view.php
sed -i "s/pmkpersonalvideos/pumukit/g" ./mod/pumukit/README.txt
sed -i "s/pmkpersonalvideos/pumukit/g" ./mod/pumukit/lib.php
sed -i "s/pmkpersonalvideos/pumukit/g" ./mod/pumukit/backup/moodle2/backup_pumukit_activity_task.class.php
sed -i "s/pmkpersonalvideos/pumukit/g" ./mod/pumukit/backup/moodle2/restore_pumukit_activity_task.class.php
sed -i "s/pmkpersonalvideos/pumukit/g" ./mod/pumukit/backup/moodle2/backup_pumukit_stepslib.php
sed -i "s/pmkpersonalvideos/pumukit/g" ./mod/pumukit/backup/moodle2/restore_pumukit_stepslib.php
sed -i "s/pmkpersonalvideos/pumukit/g" ./mod/pumukit/settings.php
sed -i "s/pmkpersonalvideos/pumukit/g" ./mod/pumukit/locallib.php
sed -i "s/pmkpersonalvideos/pumukit/g" ./mod/pumukit/index.php
sed -i "s/pmkpersonalvideos/pumukit/g" ./mod/pumukit/mod_form.php
sed -i "s/pmkpersonalvideos/pumukit/g" ./mod/pumukit/classes/event/course_module_instance_list_viewed.php
sed -i "s/pmkpersonalvideos/pumukit/g" ./mod/pumukit/classes/event/course_module_viewed.php
sed -i "s/pmkpersonalvideos/pumukit/g" ./mod/pumukit/lang/es/pumukit.php
sed -i "s/pmkpersonalvideos/pumukit/g" ./mod/pumukit/lang/en/pumukit.php
sed -i "s/pmkpersonalvideos/pumukit/g" ./mod/pumukit/db/access.php
sed -i "s/pmkpersonalvideos/pumukit/g" ./mod/pumukit/db/install.php
sed -i "s/pmkpersonalvideos/pumukit/g" ./mod/pumukit/db/upgrade.php
sed -i "s/pmkpersonalvideos/pumukit/g" ./mod/pumukit/db/log.php
sed -i "s/pmkpersonalvideos/pumukit/g" ./mod/pumukit/db/install.xml
sed -i "s/pmkpersonalvideos/pumukit/g" ./mod/pumukit/db/uninstall.php

cd mod
zip -r ../install/pumukit.zip pumukit

```
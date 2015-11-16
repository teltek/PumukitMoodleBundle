Installation Guide
==================

*This page is updated to the PuMuKIT2-moodle-bundle master and to the PuMuKIT 2.1.0*

Requirements
------------

Steps 1 and 2 requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.


Step 1: Introduce repository in the root project composer.json
--------------------------------------------------------------

Open a command console, enter your project directory and execute the
following command to add this repo:

```bash
$ cd /path/to/pumukit2
$ composer config repositories.pumukitmoodlebundle vcs https://github.com/teltek/PuMuKIT2-moodle-bundle
```

Step 2: Download the Bundle
---------------------------

From your project directory, execute the following command to download
the latest stable version of this bundle:

```bash
$ composer require teltek/pmk2-moodle-bundle dev-master
```

Step 3: Install the bundle
--------------------------

Install the bundle by executing the following line command. This command updates
the Kernel to enable the bundle (app/AppKernel.php) and loads the routing
(app/config/routing.yml) to add the bundle routes.

```bash
$ php app/console pumukit:install:bundle Pumukit/MoodleBundle/PumukitMoodleBundle
```

Step 4: Configure the bundle
----------------------------

Add the configuration to the `app/config/parameters.yml` file of your PuMuKIT2 directory.

```
pumukit_moodle:
    role: actor
    password: ThisIsASecretPasswordChangeMe
```

* `role` defines the role code the professor should be added with in a video. For example: actor.
* `password` defines the secret password between Pumukit and Moodle. It's the same password Moodle uses to install PuMoodle.

Step 5: Install PuMoodle extension into Moodle
----------------------------------------------

Follow the steps at [PuMoodleInstallationGuide.md](PuMoodleInstallationGuide.md)
to install the PuMoodle extension into your Moodle instance.

Installation Guide
==================

*This page is updated to the pumukit-moodle-bundle master and to the PuMuKIT 2.1.0*

Requirements
------------

Steps 1 requires you to have Composer installed globally, as explained
in the [installation chapter](https://getcomposer.org/doc/00-intro.md)
of the Composer documentation.


Step 1: Download the Bundle
---------------------------

From your project directory, execute the following command to download
the latest stable version of this bundle:

```bash
$ composer require teltek/pmk2-moodle-bundle dev-master
```

Step 2: Install the bundle
--------------------------

Install the bundle by executing the following line command. This command updates
the Kernel to enable the bundle (app/AppKernel.php) and loads the routing
(app/config/routing.yml) to add the bundle routes.

```bash
$ php app/console pumukit:install:bundle Pumukit/MoodleBundle/PumukitMoodleBundle
```

Adds the 'Moodle' publication channel to be able to publish videos specifically on Moodle.
```bash
$ php app/console moodle:init:pubchannel
```

Step 3: Configure the bundle
----------------------------

Add the configuration to the `app/config/parameters.yml` file of your PuMuKIT directory.

```
pumukit_moodle:
    role: actor
    password: ThisIsASecretPasswordChangeMe
```

* `role` defines the role code the professor should be added with in a video. For example: actor.
* `password` defines the secret password between Pumukit and Moodle. It's the same password Moodle uses to install PuMoodle.

Step 4: Install PuMoodle extension into Moodle
----------------------------------------------

Follow the steps at [PuMoodleInstallationGuide.md](PuMoodleInstallationGuide.md)
to install the PuMoodle extension into your Moodle instance.

Step 5: [Optional] Update encoder profiles
----------------------------------------------

To generate the broadcast copy of the media  automatically update the encoder profiles.
Modify the `app/config/encoder.yml` file adding `PUCHMOODLE` as target of `video_h264` and `audio_aac` profiles.



```yml
pumukit_encoder:
    profiles:
        video_h264:
            target: PUCHWEBTV PUCHMOODLE PUCHPODCAST
...
        audio_aac:
            target: PUCHWEBTV PUCHMOODLE PUCHPODCAST*

```
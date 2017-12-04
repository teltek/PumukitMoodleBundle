# Moodle Integration with eduOER

## Contents

1. [Introduction](#introduction)

2. [Module](#module)

    2.1. [Module installation](#module-installation-and-configuration)

    2.2. [Module configuration](#module-configuration)

    2.3. [Installation check](#installation-check)


## Introduction

Follow the next steps to integrate Moodle with eduOER.

## Module

The module allow us to embed videos directly into the Moodle course as a resource.
* The 'PuMuKIT (Atto)' module will show the search page to list all the available videos and add them to the Moodle course.

### Module installation

The process to install the module is as follows. The only difference will be the name of the '.zip' file that has to be uploaded to the Moodle platform.

*Note: To begin the installation, we need to have a Moodle administrator account.*

* Login on Moodle as administrator.

* Go to *Administration -> Site Administration -> Plugins -> Install plugins* on the left-side
menu.

* Select *Atto HTML editor / Atto plugin (atto)* in *Plugin Type*. (If the *Plugin Type* option does not appear, click on 'Show more...').

* Upload the file named *pumukit.zip* with the "Zip Package" field from the `Resources/data/pumoodle/editor/atto/plugins` folder. A window opens to select the file.

* Choose the file and click on *"Upload this file"*.

* Mark the checkbox and click on *Install plugin from the ZIP file* (previous image).

A validation window is shown.

* Press "Install add-on".

Here we see all the plugins that require update or are pending to install. The Pumukit module
should be listed here.

* To continue the installation click on "Upgrade Moodle Database now".

A message will be shown indicating the successful result of the installation.

* Click on “Continue”. The Module Configuration will be loaded.


### Module configuration

To configure each module we need to set up the following parameters:
- Pumukit URL: Pumukit server address followed by “/pumoodle/searchmultimediaobjects”
(http://URL/pumoodle/searchmultimediaobjects)
- Modal dialog title: Add eduOER video

Then click on “Save changes”. The module will be ready to use.

### Enable module

* Go to *Administration -> Site Administration -> Plugins -> Text editors -> Atto HTML editor* and enable the PuMuKIT (Atto) Plugin for all courses in *Toolbar config*:
```
files = image, media, managefiles, pumukit
```

### Installation check

To check the correct installation of the module we just have to upload a video.

Login on Moodle as a teacher or using a user account.

Go to any course, or create a new course for testing.

To upload videos into a course, activate the edition on “Turn editing on” and click on "Add an
activity or a resource." Select “Page” and click on "Add".

In the editor, click on the orange icon of a camera to add vieos fro Pumukit.

After saving changes you can click on the video link to watch the video.


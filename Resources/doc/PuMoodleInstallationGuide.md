# PuMoodle Installation Guide

*This page is updated to the PuMuKIT2-moodle-bundle 1.0.0 and to the PuMuKIT 2.1.0*

## Contents

1. [Introduction](#introduction)

2. [Modules](#modules)

    2.1. [Modules installation](#modules-installation-and-configuration)

    2.2. [Modules configuration](#modules-configuration)

    2.3. [Installation check](#installation-check)

3. [Repository and Filter installation](#repository-and-filter-installation)

    3.1. [Repository installation and configuration](#repository-installation-and-configuration)

    3.2. [Filter installation and configuration](#filter-installation-and-configuration)

    3.3. [Installation check](#installation-check-1)

## Introduction

Pumoodle is a module created for Moodle allowing video embedding from Opencast and
PuMuKIT so that you can easily insert videos from those platforms in the courses created.

## Modules

The modules allow us to embed videos directly into the Moodle course as a resource. In the current version of our MoodleBundle there are two modules, each having a different functionality:
* The 'Personal Videos' module will list all the videos we currently own and that we are able to publish on Moodle.
* The 'Video URLs' module will let us add any video that is published on the PuMuKIT WebTV Portal.

### Modules installation

Both modules are installed in the same way. The only difference will be the name of the '.zip' file that has to be uploaded to the Moodle platform.

To begin the installation, we need to have a Moodle administrator account.

Login on Moodle as administrator.

Go to “Administration” -> “Site Administration” -> “Plugins” -> “Install add ons” on the left-side
menu.



Select “Activity module (mod)” in “Plugin Type”.

Upload the file named “pmkpersonalvideos.zip” (For the 'Personal Videos' module) or the "pmkurlvideos.zip" (For the 'Video URLs' module) to the “Zip Package”. A window opens to select the file.

Choose the file and click “Upload this file”.



Mark the checkbox and click "Install add-on from the ZIP file" (previous image).

A validation window is shown. Press "Install add-on".



Here we see all the plugins that require update or are pending to install. The Pumukit module
should be listed here.

To continue the installation click on "Upgrade Moodle Database now".



A message will be shown indicating the successful result of the installation.



Click on “Continue”. The Module Configuration will be loaded.

### Modules configuration

To configure each module we need to set up the following parameters:
- Pumukit Server Url : Pumukit server address followed by “/pumoodle/”
(http://URL/pumoodle/)
- Pumukit Shared Secret : Password to code the requests to the Pumukit server.

Then click on “Save changes”. The module will be ready to use.

### Installation check

To check the correct installation of the module we just have to upload a video.

Login on Moodle as a teacher or using a user account.

Go to any course, or create a new course for testing.

To upload videos into a course, activate the edition on “Turn editing on” and click on "Add an
activity or a resource." Select “Recorded lecture” and click on "Add".



In "Select a video" section we can see all the series and videos that belong to a professor.
Professor is identified in Moodle by the logged user.



If the logged user in Moodle has not got any videos assigned an error message will be
shown.



In that case, you should assign videos to a professor in Pumukit.

For further details to how to assign a video to a teacher, see Pumukit User Manual (4.2.2.4
People section).

NOTE: In Pumukit, the video must be assigned to a person with the role “Actor” from the
People section. The email from the person in Pumukit and the email from the user logged in
PuMoodle must be the same.

Once the video is selected and the required metadata are introduced click on “Save changes
and return to the course”.



After saving changes you can click on the video link to watch the video.


## Repository and Filter installation

Here we install the repository and the filter that allow us to embed videos into a web page
created in a course in Moodle.

### Repository installation and configuration

To install the repository go to “Administration” -> “Site Administration” -> “Plugins” -> “Install add
ons” on the left-side menu.



Select “Repository” in “Plugin Type”.

Upload the file named “repository.zip” to the “Zip Package”. A window opens to select the file.
Choose the file and click “Upload this file”.



Mark the checkbox and click "Install add-on from the ZIP file" (previous image).



Here we see all extensions that require update or are pending to install. The “Pumukit videos
repository” should be listed in Repository section.

To finish the installation click on "Upgrade Moodle Database now".

To configure the repository go to “Administration” -> “Site Administration” -> “Plugins” ->
“Repositories” -> “Manage repositories”.



Enable the repository called “PuMuKit videos”. The following page will be opened.



Mark the checkbox “Allow users to add elements to the course repository”.

Click on “Save”.

Click on the “Settings” button of the “PuMuKit videos” repository.



Create a repository instance clicking on “Create a repository instance”.



To configure the repository we need to set up the following parameters:
- Name : Repository name (it will be shown in Moodle). Use “Test” for the example.
- Pumukit Server Url : Pumukit server address followed by “/pumoodle/”
(http://URL/pumoodle/)
- Pumukit Shared Secret : Password to code the requests to the Pumukit server.



Then click on “Save” and the repository will be configured and ready to use.

### Filter installation and configuration

To install the filter go to “Administration” -> “Site Administration” -> “Plugins” -> “Install add ons”
on the left-side menu.



Select “Text filter (filter)” in “Plugin Type”.

Upload the file named “filter.zip” to the “Zip Package”. A window opens to select the file. Choose
the file and click “Upload this file”.



Mark the checkbox and click "Install add-on from the ZIP file" (previous image).

A window is shown validating all the requirements.

Click on "Install add-on".



In “text filter” section should be listed the “Pumukit filter”. To finish the installation click on
"Upgrade Moodle Database now".



Click on “Continue”. The Module Configuration will be loaded.



To configure the filter go to “Administration” -> “Site Administration” -> “Plugins” -> “Filters”
-> “Manage filters”.

Change the filter status called “Pumukit filter” from “Disabled” to “On”.



The filter is configured.

### Installation check

Login on Moodle as a teacher or using a user account. Go to any course, or create a new
course for testing.

Activate the edition on “Turn editing on”. Click on "Add an activity or resource" on the
relevant item. On resources, select "Page" and click on "Add".



Press the "Insert media Moodle" icon in Content section.



The following window opens:



Click on "Find or upload a sound, video or applet". A window opens to upload files.



Select the appropriate repository name. In this case, we can see a “pumukit” section with the
Matterhorn icon. On the right-side menu we can see the series assigned to the professor. In
case none series are shown it is necessary to assign the professor that is logged in to a video in
Pumukit (see Pumukit User Manual, 4.2.2.4 People section).

NOTE: In Pumukit, the video must be assigned to a person with the role “Actor” from the
People section. The email from the person in Pumukit and the email from the user logged in
PuMoodle must be the same.

Selecting the series we can see the available videos to publish from that series.



Select a video from the series. A window opens with information of the video. Click on
“Select this file”.



It leads us to the previous window "Insert media Moodle" only must click on "Insert" and
brings us back to the Page Creation section.



On the Page Creation section the video is shown as a link. Fill the mandatory fields of the
form. Click on “Save and return to course”, then we go to the created page and the video will be
embedded on the page.

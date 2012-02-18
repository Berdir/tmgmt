Translation Management Tools (tmgmt)
-------------------------------------

A collection of tools to facilitate the translation of text elements in Drupal.

Requirements
------------------

Translation Management Tool was built for Drupal 7. There will be no backport.

To use Translation Management Tool you need to install and activate the
following modules:

 * Entity API (latest dev version!)
 * Views (latest dev version!)
 * Chaos Tools (Required for Views)
 * Views Bulk Operations
 * Content Translation
 * Locale

 * Internationalization/i18n (latest dev Version!)
   (Only necessary for i18n_string translation)

Basic concepts
------------------

With tmgmt installed, the 'translate' tab of a node changes. You can choose 
one or more languages to translate the node to and 'Request a translation' with 
the corresponding button. 

A translation job is created for each language chosen. It will run through the 
following states:

unprocessed     Translation requested in the 'translate' tab of a node.
                Settings of the job (label set, translator chosen) defined.
                The job was saved.
activ           The job is in the process of being translated. Depending on 
                the chosen translator, the actual translation happens auto-
                matically or by a human being. 
                In all cases the job is returned to the job queue for review.
                When the review is done, the status of the job item goes from
                'needs review' to 'accepted'. 
finished        The job has been accepted and the translated node was created
      
A job deleted from the list is not visible any more. You can choose to purge 
jobs automatically from the list after a defined time span. See tmgmt settings.
				 

Getting started
------------------

The first simple translation job using Microsoft's translation service.

1) Preparation

- Make sure you have downloaded all of the listed dependencies.
- Define a second language using locale
- Modify one content type to be multilingual. Choose 'Enabled, with translation'
  from the Publishing Options / Multilingual support.

2) Set up Translation Management Tools

- Download tmgmt module
- Enable the following modules, this will also include all
  - Translation Management UI
  - Content translation Source UI
  - Microsoft Translator
- A translator has been automatically created. Go to the Translator management
  page at:

    Configuration > Regional and language > Translation Management > Translators

  Adjust the label to your liking and get an API key using the provided link in
  the settings. Then save the updated translator.
  
- Adjust the Auto Acceptance settings to your liking. To enable it, check the  
  'Allow translators to automatically accept translations' in the tmgmt settings.
  You can now choose to accept jobs without review by checking  'Auto accept 
  finished translations' for each of your tranlators individually.

3) Translate

- Create a new piece of content of the multilingual content type defined before.
  Make sure to choose a language.
- Once the node has been saved, click on the "Translate" tab.
- Choose the language you want to translate the node to with the checkbox.
- Click on 'Request Translation' and the foreign language version of the node
  will be created immediately. 
- If the auto acceptance is not set, find the job in the jobs queue and choose
  the 'review' link. Accept the translation and the translated node is created.
- Check the translated node!

For further options, see the documentation on drupal.org/documentation/modules/tmgmt.

== State of Module ==

This projects consists of many pluggable, independent elements. It is alpha
quality and not every plugin is in an equal state. The list below aims to give a
short overview of each translator and source plugin.

The management part itself is working well. It is possible to create and submit
jobs to various translators, get the translated text back into the system,
review and approve it. We're still working on the user interfaces, they are
unfinished and very basic.

==== Sources ====

- Content Translation
  Plugin and User Interface is working and tested. No User interface to
  translate multiple content items at once yet.

- Entity Translation
  The API is working, there is no user interface yet. If you know the entity
  translation module then we could use your help!

- Internationalization (I18n)
  Same story, the API works but we're missing a user interface.

=== Translators ===

- Microsoft Translator
  Machine translation, technically working well but the results are of course of
  varying quality.

- File translator
  Allows to export jobs into files and import them once they have been
  translated. Contains a pluggable system to support various file formats,
  currently XLIFF and HTML.

- Local Translator
  Allows to assign translation jobs to local users so that they can translate
  things in a unified interface with the ability to review it. Currently not
  working, needs to be adapted.

- myGengo
  Integrates with http://www.mygengo.com. Initial implementation done, ready for
  testing.

- Supertext
  Integrates with http://www.supertext.ch. Development has started, not working
  yet. Stay tuned!

- Nativy
  Integrates with http://www.nativy.com/. Initial implementation done but
  it is currently broken. The integration is also not satisfying yet.

Translation Management Tools (tmgmt)
-------------------------------------

A collection of tools to facilitate the translation of text elements in Drupal. 

Requirements
------------------

Translation Management Tool was built for Drupal 7. There will be no backport.

To use Translation Management Tool you need to install and activate the
following modules:

 * Entity API
 * Views
 * Chaos Tools (Required for Views)
 * Views Bulk Operations
 * Content Translation
 * Locale

 * Internationalization/i18n (latest dev Version!)
   (Only necessary for i18n_string translation)

Basic concepts
------------------

TODO

Getting started
------------------

The first simple translation job using Microsoft's translation service.

1) Preparation

- Install and activate all of the above listed modules
- Define a second language using locale
- Modify one content type to be multilingual. Choose 'Enabled, with tranlation'
  from the Publishing Options / Multilingual support.

2) Set up Translation Management Tools

- Download tmgmt module
- Activate the minimal sub modules: 
  - Translation Management Core
  - Translation Management Field
  - Translation Management UI
  - Content Translation Source
  - Simple Translation UI
  - Microsoft Translator
- A translator has been automatically created. Go to the Translator management
  page at:

    Configuration > Regional and language > Translation Management > Translators

  Adjust the label to your liking and get an API key using the provided link in
  the settings. Then save the updated translator.

3) Translate

- Create a new piece of content of the multilingual content type defined before.
  Make sure to choose a language.
- Once the node has been saved, click on the "Translate" tab.
- Choose the language you want to translate the node to with the checkbox.
- Click on 'Request Translation' and the foreign language version of the node
  will be created immediately.
- Check the translated node!

For further options, see the documentation on drupal.org/documentation/modules/tmgmt.

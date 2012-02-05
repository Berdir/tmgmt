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

3) Translate

- Create a new piece of content of the multilingual content type defined before.
  Make sure to choose a language.
- Once the node has been saved, click on the "Translate" tab.
- Choose the language you want to translate the node to with the checkbox.
- Click on 'Request Translation' and the foreign language version of the node
  will be created immediately.
- Check the translated node!

For further options, see the documentation on drupal.org/documentation/modules/tmgmt.

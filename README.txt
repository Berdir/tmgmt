Translation Management Tools (tmgmt)

A collection of tools to facilitate the translation of text elements in Drupal. 

Requirements

Translation Management Tool was built for D7. There will be no back port.

To use Translation Management Tool you need to install and activate the following modules:

Entity API
Views 
Chaos Tools (Base for Views)
Views Bulk Operations
Content Translastion
Locale
Internationalization / i18n for String translation (latest dev Version!)


Getting started (the first simple translation job using Microsofts translating service)

1) Preparation

- Install and activate all of the above listed modules
- Define a second language using locale
- Modify one content type to be multilingual. Choose 'Enabled, with tranlation' from the Publishing Options / Multilingual support

2) Set up Translation Management Tools

- Download tmgmt module
- Activate the minimal sub modules: 
  - Translation Management Core
  - Translation Management Field
  - Translation Management UI
  - Content Translation Source
  - Simple Translation UI
  - Microsoft Translator
- Add a translator (Configuration > Regional and language > Translation Management > Translators). Give it a name of your choice 
  and choose the Microsoft translator plugin

3) Translate

- Create a new node of the multilingual content type definde before. Make sure to give it a language!
- Once the node ist saved, choose the Translate Tab
- Choose the language you want to translte the node to with the checkbox
- Click on 'Request Translation' and the foreign language version of the node will be created immediately.
- Check the translated node!

For further options, please the documentation on drupal.org/documentation/modules/tmgmt

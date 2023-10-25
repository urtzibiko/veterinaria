Paragraph view mode module for Drupal
---------------------------

INTRODUCTION
-----------
  This module allows to dynamically pick the display mode
  of the paragraph during adding/editing the paragraph item,
  by creating a field with available view modes.

INSTALLATION:
-------------
  1. Extract the tar.gz into your 'modules' or directory and copy to modules
     folder.
  2. Go to "Extend" after successfully login into admin.
  3. Enable the module at 'administer >> modules'.

REQUIREMENTS
------------
  Paragraph view mode module extend the functionality
  of the paragraphs module, so to use this module,
  you need to download/install the Paragraph module first.

CONFIGURATION
-------------
  1. To enable paragraph view mode field on your paragraph type,
     go to /admin/structure/paragraphs_type.
  2. Click edit (at the operations column) on the paragraph
     that you want to have a view mode field.
  3. Check option "Enable paragraph view mode field on this paragraph type.".
  4. Click save.
  5. Go to tab "Manage form display".
  6. Move the "Paragraph view mode" field
     anywhere you want (except the disabled section).
  7. Customize widget settings by selecting which view modes
     you want to allow to view during adding/editing the content.
  8. Set default value for the "Paragraph view mode" field.

UNINSTALLATION
--------------
  1. Disable the module from 'administer >> modules'.
  2. Uninstall the module

MAINTAINERS
-----------
  Current maintainers:
   * Mariusz Andrzejewski (sayco) - https://www.drupal.org/u/sayco

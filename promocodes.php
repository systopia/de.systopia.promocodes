<?php
/*-------------------------------------------------------+
| SYSTOPIA PromoCodes Extension                          |
| Copyright (C) 2019 SYSTOPIA                            |
| Author: B. Endres (endres@systopia.de)                 |
| http://www.systopia.de/                                |
+--------------------------------------------------------+
| This program is released as free software under the    |
| Affero GPL license. You can redistribute it and/or     |
| modify it under the terms of this license which you    |
| can read by viewing the included agpl.txt or online    |
| at www.gnu.org/licenses/agpl.html. Removal of this     |
| copyright header is strictly prohibited without        |
| written permission from the original author(s).        |
+--------------------------------------------------------*/


require_once 'promocodes.civix.php';

use CRM_Promocodes_ExtensionUtil as E;

/**
 * Add an action for creating promo codes based on a contact set
 *
 * @param string $objectType specifies the component
 * @param array $tasks the list of actions
 *
 * @access public
 */
function promocodes_civicrm_searchTasks($objectType, &$tasks) {
  if ($objectType == 'contact') {
    $tasks['generate_promocode'] = array(
        'title'  => E::ts('Generate Promo-Code'),
        'class'  => 'CRM_Promocodes_Form_Task_Generate',
        'result' => false);
  } elseif ($objectType == 'membership') {
    $tasks['generate_promocode'] = array(
      'title'  => E::ts('Generate Promo-Code'),
      'class'  => 'CRM_Promocodes_Form_Task_GenerateMembership',
      'result' => false);
  }
}

/**
 * Implements hook_civicrm_config().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_config
 */
function promocodes_civicrm_config(&$config) {
  _promocodes_civix_civicrm_config($config);
}

/**
 * Implements hook_civicrm_xmlMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_xmlMenu
 */
function promocodes_civicrm_xmlMenu(&$files) {
  _promocodes_civix_civicrm_xmlMenu($files);
}

/**
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function promocodes_civicrm_install() {
  _promocodes_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_postInstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_postInstall
 */
function promocodes_civicrm_postInstall() {
  _promocodes_civix_civicrm_postInstall();
}

/**
 * Implements hook_civicrm_uninstall().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_uninstall
 */
function promocodes_civicrm_uninstall() {
  _promocodes_civix_civicrm_uninstall();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function promocodes_civicrm_enable() {
  _promocodes_civix_civicrm_enable();
}

/**
 * Implements hook_civicrm_disable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_disable
 */
function promocodes_civicrm_disable() {
  _promocodes_civix_civicrm_disable();
}

/**
 * Implements hook_civicrm_upgrade().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_upgrade
 */
function promocodes_civicrm_upgrade($op, CRM_Queue_Queue $queue = NULL) {
  return _promocodes_civix_civicrm_upgrade($op, $queue);
}

/**
 * Implements hook_civicrm_managed().
 *
 * Generate a list of entities to create/deactivate/delete when this module
 * is installed, disabled, uninstalled.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_managed
 */
function promocodes_civicrm_managed(&$entities) {
  _promocodes_civix_civicrm_managed($entities);
}

/**
 * Implements hook_civicrm_caseTypes().
 *
 * Generate a list of case-types.
 *
 * Note: This hook only runs in CiviCRM 4.4+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_caseTypes
 */
function promocodes_civicrm_caseTypes(&$caseTypes) {
  _promocodes_civix_civicrm_caseTypes($caseTypes);
}

/**
 * Implements hook_civicrm_angularModules().
 *
 * Generate a list of Angular modules.
 *
 * Note: This hook only runs in CiviCRM 4.5+. It may
 * use features only available in v4.6+.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_angularModules
 */
function promocodes_civicrm_angularModules(&$angularModules) {
  _promocodes_civix_civicrm_angularModules($angularModules);
}

/**
 * Implements hook_civicrm_alterSettingsFolders().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_alterSettingsFolders
 */
function promocodes_civicrm_alterSettingsFolders(&$metaDataFolders = NULL) {
  _promocodes_civix_civicrm_alterSettingsFolders($metaDataFolders);
}

/**
 * Implements hook_civicrm_entityTypes().
 *
 * Declare entity types provided by this module.
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_entityTypes
 */
function promocodes_civicrm_entityTypes(&$entityTypes) {
  _promocodes_civix_civicrm_entityTypes($entityTypes);
}

// --- Functions below this ship commented out. Uncomment as required. ---

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_preProcess
 *
function promocodes_civicrm_preProcess($formName, &$form) {

} // */

/**
 * Implements hook_civicrm_navigationMenu().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_navigationMenu
 */
function promocodes_civicrm_navigationMenu(&$menu) {
  _promocodes_civix_insert_navigation_menu($menu, 'Campaigns', array(
    'label' => E::ts('Generate Promocodes'),
    'name' => 'promocodes_campaign_generate',
    'url' => 'civicrm/campaign/promocodes',
    'permission' => 'manage campaign',
    'operator' => 'OR',
    'separator' => 0,
  ));
  _promocodes_civix_navigationMenu($menu);
}

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
 * Implements hook_civicrm_install().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_install
 */
function promocodes_civicrm_install() {
  _promocodes_civix_civicrm_install();
}

/**
 * Implements hook_civicrm_enable().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_enable
 */
function promocodes_civicrm_enable() {
  _promocodes_civix_civicrm_enable();
}

// --- Functions below this ship commented out. Uncomment as required. ---

/**
 * Implements hook_civicrm_preProcess().
 *
 * @link http://wiki.civicrm.org/confluence/display/CRMDOC/hook_civicrm_preProcess
 *

 // */

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

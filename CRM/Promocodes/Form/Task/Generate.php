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

require_once 'CRM/Core/Form.php';

use CRM_Promocodes_ExtensionUtil as E;

/**
 * Form controller class
 *
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC43/QuickForm+Reference
 */
class CRM_Promocodes_Form_Task_Generate extends CRM_Contact_Form_Task {

  public function buildQuickForm() {
    CRM_Utils_System::setTitle(E::ts('Promo-Code Generation'));

    $this->add(
        'select',
        'campaign_id',
        E::ts('Campaign'),
        $this->getCampaignList(),
        TRUE
    );

    $this->add(
        'select',
        'code_type',
        E::ts('Code Type'),
        CRM_Promocodes_Generator::getCodeOptions(),
        TRUE,
        array('class' => 'huge')
    );

    parent::buildQuickForm();

    $this->addButtons(array(
        array(
            'type' => 'submit',
            'name' => E::ts('Generate CSV'),
            'isDefault' => TRUE,
        ),
        array(
            'type' => 'cancel',
            'name' => E::ts('Back'),
            'isDefault' => FALSE,
        ),
    ));
  }



  /**
   * get the last iteration's values
   */
  public function setDefaultValues() {
    $values = civicrm_api3('Setting', 'getvalue', array('name' => 'de.systopia.promocodes.contact', 'group' => 'de.systopia.promocodes'));
    if (empty($values) || !is_array($values)) {
      return array();
    } else {
      return $values;
    }
  }


  /**
   * PostProcess:
   *  - store submitted settings as new defaults
   *  - generate CSV
   *
   * @throws CiviCRM_API3_Exception
   */
  public function postProcess() {
    // store settings as default
    $all_values = $this->exportValues();

    // store defaults
    $values = array(
        'campaign_id' => CRM_Utils_Array::value('campaign_id', $all_values),
        'code_type'   => CRM_Utils_Array::value('code_type', $all_values),
    );
    civicrm_api3('Setting', 'create', array('de.systopia.promocodes.contact' => $values));

    if (isset($all_values['_qf_Generate_submit'])) {
      // CREATE CSV
      $generator = new CRM_Promocodes_Generator($values);
      $generator->generateCSV($all_values['code_type'], $this->_contactIds);
    }

    parent::postProcess();
  }


  /**
   * Get a list of campaigns
   */
  protected function getCampaignList() {
    $campaigns = [];
    $query = civicrm_api3('Campaign', 'get', [
        'is_active'    => 1,
        'return'       => 'id,title',
        'option.limit' => 0]);
    foreach ($query['values'] as $campaign) {
      $campaigns[$campaign['id']] = $campaign['title'];
    }
    return $campaigns;
  }
}

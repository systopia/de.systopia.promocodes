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
        'text',
        'delimiter',
        E::ts('Delimiter'),
        array('class' => 'tiny'),
        FALSE
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
        'delimiter'   => CRM_Utils_Array::value('delimiter', $all_values),
    );
    civicrm_api3('Setting', 'create', array('de.systopia.promocodes.contact' => $values));

    if (isset($all_values['_qf_Generate_submit'])) {
      // CREATE CSV
      $this->generateCSV($values);
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

  /**
   * Generate CSV file with PromoCodes
   */
  protected function generateCSV($params) {
    $fields = [
        'postal_greeting' => E::ts("Postal Greeting"),
        'first_name'      => E::ts("First Name"),
        'last_name'       => E::ts("Last Name"),
        'code'            => E::ts("Promo Code"),
    ];

    // just write stuff directly into the output stream
    $filename = E::ts("PromoCodes-") . date('YmdHis') . '.csv';
    header('Content-Description: File Transfer');
    header('Content-Type: application/csv');
    header('Content-Disposition: attachment; filename=' . $filename);
    $output_stream = fopen('php://output', 'w');
    ob_clean();
    flush();

    // write headers
    fputcsv($output_stream, array_values($fields));

    // write data
    $contact_ids = implode(',', $this->_contactIds);
    if ($contact_ids) {
      $query_sql = "
        SELECT 
          contact.id                      AS contact_id,
          contact.first_name              AS first_name,
          contact.last_name               AS last_name,
          contact.postal_greeting_display AS postal_greeting
        FROM civicrm_contact contact
        WHERE contact.id IN ({$contact_ids})
          AND (contact.is_deleted IS NULL OR contact.is_deleted = 0)";
      $query = CRM_Core_DAO::executeQuery($query_sql);
      while ($query->fetch()) {
        $record = $this->generateRecord(array_keys($fields), $query, $params);
        fputcsv($output_stream, $record);
      }
    }

    // done!
    fclose($output_stream);
    CRM_Utils_System::civiExit();
  }

  /**
   * Generate list of field values corresponding to the fields
   * @param $fields  array field key list
   * @param $data    object DAO data object
   * @param $params  array parameters
   * @return  array record
   */
  protected function generateRecord($fields, $data, $params) {
    $record = [];
    foreach ($fields as $field) {
      if ($field == 'code') {
        $value = $this->generateCode($data->contact_id, $params);
      } else {
        $value = isset($data->$field) ? $data->$field : '';
      }
      $record[] = $value;
    }
    return $record;
  }

  /**
   * Generate code based
   *
   * @param $contact_id integer contact ID
   * @param $params     array   additional parameters
   *
   * @return string generated code
   */
  protected function generateCode($contact_id, $params) {
    $delimiter = CRM_Utils_Array::value('delimiter', $params, '');
    $components = [];
    $components[0] = sprintf('%010d', $contact_id);
    $components[1] = sprintf('%06d', $params['campaign_id']);
    $components[2] = $this->calculate_mod97($components[0] . $components['1']);
    return $delimiter . implode($delimiter, $components) . $delimiter;
  }

  /**
   * Calculate MOD97 checksum
   *
   * @param $number string number
   * @return two-digit mod97 checksum
   */
  protected function calculate_mod97($number) {
    $number .= '00';
    $mod97 = 97 - ($number % 97) + 1;
    return sprintf('%02d', $mod97);
  }
}

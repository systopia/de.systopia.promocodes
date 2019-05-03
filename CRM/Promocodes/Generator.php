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
 * Generate Promotional Codes
 */
class CRM_Promocodes_Generator {

  protected $params = NULL;
  protected $fields = NULL;

  /**
   * CRM_Promocodes_Generator constructor.
   *
   * @param $params generation parameters, e.g. 'campaign_id'
   */
  public function __construct($params) {
    $this->params = $params;
    // construct the field set. First: basic fields
    $this->fields = [
        'organization_name' => E::ts("Organisation Name"),
        'legal_name'        => E::ts("Legal Name"),
        'household_name'    => E::ts("Household Name"),
        'prefix'            => E::ts("Prefix"),
        'first_name'        => E::ts("First Name"),
        'last_name'         => E::ts("Last Name"),
        'suffix'            => E::ts("Suffix"),
        'street_address'    => E::ts("Street Address"),
        'postal_code'       => E::ts("Postal Code"),
        'city'              => E::ts("City"),
        'postal_greeting'   => E::ts("Postal Greeting"),
    ];

    // then: custom fields
    if (!empty($params['custom_fields'])) {
      foreach ($params['custom_fields'] as $custom_field) {
        $this->fields[$custom_field['field_key']] = $custom_field['field_title'];
      }
    }

    // finally: the code
    $this->fields['code'] = E::ts("Promo Code");
  }

  /**
   * Generate CSV file with PromoCodes,
   *  write it to the output stream and exit
   *
   * @param $code_type   string  code identifier
   * @param $contact_ids array   list of contact IDs
   */
  public function generateCSV($code_type, $contact_ids) {
    // just write stuff directly into the output stream
    $filename = E::ts("PromoCodes-") . date('YmdHis') . '.csv';
    header('Content-Description: File Transfer');
    header('Content-Type: application/csv');
    header('Content-Disposition: attachment; filename=' . $filename);
    $output_stream = fopen('php://output', 'w');
    ob_clean();
    flush();

    // write headers
    fputcsv($output_stream, array_values($this->fields));

    // write data
    if ($contact_ids) {
      $query_sql = $this->buildSQL($contact_ids);
      $query = CRM_Core_DAO::executeQuery($query_sql);
      while ($query->fetch()) {
        $record = $this->generateRecord($query, $code_type);
        fputcsv($output_stream, $record);
      }
    }

    // done!
    fclose($output_stream);
    CRM_Utils_System::civiExit();
  }

  /**
   * Generate the SQL query
   *
   * @param $contact_ids array list of contact IDs
   * @return string SQL query
   */
  protected function buildSQL($contact_ids) {
    $contact_ids = implode(',', $contact_ids);

    // look up option groups
    $prefix_group_id = (int) civicrm_api3('OptionGroup', 'getvalue', ['name' => 'individual_prefix', 'return' => 'id']);
    $suffix_group_id = (int) civicrm_api3('OptionGroup', 'getvalue', ['name' => 'individual_suffix', 'return' => 'id']);

    // TODO: add custom fields

    $query_sql = "
        SELECT 
          contact.id                      AS contact_id,
          contact.organization_name       AS organization_name,
          contact.legal_name              AS legal_name,
          contact.household_name          AS household_name,
          contact.first_name              AS first_name,
          contact.last_name               AS last_name,
          contact.postal_greeting_display AS postal_greeting,
          prefix.label                    AS prefix,
          suffix.label                    AS suffix,
          address.street_address          AS street_address,
          address.postal_code             AS postal_code,
          address.city                    AS city
        FROM civicrm_contact contact
        LEFT JOIN civicrm_address address     ON address.contact_id = contact.id  AND address.is_primary = 1
        LEFT JOIN civicrm_option_value prefix ON prefix.value = contact.prefix_id AND prefix.option_group_id = {$prefix_group_id}
        LEFT JOIN civicrm_option_value suffix ON suffix.value = contact.suffix_id AND suffix.option_group_id = {$suffix_group_id} 
        WHERE contact.id IN ({$contact_ids})
          AND (contact.is_deleted IS NULL OR contact.is_deleted = 0)
        GROUP BY contact.id";
    return $query_sql;
  }

  /**
   * Generate list of field values corresponding to the fields
   * @param $data        object  DAO data object
   * @param $code_type   string  code identifier
   * @return  array record
   */
  protected function generateRecord($data, $code_type) {
    $fields = array_keys($this->fields);
    $record = [];
    foreach ($fields as $field) {
      if ($field == 'code') {
        $value = $this->generateCode($code_type, $data);
      } else {
        $value = isset($data->$field) ? $data->$field : '';
      }
      $record[] = $value;
    }
    return $record;
  }

  ################################################################################
  ##                        CODE GENERATION                                     ##
  ################################################################################

  /**
   * Get a list of code options
   * @return array code key => name
   */
  public static function getCodeOptions() {
    return [
        'mod97_contact10_campaign6_delimiter_X' => E::ts("'X{Contact}X{Campaign}X{MOD97}X' (fixed length)"),
        'mod97_contact_campaign_delimiter_X'    => E::ts("'X{Contact}X{Campaign}X{MOD97}X' (short)"),
    ];
  }

  /**
   * Generate code based on the data row
   *
   * @param $data        object  DAO data object
   * @param $code_type   string  code identifier (see getCodeOptions)
   *
   * @return string generated code
   */
  protected function generateCode($code_type, $data) {
    switch ($code_type) {
      case 'mod97_contact10_campaign6_delimiter_X':
        $campaign_id = CRM_Utils_Array::value('campaign_id', $this->params, 0);
        return $this->generateCodeCampaignMOD97($data->contact_id, '%010d', $campaign_id, '%06d', 'X');

      default:
      case 'mod97_contact_campaign_delimiter_X':
        $campaign_id = CRM_Utils_Array::value('campaign_id', $this->params, 0);
        return $this->generateCodeCampaignMOD97($data->contact_id, '%d', $campaign_id, '%d', 'X');
    }
  }


  /**
   * Simple MOD97 code generation
   * @return string
   */
  protected function generateCodeCampaignMOD97($contact_id, $contact_format, $campaign_id, $campaign_format, $delimiter) {
    $components = [];
    $components[0] = sprintf($contact_format, $contact_id);
    $components[1] = sprintf($campaign_format, $campaign_id);
    $components[2] = $this->calculate_mod97($components[0] . $components['1']);
    return $delimiter . implode($delimiter, $components) . $delimiter;
  }



  /**
   * Calculate MOD97 checksum
   *
   * @param $number string number
   * @return string two-digit mod97 checksum
   */
  protected function calculate_mod97($number) {
    $number .= '00';
    $mod97 = 97 - ($number % 97) + 1;
    return sprintf('%02d', $mod97);
  }
}

<?php
use CRM_Promocodes_ExtensionUtil as E;

/**
 * Promocode.Validate API specification (optional)
 * This is used for documentation and validation.
 *
 * @param array $spec description of fields supported by this API call
 * @return void
 * @see http://wiki.civicrm.org/confluence/display/CRMDOC/API+Architecture+Standards
 */
function _civicrm_api3_promocode_Validate_spec(&$spec) {
  $spec['promocode']['api.required'] = 1;
  $spec['delimiter']['api.required'] = 1;
}

/**
 * Promocode.Validate API
 *
 * @param array $params
 * @return array API result descriptor
 * @see civicrm_api3_create_success
 * @see civicrm_api3_create_error
 * @throws API_Exception
 */
function civicrm_api3_promocode_Validate($params) {
  $data = array(
    $params['promocode'] => array(),
  );

  try {
    list(,$contact_id, $campaign_id, $checksum) = explode($params['delimiter'], $params['promocode']);
    if (!is_numeric($contact_id) || !is_numeric($campaign_id) || !is_numeric($checksum)) {
      throw new Exception(E::ts('Promocode could not be parsed.'));
    }
    if ($checksum != CRM_Promocodes_Generator::calculate_mod97($contact_id . $campaign_id)) {
      throw new Exception(E::ts('Invalid checksum.'));
    }
    $data[$params['promocode']]['valid'] = 1;
  }
  catch (Exception $exception) {
    $data[$params['promocode']]['valid'] = 0;
    $data[$params['promocode']]['validation_message'] = $exception->getMessage();
  }

  return civicrm_api3_create_success($data);
}

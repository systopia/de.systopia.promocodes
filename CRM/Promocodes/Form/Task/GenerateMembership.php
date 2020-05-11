<?php
/*-------------------------------------------------------+
| SYSTOPIA PromoCodes Extension                          |
| Copyright (C) 2020 SYSTOPIA                            |
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
class CRM_Promocodes_Form_Task_GenerateMembership extends CRM_Member_Form_Task
{
    public function buildQuickForm()
    {
        CRM_Utils_System::setTitle(E::ts('Promo-Code Generation'));

        $this->add(
            'select',
            'campaign_id',
            E::ts('Campaign'),
            CRM_Promocodes_Utils::getCampaignList(),
            true
        );

        $this->add(
            'select',
            'financial_type_id',
            E::ts('Financial Type'),
            CRM_Promocodes_Utils::getFinancialTypes(),
            true
        );

        $this->add(
            'select',
            'code_type',
            E::ts('Code Type'),
            CRM_Promocodes_Generator::getCodeOptions('Membership'),
            true,
            array('class' => 'huge')
        );

        // add custom field options
        $custom_fields = CRM_Promocodes_Utils::getCustomFields(['Contact', 'Organization', 'Individual', 'Membership']);
        $this->add(
            'select',
            'custom1_id',
            E::ts('Custom Field'),
            $custom_fields,
            false,
            array('class' => 'huge')
        );
        $this->add(
            'text',
            'custom1_name',
            E::ts('Column Name'),
            array('class' => 'huge'),
            false
        );

        parent::buildQuickForm();

        $this->addButtons(
            array(
                array(
                    'type'      => 'submit',
                    'name'      => E::ts('Generate CSV'),
                    'isDefault' => true,
                ),
                array(
                    'type'      => 'cancel',
                    'name'      => E::ts('Back'),
                    'isDefault' => false,
                ),
            )
        );
    }


    /**
     * get the last iteration's values
     */
    public function setDefaultValues()
    {
        $values = Civi::settings()->get('de.systopia.promocodes.membership');
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
    public function postProcess()
    {
        // store settings as default
        $all_values = $this->exportValues();

        // store defaults
        $values = array(
            'campaign_id'       => CRM_Utils_Array::value('campaign_id', $all_values),
            'financial_type_id' => CRM_Utils_Array::value('financial_type_id', $all_values),
            'code_type'         => CRM_Utils_Array::value('code_type', $all_values),
            'custom1_id'        => CRM_Utils_Array::value('custom1_id', $all_values),
            'custom1_name'      => CRM_Utils_Array::value('custom1_name', $all_values),
        );
        Civi::settings()->set('de.systopia.promocodes.membership', $values);

        // GENERATION:
        if (isset($all_values['_qf_GenerateMembership_submit'])) {
            // EXTRACT PARAMETERS
            $params = $values;
            if (!empty($values['custom1_id']) && !empty($values['custom1_name'])) {
                $params['custom_fields'] = [
                    [
                        'field_id'    => $values['custom1_id'],
                        'field_key'   => "custom_field_{$values['custom1_id']}",
                        'field_title' => $values['custom1_name'],
                    ]
                ];
            }

            // CREATE CSV
            $generator = new CRM_Promocodes_Generator($params, 'Membership');
            $generator->generateCSV($all_values['code_type'], $this->_memberIds);
        }

        parent::postProcess();
    }

}

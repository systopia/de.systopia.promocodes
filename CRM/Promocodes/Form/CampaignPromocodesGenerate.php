<?php
/*-------------------------------------------------------+
| SYSTOPIA PromoCodes Extension                          |
| Copyright (C) 2020 SYSTOPIA                            |
| Author: J. Schuppe (schuppe@systopia.de)               |
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

use CRM_Promocodes_ExtensionUtil as E;

/**
 * Form controller class for Promocode generation for campaigns.
 */
class CRM_Promocodes_Form_CampaignPromocodesGenerate extends CRM_Core_Form
{
    public function buildQuickForm()
    {
        parent::buildQuickForm();

        CRM_Utils_System::setTitle(E::ts('Promocode Generation: Campaigns'));

        $this->add(
            'select',
            'campaign_id',
            E::ts('Campaign'),
            CRM_Promocodes_Utils::getCampaignList(),
            true,
            ['class' => 'crm-select2 huge']
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
            CRM_Promocodes_Generator::getCodeOptions('Campaign'),
            true,
            ['class' => 'huge40']
        );

        // Set default values from settings.
        $values = Civi::settings()->get('de.systopia.promocodes.campaign');
        if (is_array($values)) {
            $this->setDefaults($values);
        }

        $this->addButtons(
            [
                [
                    'type' => 'submit',
                    'name' => E::ts('Generate Promocode'),
                    'isDefault' => true,
                ],
            ]
        );
    }

    /**
     * @throws CRM_Core_Exception
     */
    public function postProcess()
    {
        $all_values = $this->exportValues();

        // Store defaults as setting.
        $values = [
            'campaign_id' => CRM_Utils_Array::value('campaign_id', $all_values),
            'code_type' => CRM_Utils_Array::value('code_type', $all_values),
            'financial_type_id' => CRM_Utils_Array::value('financial_type_id', $all_values),
        ];
        Civi::settings()->set('de.systopia.promocodes.campaign', $values);

        // Create code.
        $params = $values;
        $generator = new CRM_Promocodes_Generator($params);
        $code = $generator->generateCode(
            $all_values['code_type'],
            []
        );

        CRM_Core_Session::setStatus(
            E::ts(
                'The generated Promocode is: <strong><code>%1</code></strong>. Please copy it from here, since it is not being stored anywhere.',
                [1 => $code]
            ),
            E::ts('Promocode generated'),
            'no-popup'
        );

        parent::postProcess();
    }
}

{*-------------------------------------------------------+
| SYSTOPIA PromoCodes Extension                          |
| Copyright (C) 2019 SYSTOPIA                            |
| Author: B. Endres (endres@systopia.de)                 |
| http://www.systopia.de/                                |
+--------------------------------------------------------+
| This program is released as free software under the    |
| Affero GPL license. You can redistribute it and/or     |
| modify it under the terms of this license which y}u    |
| can read by viewing the included agpl.txt or online    |
| at www.gnu.org/licenses/agpl.html. Removal of this     |
| copyright header is strictly prohibited without        |
| written permission from the original author(s).        |
+-------------------------------------------------------*}

<div class="crm-block crm-form-block">

  <h3>{ts domain="de.systopia.promocodes"}PromoCode Parameters{/ts}</h3>

  <div class="crm-section">
    <div class="label">{$form.code_type.label}</div>
    <div class="content">{$form.code_type.html}</div>
    <div class="clear"></div>
  </div>

  <div class="crm-section">
    <div class="label">{$form.campaign_id.label}</div>
    <div class="content">{$form.campaign_id.html}</div>
    <div class="clear"></div>
  </div>

  <div class="crm-section">
    <div class="label">{$form.financial_type_id.label}</div>
    <div class="content">{$form.financial_type_id.html}</div>
    <div class="clear"></div>
  </div>

  <h3>{ts domain="de.systopia.promocodes"}Custom Columns{/ts}</h3>

  {foreach from=$custom_indices item=index}
    <div class="crm-section">
      {capture assign=field_id}custom{$index}_id{/capture}
      {capture assign=field_name}custom{$index}_name{/capture}
      <div class="label">{$form.$field_id.label}</div>
      <div class="content">{$form.$field_id.html}&nbsp;{$form.$field_name.html}</div>
      <div class="clear"></div>
    </div>
  {/foreach}

  <div class="crm-submit-buttons">
    {include file="CRM/common/formButtons.tpl" location="bottom"}
  </div>

</div>

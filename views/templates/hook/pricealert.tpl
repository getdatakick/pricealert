{*
* NOTICE OF LICENSE
*   This file is property of Petr Hucik. You may NOT redistribute the code in any way
*   See license.txt for the complete license
*
* @author    Petr Hucik
* @copyright Petr Hucik <petr.hucik@gmail.com>
* @license   see license.txt 
*}

{addJsDef priceAlertUrl="{$base_dir}modules/pricealert/ajax.php"}
{addJsDef priceAlertData=$data}

<p class="buttons_bottom_block no-print">
  <a href="#" id="pricealert-button-launch" onclick='window.PriceAlert(true); return false' rel="nofollow" title="Add to my wishlist">
    {l s='Alert me when price drops' mod='pricealert'}
  </a>

  <div id="pricealert-dialog"></div>
</p>

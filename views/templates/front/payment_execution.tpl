{*
* 2007-2016 PrestaShop
*
* NOTICE OF LICENSE
*
* This source file is subject to the Academic Free License (AFL 3.0)
* that is bundled with this package in the file LICENSE.txt.
* It is also available through the world-wide-web at this URL:
* http://opensource.org/licenses/afl-3.0.php
* If you did not receive a copy of the license and are unable to
* obtain it through the world-wide-web, please send an email
* to license@prestashop.com so we can send you a copy immediately.
*
* DISCLAIMER
*
* Do not edit or add to this file if you wish to upgrade PrestaShop to newer
* versions in the future. If you wish to customize PrestaShop for your
* needs please refer to http://www.prestashop.com for more information.
*
*  @author PrestaShop SA <contact@prestashop.com>
*  @copyright  2007-2016 PrestaShop SA
*  @license    http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}

{capture name=path}
    <a href="{$link->getPageLink('order', true, NULL, "step=3")|escape:'html':'UTF-8'}" title="{l s='Go back to the Checkout' mod='reservation'}">{l s='Checkout' mod='reservation'}</a><span class="navigation-pipe">{$navigationPipe}</span>{l s='Bank-wire payment' mod='reservation'}
{/capture}

{include file="$tpl_dir./breadcrumb.tpl"}

<h2>{l s='Résumé' mod='reservation'}</h2>

{assign var='current_step' value='payment'}
{include file="$tpl_dir./order-steps.tpl"}

{if $nbProducts <= 0}
    <p class="warning">{l s='Your shopping cart is empty.' mod='reservation'}</p>
{else}

<h3>{l s='Réservation de Vélo' mod='reservation'}</h3>
<form action="{$link->getModuleLink('reservation', 'validation', [], true)|escape:'html'}" method="post">
<p>
    {l s='Votre réservation de vélo a bien été prise en compte.' mod='reservation'}
    <br/><br />
    {l s='Voici un court résumé de votre réservation :' mod='reservation'}
</p>
<p style="margin-top:20px;">
    - {l s='Le prix total de votre réservation est de :' mod='reservation'}
    <span id="amount" class="price">{displayPrice price=$total}</span>
    {if $use_taxes == 1}
        {l s='(tax incl.)' mod='reservation'}
    {/if}
</p>
<p>
    <b>{l s='Merci de confirmer votre réservation en cliquant sur le bouton "Je confirme ma réservation".' mod='reservation'}</b>
</p>
<p class="cart_navigation" id="cart_navigation">
    <input type="submit" value="{l s='Je confirme ma réservation' mod='reservation'}" class="exclusive_large" />
</p>
</form>
{/if}

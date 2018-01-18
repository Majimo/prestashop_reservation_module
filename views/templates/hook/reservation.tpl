{*
* 2007-2015 PrestaShop
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
*  @author    Pierre Fervel
*  @copyright 2017 Dropbird
*  @license   http://opensource.org/licenses/afl-3.0.php  Academic Free License (AFL 3.0)
*  International Registered Trademark & Property of PrestaShop SA
*}
<script src="https://code.jquery.com/jquery-1.12.4.js"></script>
<script src="https://code.jquery.com/ui/1.12.1/jquery-ui.js"></script>
<div class="row reservation">
	<div class="col-md-offset-1 col-xs-offset-1 col-md-10 intro">
		<p>{if $lang_iso == 'fr'}Utiliser le formulaire suivant pour réaliser votre réservation :{/if}
		   {if $lang_iso == 'gb'}Use the following form to rent a bike :{/if}</p>
	</div>
	<div class="col-md-offset-3 col-xs-offset-1 col-md-6 reservation_choices">
			{*<h3 style="text-align: center">{if $lang_iso == 'fr'}Borne En Maintenance{/if}
											{if $lang_iso == 'en'}Service currently under maintenance{/if}</h3>
			*}
				<form action="{$module_dir|escape:'html':'UTF-8'}forms/reservation_form.php" method="post">
				
				<span class="col-md-6 col-xs-12">	{if $lang_iso == 'fr'}Borne :{/if}
		   								{if $lang_iso == 'gb'}Place :{/if}</span>
				<span class="col-md-6 col-xs-11"><select name="id_borne">
					<!-- <option value="siblu01">Siblu La Réserve Nord</option>
					<option value="siblu02">Siblu La Réserve Ouest</option> -->
					<option value="siblu03" selected="selected">Les Dunes de Contis</option>
				</select></span>
				<br />				
				<div class="clearfix visible-xs visible-sm"></div>
				<span class="col-md-6 col-xs-12">	{if $lang_iso == 'fr'}Numéro d'emplacement :{/if}
		   								{if $lang_iso == 'gb'}Pitch number :{/if}</span>
				<span class="col-md-6 col-xs-11"><input type="textarea" name="bungalo_num" /></span>
				<div class="clearfix visible-xs visible-sm"></div>
				<span class="col-md-6 col-xs-12">	{if $lang_iso == 'fr'}La journée du :{/if}
		   								{if $lang_iso == 'gb'}From :{/if}</span>
				<span class="col-md-6 col-xs-11"><input type="text" placeholder="mm/jj/aaaa" id="datepicker" name="date_debut" /></span>
				<br />				
				<div class="clearfix visible-xs visible-sm"></div>
				<span class="col-md-6 col-xs-12">	{if $lang_iso == 'fr'}Pour une durée de :{/if}
		   								{if $lang_iso == 'gb'}Duration :{/if}</span>
				<span class="col-md-6 col-xs-11"><select id="selectedInterval" name="date_fin">
					<option style="display: none" disabled selected value>{if $lang_iso == 'fr'}Faites votre choix{/if}
		   								{if $lang_iso == 'gb'}Make your choice{/if}</option>
					<option value="m">	{if $lang_iso == 'fr'}Matin : 8:00 à 13:00{/if}
		   								{if $lang_iso == 'gb'}Morning 8am - 1pm{/if}</option>
					<option value="a">	{if $lang_iso == 'fr'}Après-midi : 14:00 à 19:00{/if}
		   								{if $lang_iso == 'gb'}Aternoon 2pm - 7pm{/if}</option>
					<option value="d">	{if $lang_iso == 'fr'}Toute la journée : 8:00 à 19:00{/if}
		   								{if $lang_iso == 'gb'}All day long 8am - 6pm{/if}</option>
		   			<option value="n">	{if $lang_iso == 'fr'}Toute la nuit : 20:00 à 7:00{/if}
		   								{if $lang_iso == 'gb'}All night long 8pm - 7am{/if}</option>
				</select></span>
				<br />				
				<div class="clearfix visible-xs visible-sm"></div>
				<span class="col-md-6 col-xs-7">{if $lang_iso == 'fr'}Vélos disponibles :{/if}
												{if $lang_iso == 'gb'}Avalaible bikes :{/if}</span>
				<span class="col-md-6 col-xs-5" id="velos_dispos">-</span>
				<div class="clearfix visible-xs visible-sm"></div>
				<span class="col-md-6 col-xs-12">	{if $lang_iso == 'fr'}Nombre de Vélos souhaités :{/if}
		   								{if $lang_iso == 'gb'}Total Bikes to rent :{/if}</span>
		   		<span class="col-md-6 col-xs-11"><select id="selectedVelo" name="nb_velo">
					<option value="1" selected="selected">1</option>
					<option value="2">2</option>
					<option value="3">3</option>
		   			<option value="4">4</option>
				</select></span>
				<div class="clearfix visible-xs visible-sm"></div>
				<input id="buttonSubmit" style="background-color: #4E4E4E" disabled type="submit" name="bouton" class="col-xs-offset-3 col-md-offset-3 col-md-6 col-xs-6 submit" />
				{literal}
					<script>

					$(function() {
					var dateToday = new Date()
					var datepickerValue = null
					var datepicker = $( "#datepicker" ).datepicker({
						altField: "#datepicker",
						minDate: dateToday,
						closeText: 'Fermer',
						prevText: 'Précédent',
						nextText: 'Suivant',
						currentText: 'Aujourd\'hui',
						monthNames: ['Janvier', 'Février', 'Mars', 'Avril', 'Mai', 'Juin', 'Juillet', 'Août', 'Septembre', 'Octobre', 'Novembre', 'Décembre'],
						monthNamesShort: ['Janv.', 'Févr.', 'Mars', 'Avril', 'Mai', 'Juin', 'Juil.', 'Août', 'Sept.', 'Oct.', 'Nov.', 'Déc.'],
						dayNames: ['Dimanche', 'Lundi', 'Mardi', 'Mercredi', 'Jeudi', 'Vendredi', 'Samedi'],
						dayNamesShort: ['Dim.', 'Lun.', 'Mar.', 'Mer.', 'Jeu.', 'Ven.', 'Sam.'],
						dayNamesMin: ['D', 'L', 'M', 'M', 'J', 'V', 'S'],
						weekHeader: 'Sem.',
						dateFormat: 'mm/dd/yy'
					})

					datepicker.on("input change", function datePicker(e) {
							var ajaxCart = new XMLHttpRequest();
							var selectedInterval = document.getElementById('selectedInterval')
							var result = selectedInterval.options[selectedInterval.selectedIndex].value
							ajaxCart.open('POST', window.baseUri + '/modules/reservation/views/templates/hook/ajax/reservationCart.php')
							ajaxCart.setRequestHeader("Content-Type", "application/x-www-form-urlencoded")
							datepickerValue = this.value
							ajaxCart.send('myDate=' + this.value + '&myInterval=' + result)
							document.getElementById('velos_dispos').innerHTML = '...'
							checkBike()
							ajaxCart.addEventListener('readystatechange', function() {
								if (ajaxCart.readyState === XMLHttpRequest.DONE && ajaxCart.status === 200) {
									document.getElementById('velos_dispos').innerHTML = ajaxCart.responseText
									checkBike()
								}
								else if (ajaxCart.readyState === XMLHttpRequest.DONE && ajaxCart.status != 200) {
									document.getElementById('velos_dispos').innerHTML = ''
									console.error('Error : statut ->' + ajaxCart.status + ' : ' + ajaxCart.statusText)
								}
							}, false)
					})

					function checkBike() {
						var bikeWanted = document.getElementById('selectedVelo').value
						var bikeDispo = document.getElementById('velos_dispos').innerHTML
						var submitValue = document.getElementById('buttonSubmit')

						if (isNaN(parseInt(bikeDispo)) || parseInt(bikeDispo) < parseInt(bikeWanted)) {
							submitValue.style.backgroundColor = '#4E4E4E'
							submitValue.disabled = true
						}
						else {
							submitValue.style.backgroundColor = '#FC0F73'
							submitValue.disabled = false
						}
					}

					document.getElementById('selectedVelo').addEventListener("change", function() { checkBike() } )

					document.getElementById('selectedInterval').addEventListener("change", function() {
								var ajaxCart = new XMLHttpRequest()
								var datepicker = document.getElementById('datepicker')
								var result = this.options[this.selectedIndex].value
						
								ajaxCart.open('POST', window.baseUri + '/modules/reservation/views/templates/hook/ajax/reservationCart.php')
								ajaxCart.setRequestHeader("Content-Type", "application/x-www-form-urlencoded")
								ajaxCart.send('myDate=' + datepickerValue + '&myInterval=' + result)
								document.getElementById('velos_dispos').innerHTML = '...'
								checkBike()
								ajaxCart.addEventListener('readystatechange', function() {
									if (ajaxCart.readyState === XMLHttpRequest.DONE && ajaxCart.status === 200) {
										document.getElementById('velos_dispos').innerHTML = ajaxCart.responseText
										checkBike()
									}
									else if (ajaxCart.readyState === XMLHttpRequest.DONE && ajaxCart.status != 200) {
										document.getElementById('velos_dispos').innerHTML = ''
										console.error('Error : statut ->' + ajaxCart.status + ' : ' + ajaxCart.statusText)
									}
								}, false)
						})
					})
					</script>
				{/literal}
			</form>
		</div>
</div>
{* onclick="ajaxCart.add(product_id=8, attribute_id=0, true, null, quantity=1, null);" *}
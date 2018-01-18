<p style="color: red;font-weight: bold;">	{if $lang_iso == 'fr'}Attention ! Votre réservation ne sera prise en compte que lorsque vous aurez effectué votre paiement.{/if}
		   									{if $lang_iso == 'gb'}Careful ! Your rent will be valid only when we receive your payment.{/if}</p>

<form id="formReservation">
<p style="display: inline-block">	{if $lang_iso == 'fr'}Choisissez une date de reservation :{/if}
		   							{if $lang_iso == 'gb'}Pick a rent date :{/if}&emsp;</p>
<select style="display: inline-block" onchange="reservationChange()" name="findReservations" id="findReservations">
{foreach from=$select_options item=reservation key=i}
    {$reservation}
{/foreach}
</select>
{foreach from=$reservations_list item=reservation key=i}
    {$reservation}
{/foreach}

{literal}
<script>
	function reservationChange() {
		var findReservations = document.getElementById('findReservations')
		var listReservations = document.getElementById('listReservations').childNodes[1].childNodes
		if ((findReservations == null) || (listReservations == null)) {
			console.error('Error: Elements doesn\'t exist.')
			return
		}
		var selectedClient = findReservations.options[findReservations.selectedIndex]
		var classNameClient = selectedClient.className
		var classListReservation = document.getElementsByClassName(classNameClient)
		if (classListReservation == null) {
			console.log('Error: Elements doesn\'t exist.')
		}
		selectedClient = selectedClient.classList[0]
		for (var i = 1; listReservations[i]; i++) {
			if (listReservations[i].tagName == 'TR')
				listReservations[i].setAttribute('style', 'display:none')
		}

		var t = 0
		for (var i = 0; i < classListReservation.length ; i++) {
			classListReservation[i].setAttribute('style', 'display= table-row;')
			if (classListReservation[i].tagName == 'TR') {
				if (!(t % 2))
					classListReservation[i].style.backgroundColor = "#ddd"
				else
					classListReservation[i].style.backgroundColor = "#FFF"
				t++
		}
	}
	}
</script>
{/literal}
